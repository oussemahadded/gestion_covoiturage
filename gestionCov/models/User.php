<?php
/**
 * models/User.php
 * Modèle Utilisateur — accès aux données PDO
 */

class User
{
    private PDO $pdo;
    private ?bool $hasStatutCompteColumnCache = null;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Lecture ──────────────────────────────────────────────────────────────

    /** Retourne un utilisateur par son email */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateurs WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /** Retourne un utilisateur par son id */
    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateurs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Retourne tous les utilisateurs (admin) */
    public function getAll(): array
    {
        $sql = $this->hasStatutCompteColumn()
            ? 'SELECT id, nom, prenom, email, telephone, role, statut_compte, created_at
               FROM utilisateurs
               ORDER BY created_at DESC'
            : 'SELECT id, nom, prenom, email, telephone, role, \'actif\' AS statut_compte, created_at
               FROM utilisateurs
               ORDER BY created_at DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    /** Compte total des utilisateurs */
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
    }

    // ── Écriture ─────────────────────────────────────────────────────────────

    /**
     * Inscrit un nouvel utilisateur
     * @return int|false l'id inséré ou false
     */
    public function create(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $telephone,
        string $role,
        string $statutCompte = 'actif'
    ): int|false
    {
        // Hachage sécurisé du mot de passe
        $hash = password_hash($motDePasse, PASSWORD_BCRYPT);

        if ($this->hasStatutCompteColumn()) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut_compte)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $nom,
                $prenom,
                $email,
                $hash,
                $telephone,
                $role,
                $this->normalizeAccountStatus($statutCompte)
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$nom, $prenom, $email, $hash, $telephone, $role]);
        }

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Supprime un utilisateur par son id.
     * Note: la suppression physique n'est plus utilisée depuis l'UI admin
     * afin de préserver l'historique (trajets, réservations, avis, messages).
     */
    public function deleteById(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM utilisateurs WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /** Vérifie si un email est déjà pris */
    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Moyenne des notes d'un conducteur */
    public function getAverageRating(int $conducteurId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT AVG(a.note) as moyenne
             FROM avis a
             INNER JOIN trajets t ON a.trajet_id = t.id
             WHERE t.conducteur_id = ?'
        );
        $stmt->execute([$conducteurId]);
        $result = $stmt->fetchColumn();
        return $result ? round((float) $result, 1) : 0.0;
    }

    // ── Système de points (récompenses) ──────────────────────────────────────

    /**
     * Obtient le total de points d'un conducteur
     */
    public function getPoints(int $conducteurId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(points_total, 0) as points FROM utilisateurs WHERE id = ? AND role = "conducteur"'
        );
        $stmt->execute([$conducteurId]);
        $result = $stmt->fetch();
        return $result ? (int) $result['points'] : 0;
    }

    /**
     * Ajoute des points à un conducteur et crée un historique
     * @return bool true si succès
     */
    /**
     * Adds points to a driver and inserts a modern-schema audit row.
     * Signature is kept backward-compatible; $reservationId and $description are
     * mapped to entity_id / ignored (modern schema uses entity_type + entity_id).
     */
    public function addPoints(
        int $conducteurId,
        int $points,
        string $reason,
        ?int $reservationId = null,
        ?string $description = null
    ): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Increase driver's points total
            $stmt = $this->pdo->prepare(
                'UPDATE utilisateurs
                 SET points_total = points_total + ?
                 WHERE id = ? AND role = "conducteur"'
            );
            $updateOk = $stmt->execute([$points, $conducteurId]);

            if (!$updateOk) {
                $this->pdo->rollBack();
                return false;
            }

            // 2. Unlock Sésame eligibility flag once Bronze tier (5,000 pts) is reached
            $stmt = $this->pdo->prepare(
                'SELECT points_total, eligibilite_remise_sesame_at FROM utilisateurs WHERE id = ?'
            );
            $stmt->execute([$conducteurId]);
            $user = $stmt->fetch();

            if ($user && empty($user['eligibilite_remise_sesame_at']) && (int) $user['points_total'] >= 5000) {
                $stmt = $this->pdo->prepare(
                    'UPDATE utilisateurs SET eligibilite_remise_sesame_at = NOW() WHERE id = ?'
                );
                $stmt->execute([$conducteurId]);
            }

            // 3. Insert into points_history using the MODERN schema:
            //    utilisateur_id, points_awarded, entity_type, entity_id
            $entityType = $reason;          // treat caller's $reason as entity_type
            $entityId   = $reservationId;   // treat caller's $reservationId as entity_id

            $stmt = $this->pdo->prepare(
                'INSERT INTO points_history (utilisateur_id, points_awarded, entity_type, entity_id)
                 VALUES (?, ?, ?, ?)'
            );
            $historyOk = $stmt->execute([$conducteurId, $points, $entityType, $entityId]);

            if ($historyOk) {
                $this->pdo->commit();

                // Sync reward tier history (non-critical)
                try {
                    $rewardModel = new Reward();
                    $rewardModel->syncRewardHistory($conducteurId);
                } catch (Throwable $re) {
                    error_log('[REWARD SYNC] ' . $re->getMessage());
                }

                return true;
            } else {
                $this->pdo->rollBack();
                return false;
            }
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[addPoints] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un conducteur est éligible à la remise SESAME (100+ points)
     */
    public function isEligibleForSESAMEDiscount(int $conducteurId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT eligibilite_remise_sesame_at FROM utilisateurs WHERE id = ? AND role = "conducteur"'
        );
        $stmt->execute([$conducteurId]);
        $result = $stmt->fetch();
        return $result && !empty($result['eligibilite_remise_sesame_at']);
    }

    /**
     * Retourne l'historique des points gagnés
     */
    public function getPointsHistory(int $conducteurId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ph.id,
                    ph.utilisateur_id,
                    ph.points_awarded,
                    ph.entity_type,
                    ph.entity_id,
                    ph.created_at,
                    t.ville_depart,
                    t.ville_arrivee
             FROM points_history ph
             LEFT JOIN trajets t ON ph.entity_id = t.id
                                AND ph.entity_type IN ("trajet_complete", "trajet")
             WHERE ph.utilisateur_id = ?
             ORDER BY ph.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $conducteurId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtient les statistiques de points d'un conducteur
     */
    public function getPointsStats(int $conducteurId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                points_total,
                eligibilite_remise_sesame_at,
                COALESCE(
                    (SELECT COUNT(*) FROM points_history WHERE utilisateur_id = ?), 0
                ) AS total_transactions,
                COALESCE(
                    (SELECT SUM(points_awarded) FROM points_history WHERE utilisateur_id = ?), 0
                ) AS total_earned
             FROM utilisateurs
             WHERE id = ? AND role = "conducteur"'
        );
        $stmt->execute([$conducteurId, $conducteurId, $conducteurId]);
        return $stmt->fetch() ?: [
            'points_total'                => 0,
            'eligibilite_remise_sesame_at' => null,
            'total_transactions'          => 0,
            'total_earned'                => 0,
        ];
    }

    public function updateAccountStatus(int $id, string $status): bool
    {
        if (!$this->hasStatutCompteColumn()) {
            return false;
        }

        if (!in_array($status, ['actif', 'en_attente', 'refuse', 'desactive'], true)) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE utilisateurs SET statut_compte = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    private function hasStatutCompteColumn(): bool
    {
        if ($this->hasStatutCompteColumnCache !== null) {
            return $this->hasStatutCompteColumnCache;
        }

        $stmt = $this->pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'statut_compte'");
        $this->hasStatutCompteColumnCache = (bool) $stmt->fetch();
        return $this->hasStatutCompteColumnCache;
    }

    private function normalizeAccountStatus(string $status): string
    {
        $allowed = ['actif', 'en_attente', 'refuse', 'desactive'];
        return in_array($status, $allowed, true) ? $status : 'actif';
    }

    public function getDashboardStats(): array
    {
        $stats = [
            'total_points' => 0,
            'new_users_30d' => 0,
            'role_breakdown' => ['etudiant' => 0, 'professeur' => 0, 'conducteur' => 0, 'admin' => 0],
            'total_trajets' => 0,
            'completed_trajets' => 0
        ];

        try {
            $stmt = $this->pdo->query('SELECT SUM(points_total) FROM utilisateurs');
            $stats['total_points'] = (int) $stmt->fetchColumn();

            $stmt = $this->pdo->query('SELECT COUNT(*) FROM utilisateurs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
            $stats['new_users_30d'] = (int) $stmt->fetchColumn();

            $stmt = $this->pdo->query('SELECT role, COUNT(*) as count FROM utilisateurs GROUP BY role');
            foreach ($stmt->fetchAll() as $row) {
                $stats['role_breakdown'][$row['role']] = (int) $row['count'];
            }

            $stmt = $this->pdo->query('SELECT COUNT(*) as total, SUM(CASE WHEN statut_trajet = "termine" THEN 1 ELSE 0 END) as completed FROM trajets');
            $tr = $stmt->fetch();
            if ($tr) {
                $stats['total_trajets'] = (int) $tr['total'];
                $stats['completed_trajets'] = (int) $tr['completed'];
            }
        } catch (Throwable $e) {}

        return $stats;
    }
}
