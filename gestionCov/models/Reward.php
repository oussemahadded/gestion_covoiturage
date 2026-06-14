<?php
/**
 * models/Reward.php
 * Reward & Remise business logic — CHAYA3NI
 *
 * Eligibility rule:
 *   - role === 'conducteur'
 *   - email ends with @sesame.com.tn
 *
 * Points come from utilisateurs.points_total (set by existing points system).
 * Levels are read dynamically from reward_levels table (never hardcoded).
 */

class Reward
{
    private PDO $pdo;

    /** Sesame domain suffix — eligibility check */
    private const SESAME_DOMAIN = '@sesame.com.tn';

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Eligibility ───────────────────────────────────────────────────────────

    /**
     * Returns true if the given user qualifies for the points / remise programme.
     * Requirement: role must be 'conducteur'.
     * (Email-domain check is optional for admin panel filtering but NOT required
     *  for the basic eligibility that gates the rewards dashboard.)
     */
    public function isEligibleConducteur(array $user): bool
    {
        return ($user['role'] ?? '') === 'conducteur';
    }

    /**
     * Strict eligibility check that also requires a @sesame.com.tn email.
     * Used only by admin queries that want to filter Sesame-domain conducteurs.
     */
    public function isEligibleConducteurStrict(array $user): bool
    {
        if (($user['role'] ?? '') !== 'conducteur') {
            return false;
        }
        $email = strtolower(trim((string) ($user['email'] ?? '')));
        return str_ends_with($email, self::SESAME_DOMAIN);
    }

    // ── Points ────────────────────────────────────────────────────────────────

    /**
     * Calculates (fetches) the total points for a given user ID.
     */
    public function calculateTotalPoints(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(points_total, 0) FROM utilisateurs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    // ── Static Tier Definitions ───────────────────────────────────────────────

    /**
     * Returns the four canonical discount tiers as a static array.
     * These serve as a PHP-level fallback when the reward_levels DB table is
     * empty or unavailable, and as the authoritative source of truth for tiers.
     *
     * Thresholds:
     *   Bronze   — 5,000 pts  → 2%  remise
     *   Silver   — 10,000 pts → 5%  remise
     *   Gold     — 20,000 pts → 10% remise
     *   Platinum — 50,000 pts → 15% remise
     */
    public static function getStaticTiers(): array
    {
        return [
            ['label' => 'Bronze',   'min_points' => 5000,  'remise_percent' => 2.0,  'badge_color' => '#CD7F32'],
            ['label' => 'Silver',   'min_points' => 10000, 'remise_percent' => 5.0,  'badge_color' => '#A8A9AD'],
            ['label' => 'Gold',     'min_points' => 20000, 'remise_percent' => 10.0, 'badge_color' => '#FFD700'],
            ['label' => 'Platinum', 'min_points' => 50000, 'remise_percent' => 15.0, 'badge_color' => '#E5E4E2'],
        ];
    }

    /**
     * Computes the current tier from the static definitions for a given points total.
     * Returns null if below Bronze threshold.
     */
    public static function getTierFromStaticTiers(int $points): ?array
    {
        $tiers = array_reverse(self::getStaticTiers()); // highest first
        foreach ($tiers as $tier) {
            if ($points >= (int) $tier['min_points']) {
                return $tier;
            }
        }
        return null;
    }

    /**
     * Returns the next tier above the given points from static definitions.
     * Returns null if already at Platinum.
     */
    public static function getNextTierFromStaticTiers(int $points): ?array
    {
        foreach (self::getStaticTiers() as $tier) {
            if ($points < (int) $tier['min_points']) {
                return $tier;
            }
        }
        return null;
    }

    // ── Reward Levels (dynamic from DB, with static fallback) ─────────────────

    /**
     * Returns all reward levels ordered ascending by min_points.
     * Falls back to the static tier definitions if the DB table is empty.
     */
    public function getAllLevels(): array
    {
        try {
            $rows = $this->pdo
                ->query('SELECT * FROM reward_levels ORDER BY min_points ASC')
                ->fetchAll();
            return !empty($rows) ? $rows : self::getStaticTiers();
        } catch (Throwable $e) {
            return self::getStaticTiers();
        }
    }

    /**
     * Returns the current reward level row for the given points total.
     * Returns null if no level has been reached yet.
     * Falls back to static tier definitions if DB table is empty.
     */
    public function getCurrentRewardLevel(int $points): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM reward_levels
                 WHERE min_points <= ?
                 ORDER BY min_points DESC
                 LIMIT 1'
            );
            $stmt->execute([$points]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
        } catch (Throwable $e) {
            // fall through to static
        }
        // DB fallback: use static tiers
        return self::getTierFromStaticTiers($points);
    }

    /**
     * Returns the next reward level above the current points.
     * Returns null if the user is already at the highest level.
     * Falls back to static tier definitions if DB table is empty.
     */
    public function getNextRewardLevel(int $points): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM reward_levels
                 WHERE min_points > ?
                 ORDER BY min_points ASC
                 LIMIT 1'
            );
            $stmt->execute([$points]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
            // If DB returned nothing, check static tiers as fallback
        } catch (Throwable $e) {
            // fall through to static
        }
        return self::getNextTierFromStaticTiers($points);
    }

    /**
     * Returns the remise percentage for the given points total.
     * Returns 0.00 if no level reached.
     */
    public function calculateRemise(int $points): float
    {
        $level = $this->getCurrentRewardLevel($points);
        return $level ? (float) $level['remise_percent'] : 0.00;
    }

    /**
     * How many points remain before reaching the next level.
     * Returns 0 if already at max level.
     */
    public function getRemainingPoints(int $points): int
    {
        $next = $this->getNextRewardLevel($points);
        if (!$next) {
            return 0;
        }
        return max(0, (int) $next['min_points'] - $points);
    }

    /**
     * Progress percentage toward the next level (0–100).
     * Based on the gap between current level start and next level start.
     */
    public function getProgressPercentage(int $points): float
    {
        $next = $this->getNextRewardLevel($points);

        if (!$next) {
            return 100.0; // Max level reached
        }

        $current = $this->getCurrentRewardLevel($points);
        $base    = $current ? (int) $current['min_points'] : 0;
        $target  = (int) $next['min_points'];
        $span    = $target - $base;

        if ($span <= 0) {
            return 100.0;
        }

        return min(100.0, round((($points - $base) / $span) * 100, 1));
    }

    // ── Reward History ────────────────────────────────────────────────────────

    /**
     * Persists a level-change event into reward_history.
     * Returns false (never throws) if the table is missing or the insert fails.
     */
    public function saveRewardHistory(
        int     $userId,
        ?string $oldLevel,
        string  $newLevel,
        ?float  $oldRemise,
        float   $newRemise
    ): bool {
        if ($newLevel === '') {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO reward_history
                   (user_id, old_level, new_level, old_remise, new_remise, changed_at)
                 VALUES (?, ?, ?, ?, ?, NOW())'
            );
            return $stmt->execute([$userId, $oldLevel, $newLevel, $oldRemise, $newRemise]);
        } catch (Throwable $e) {
            error_log('[Reward::saveRewardHistory] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Computes current level for a user and saves history if it changed.
     * Called after any points update. Silently no-ops if reward_history is missing.
     */
    public function syncRewardHistory(int $userId): void
    {
        try {
            $points  = $this->calculateTotalPoints($userId);
            $current = $this->getCurrentRewardLevel($points);
            if (!$current) {
                return;
            }

            // Fetch last saved tier for this user
            $stmt = $this->pdo->prepare(
                'SELECT new_level, new_remise
                 FROM reward_history
                 WHERE user_id = ?
                 ORDER BY changed_at DESC
                 LIMIT 1'
            );
            $stmt->execute([$userId]);
            $last = $stmt->fetch();

            $newLevel  = $current['label'];
            $newRemise = (float) $current['remise_percent'];
            $oldLevel  = $last ? $last['new_level']  : null;
            $oldRemise = $last ? (float) $last['new_remise'] : null;

            if ($last && $last['new_level'] === $newLevel) {
                return; // Tier unchanged — nothing to record
            }

            $this->saveRewardHistory($userId, $oldLevel, $newLevel, $oldRemise, $newRemise);
        } catch (Throwable $e) {
            // Non-critical: log and swallow so points are never rolled back over this
            error_log('[Reward::syncRewardHistory] ' . $e->getMessage());
        }
    }

    /**
     * Reward history for a specific user (newest first).
     * Returns [] if the table is missing or any DB error occurs.
     */
    public function getUserRewardHistory(int $userId, int $limit = 20): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM reward_history
                 WHERE user_id = ?
                 ORDER BY changed_at DESC
                 LIMIT ?'
            );
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[Reward::getUserRewardHistory] ' . $e->getMessage());
            return [];
        }
    }

    // ── Admin Queries ─────────────────────────────────────────────────────────

    /**
     * Total count of eligible conducteurs (for pagination).
     */
    public function countEligibleConducteurs(string $search = '', string $levelFilter = ''): int
    {
        $where  = ["u.role = 'conducteur'", "u.email LIKE '%" . self::SESAME_DOMAIN . "'"];
        $params = [];

        if ($search !== '') {
            $where[]  = '(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)';
            $like     = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql = 'SELECT COUNT(*) FROM utilisateurs u WHERE ' . implode(' AND ', $where);

        if ($levelFilter !== '') {
            // We need a subquery join for level filtering
            $sql = "SELECT COUNT(*) FROM utilisateurs u
                    LEFT JOIN reward_levels rl
                      ON rl.id = (
                        SELECT id FROM reward_levels
                        WHERE min_points <= u.points_total
                        ORDER BY min_points DESC LIMIT 1
                      )
                    WHERE " . implode(' AND ', $where) . " AND rl.label = ?";
            $params[] = $levelFilter;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Paginated list of eligible conducteurs with their reward data.
     */
    public function getEligibleConducteurs(
        int    $limit      = 10,
        int    $offset     = 0,
        string $search     = '',
        string $levelFilter = ''
    ): array {
        $where  = ["u.role = 'conducteur'", "u.email LIKE '%" . self::SESAME_DOMAIN . "'"];
        $params = [];

        if ($search !== '') {
            $where[]  = '(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)';
            $like     = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $havingClause = '';
        if ($levelFilter !== '') {
            $havingClause = 'HAVING current_level = ?';
            $params[]     = $levelFilter;
        }

        $sql = "SELECT
                  u.id,
                  u.nom,
                  u.prenom,
                  u.email,
                  COALESCE(u.points_total, 0) AS points_total,
                  rl.label          AS current_level,
                  rl.remise_percent AS current_remise,
                  rl.badge_color    AS badge_color,
                  rn.label          AS next_level,
                  rn.min_points     AS next_min_points,
                  COALESCE(
                    ROUND(
                      LEAST(100,
                        CASE
                          WHEN rn.min_points IS NOT NULL AND (rn.min_points - COALESCE(rl.min_points, 0)) > 0
                          THEN ((COALESCE(u.points_total,0) - COALESCE(rl.min_points,0))
                               / (rn.min_points - COALESCE(rl.min_points,0))) * 100
                          WHEN rn.min_points IS NULL THEN 100
                          ELSE 0
                        END
                      ), 1
                    ),
                    0
                  ) AS progress_pct
                FROM utilisateurs u
                LEFT JOIN reward_levels rl
                  ON rl.id = (
                    SELECT id FROM reward_levels
                    WHERE min_points <= COALESCE(u.points_total, 0)
                    ORDER BY min_points DESC LIMIT 1
                  )
                LEFT JOIN reward_levels rn
                  ON rn.id = (
                    SELECT id FROM reward_levels
                    WHERE min_points > COALESCE(u.points_total, 0)
                    ORDER BY min_points ASC LIMIT 1
                  )
                WHERE " . implode(' AND ', $where) . "
                $havingClause
                ORDER BY u.points_total DESC, u.nom ASC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Dashboard statistics for the admin reward section.
     */
    public function getAdminRewardStats(): array
    {
        $stats = [
            'total_eligible'     => 0,
            'total_conducteurs'  => 0,
            'highest_level'      => '—',
            'highest_color'      => '#6B7280',
            'avg_points'         => 0,
            'level_distribution' => [],
        ];

        try {
            // Total conducteurs actifs
            $stats['total_conducteurs'] = (int) $this->pdo
                ->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'conducteur'")
                ->fetchColumn();

            // Total éligibles (conducteur + sesame domain)
            $stats['total_eligible'] = (int) $this->pdo
                ->query("SELECT COUNT(*) FROM utilisateurs
                          WHERE role = 'conducteur'
                          AND email LIKE '%" . self::SESAME_DOMAIN . "'")
                ->fetchColumn();

            // Highest reward level reached
            $topLevel = $this->pdo->query(
                "SELECT rl.label, rl.badge_color
                 FROM utilisateurs u
                 JOIN reward_levels rl
                   ON rl.id = (
                     SELECT id FROM reward_levels
                     WHERE min_points <= COALESCE(u.points_total, 0)
                     ORDER BY min_points DESC LIMIT 1
                   )
                 WHERE u.role = 'conducteur'
                   AND u.email LIKE '%" . self::SESAME_DOMAIN . "'
                 ORDER BY rl.min_points DESC
                 LIMIT 1"
            )->fetch();

            if ($topLevel) {
                $stats['highest_level'] = $topLevel['label'];
                $stats['highest_color'] = $topLevel['badge_color'];
            }

            // Average points across all eligible conducteurs
            $avg = $this->pdo->query(
                "SELECT AVG(COALESCE(points_total, 0))
                 FROM utilisateurs
                 WHERE role = 'conducteur'
                   AND email LIKE '%" . self::SESAME_DOMAIN . "'"
            )->fetchColumn();
            $stats['avg_points'] = (int) round((float) $avg);

            // Level distribution
            $rows = $this->pdo->query(
                "SELECT
                   COALESCE(rl.label, 'Aucun') AS level_label,
                   COUNT(*) AS cnt
                 FROM utilisateurs u
                 LEFT JOIN reward_levels rl
                   ON rl.id = (
                     SELECT id FROM reward_levels
                     WHERE min_points <= COALESCE(u.points_total, 0)
                     ORDER BY min_points DESC LIMIT 1
                   )
                 WHERE u.role = 'conducteur'
                   AND u.email LIKE '%" . self::SESAME_DOMAIN . "'
                 GROUP BY level_label"
            )->fetchAll();

            foreach ($rows as $row) {
                $stats['level_distribution'][$row['level_label']] = (int) $row['cnt'];
            }
        } catch (Throwable $e) {
            error_log('[Reward::getAdminRewardStats] ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Full reward data snapshot for a single conducteur (for conducteur dashboard).
     * Eligibility is based on role='conducteur' only (no email-domain restriction).
     */
    public function getConducteurRewardData(array $user): array
    {
        $points   = $this->calculateTotalPoints((int) $user['id']);
        $eligible = $this->isEligibleConducteur($user);
        $current  = $this->getCurrentRewardLevel($points);
        $next     = $this->getNextRewardLevel($points);

        return [
            'eligible'         => $eligible,
            'points_total'     => $points,
            'current_level'    => $current,
            'next_level'       => $next,
            'remise_percent'   => $this->calculateRemise($points),
            'remaining_points' => $this->getRemainingPoints($points),
            'progress_pct'     => $this->getProgressPercentage($points),
            'history'          => $eligible ? $this->getUserRewardHistory((int) $user['id'], 5) : [],
        ];
    }
}
