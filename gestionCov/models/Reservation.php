<?php
/**
 * models/Reservation.php
 * Reservation model with concurrency-safe booking and admin traceability helpers.
 */

class Reservation
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Read

    public function getByPassager(int $passagerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    COALESCE(r.prix_snapshot, t.prix) AS montant_estime,
                    t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart, t.prix,
                    u.nom AS conducteur_nom, u.prenom AS conducteur_prenom, u.telephone AS conducteur_tel
             FROM reservations r
             INNER JOIN trajets t ON r.trajet_id = t.id
             INNER JOIN utilisateurs u ON t.conducteur_id = u.id
             WHERE r.passager_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$passagerId]);
        return $stmt->fetchAll();
    }

    public function getByTrajetConducteur(int $conducteurId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    COALESCE(r.prix_snapshot, t.prix) AS montant_estime,
                    t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart, t.prix,
                    t.route_geometry, t.distance_km, t.duree_minutes, t.prix_par_km,
                    u.nom AS passager_nom, u.prenom AS passager_prenom, u.telephone AS passager_tel, u.email AS passager_email
             FROM reservations r
             INNER JOIN trajets t ON r.trajet_id = t.id
             INNER JOIN utilisateurs u ON r.passager_id = u.id
             WHERE t.conducteur_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$conducteurId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservations WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function exists(int $trajetId, int $passagerId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE trajet_id = ? AND passager_id = ?'
        );
        $stmt->execute([$trajetId, $passagerId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function findByTrajetAndPassager(int $trajetId, int $passagerId): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM reservations WHERE trajet_id = ? AND passager_id = ? LIMIT 1'
        );
        $stmt->execute([$trajetId, $passagerId]);
        return $stmt->fetch();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    }

    public function countPendingForConducteur(int $conducteurId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM reservations r
             INNER JOIN trajets t ON r.trajet_id = t.id
             WHERE t.conducteur_id = ?
               AND r.statut = "en_attente"'
        );
        $stmt->execute([$conducteurId]);
        return (int) $stmt->fetchColumn();
    }

    public function getTraceabilityStats(): array
    {
        $sql = 'SELECT
                    (SELECT COUNT(*) FROM trajets) AS total_trajets,
                    (SELECT COUNT(*) FROM reservations) AS total_reservations,
                    (SELECT COUNT(*) FROM reservations WHERE statut = "confirmee") AS reservations_confirmees,
                    (SELECT COUNT(*) FROM reservations WHERE statut = "en_attente") AS reservations_en_attente,
                    (
                        SELECT COALESCE(SUM(COALESCE(r.prix_snapshot, t.prix)), 0)
                        FROM reservations r
                        INNER JOIN trajets t ON r.trajet_id = t.id
                        WHERE r.statut = "confirmee"
                    ) AS recette_confirmee_estimee,
                    (
                        SELECT COALESCE(SUM(COALESCE(r.prix_snapshot, t.prix)), 0)
                        FROM reservations r
                        INNER JOIN trajets t ON r.trajet_id = t.id
                        WHERE r.statut IN ("en_attente", "confirmee")
                    ) AS recette_estimee_active';

        $row = $this->pdo->query($sql)->fetch();
        if (!$row) {
            return [
                'total_trajets' => 0,
                'total_reservations' => 0,
                'reservations_confirmees' => 0,
                'reservations_en_attente' => 0,
                'recette_confirmee_estimee' => 0.0,
                'recette_estimee_active' => 0.0,
            ];
        }

        return [
            'total_trajets' => (int) ($row['total_trajets'] ?? 0),
            'total_reservations' => (int) ($row['total_reservations'] ?? 0),
            'reservations_confirmees' => (int) ($row['reservations_confirmees'] ?? 0),
            'reservations_en_attente' => (int) ($row['reservations_en_attente'] ?? 0),
            'recette_confirmee_estimee' => (float) ($row['recette_confirmee_estimee'] ?? 0),
            'recette_estimee_active' => (float) ($row['recette_estimee_active'] ?? 0),
        ];
    }

    public function getTraceabilityRows(array $filters = []): array
    {
        $sql = 'SELECT r.id AS reservation_id,
                       r.trajet_id,
                       r.passager_id,
                       r.statut,
                       r.prix_snapshot,
                       r.reservation_point_lat,
                       r.reservation_point_lng,
                       r.reservation_point_type,
                       r.reservation_distance_km,
                       r.reservation_duree_minutes,
                       r.reservation_price,
                       r.created_at AS reservation_created_at,
                       r.updated_at AS reservation_updated_at,
                       r.confirmed_at,
                       r.refused_at,
                       r.cancelled_at,
                       r.payment_status,
                       r.paid_amount,
                       r.paid_at,
                       t.id AS trip_id,
                       t.conducteur_id,
                       t.ville_depart,
                       t.ville_arrivee,
                       t.date_depart,
                       t.heure_depart,
                       t.prix AS trajet_prix,
                       t.statut_trajet,
                       t.completed_at,
                       p.nom AS passager_nom,
                       p.prenom AS passager_prenom,
                       p.email AS passager_email,
                       p.telephone AS passager_telephone,
                       p.role AS passager_role,
                       c.nom AS conducteur_nom,
                       c.prenom AS conducteur_prenom,
                       c.email AS conducteur_email,
                       c.telephone AS conducteur_telephone
                FROM reservations r
                INNER JOIN trajets t ON r.trajet_id = t.id
                INNER JOIN utilisateurs p ON r.passager_id = p.id
                INNER JOIN utilisateurs c ON t.conducteur_id = c.id
                WHERE 1=1';

        $params = [];

        if (!empty($filters['statut'])) {
            $sql .= ' AND r.statut = :statut';
            $params[':statut'] = (string) $filters['statut'];
        }

        if (!empty($filters['trajet_id'])) {
            $sql .= ' AND r.trajet_id = :trajet_id';
            $params[':trajet_id'] = (int) $filters['trajet_id'];
        }

        if (!empty($filters['reservation_id'])) {
            $sql .= ' AND r.id = :reservation_id';
            $params[':reservation_id'] = (int) $filters['reservation_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND t.date_depart >= :date_from';
            $params[':date_from'] = (string) $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND t.date_depart <= :date_to';
            $params[':date_to'] = (string) $filters['date_to'];
        }

        if (!empty($filters['role'])) {
            $sql .= ' AND p.role = :role';
            $params[':role'] = (string) $filters['role'];
        }

        if (!empty($filters['email'])) {
            $sql .= ' AND (p.email LIKE :email OR c.email LIKE :email)';
            $params[':email'] = '%' . (string) $filters['email'] . '%';
        }

        $sql .= ' ORDER BY r.created_at DESC, r.id DESC';
        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 300;
        $limit = max(1, min(1000, $limit));
        $sql .= ' LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findDetailedById(int $reservationId): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    COALESCE(r.prix_snapshot, t.prix) AS montant_estime,
                    t.id AS trajet_id,
                    t.ville_depart,
                    t.ville_arrivee,
                    t.date_depart,
                    t.heure_depart,
                    t.prix AS trajet_prix,
                    t.description AS trajet_description,
                    t.places_total,
                    t.places_restantes,
                    t.statut_trajet,
                    t.completed_at,
                    t.created_at AS trajet_created_at,
                    t.updated_at AS trajet_updated_at,
                    p.id AS passager_user_id,
                    p.nom AS passager_nom,
                    p.prenom AS passager_prenom,
                    p.email AS passager_email,
                    p.telephone AS passager_telephone,
                    p.role AS passager_role,
                    p.statut_compte AS passager_statut_compte,
                    c.id AS conducteur_user_id,
                    c.nom AS conducteur_nom,
                    c.prenom AS conducteur_prenom,
                    c.email AS conducteur_email,
                    c.telephone AS conducteur_telephone,
                    c.role AS conducteur_role,
                    c.statut_compte AS conducteur_statut_compte
             FROM reservations r
             INNER JOIN trajets t ON r.trajet_id = t.id
             INNER JOIN utilisateurs p ON r.passager_id = p.id
             INNER JOIN utilisateurs c ON t.conducteur_id = c.id
             WHERE r.id = ?
             LIMIT 1'
        );
        $stmt->execute([$reservationId]);
        return $stmt->fetch();
    }

    public function getDeclaredRevenueStats(): array
    {
        $sql = 'SELECT
                    (
                        SELECT COALESCE(SUM(COALESCE(r.paid_amount, r.prix_snapshot, t.prix)), 0)
                        FROM reservations r
                        INNER JOIN trajets t ON r.trajet_id = t.id
                        WHERE r.payment_status = "declare_paye"
                    ) AS total_global,
                    (
                        SELECT COALESCE(SUM(COALESCE(r.paid_amount, r.prix_snapshot, t.prix)), 0)
                        FROM reservations r
                        INNER JOIN trajets t ON r.trajet_id = t.id
                        WHERE r.payment_status = "declare_paye"
                          AND YEARWEEK(r.paid_at, 1) = YEARWEEK(CURDATE(), 1)
                    ) AS total_week,
                    (
                        SELECT COALESCE(SUM(COALESCE(r.paid_amount, r.prix_snapshot, t.prix)), 0)
                        FROM reservations r
                        INNER JOIN trajets t ON r.trajet_id = t.id
                        WHERE r.payment_status = "declare_paye"
                          AND DATE_FORMAT(r.paid_at, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")
                    ) AS total_month,
                    (SELECT COUNT(*) FROM trajets WHERE statut_trajet = "termine") AS completed_trips_count,
                    (SELECT COUNT(*) FROM reservations WHERE payment_status = "declare_paye") AS paid_reservations_count';

        $row = $this->pdo->query($sql)->fetch();
        if (!$row) {
            return [
                'total_global' => 0.0,
                'total_week' => 0.0,
                'total_month' => 0.0,
                'completed_trips_count' => 0,
                'paid_reservations_count' => 0,
            ];
        }

        return [
            'total_global' => (float) ($row['total_global'] ?? 0),
            'total_week' => (float) ($row['total_week'] ?? 0),
            'total_month' => (float) ($row['total_month'] ?? 0),
            'completed_trips_count' => (int) ($row['completed_trips_count'] ?? 0),
            'paid_reservations_count' => (int) ($row['paid_reservations_count'] ?? 0),
        ];
    }

    public function getRevenueByConducteur(): array
    {
        $sql = 'SELECT c.id AS conducteur_id,
                       c.nom AS conducteur_nom,
                       c.prenom AS conducteur_prenom,
                       c.email AS conducteur_email,
                       c.telephone AS conducteur_telephone,
                       COUNT(DISTINCT t.id) AS completed_trips_count,
                       COUNT(r.id) AS paid_reservations_count,
                       COALESCE(SUM(COALESCE(r.paid_amount, r.prix_snapshot, t.prix)), 0) AS declared_total
                FROM reservations r
                INNER JOIN trajets t ON r.trajet_id = t.id
                INNER JOIN utilisateurs c ON t.conducteur_id = c.id
                WHERE r.payment_status = "declare_paye"
                GROUP BY c.id
                ORDER BY declared_total DESC, c.nom ASC, c.prenom ASC';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getRevenueByWeek(): array
    {
        $sql = 'SELECT YEARWEEK(r.paid_at, 1) AS week_key,
                       DATE_SUB(DATE(r.paid_at), INTERVAL WEEKDAY(r.paid_at) DAY) AS week_start,
                       COUNT(r.id) AS paid_reservations_count,
                       COALESCE(SUM(COALESCE(r.paid_amount, r.prix_snapshot, t.prix)), 0) AS declared_total
                FROM reservations r
                INNER JOIN trajets t ON r.trajet_id = t.id
                WHERE r.payment_status = "declare_paye"
                GROUP BY week_key, week_start
                ORDER BY week_start DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getRevenueByMonth(): array
    {
        $sql = 'SELECT DATE_FORMAT(r.paid_at, "%Y-%m") AS month_key,
                       COUNT(r.id) AS paid_reservations_count,
                       COALESCE(SUM(COALESCE(r.paid_amount, r.prix_snapshot, t.prix)), 0) AS declared_total
                FROM reservations r
                INNER JOIN trajets t ON r.trajet_id = t.id
                WHERE r.payment_status = "declare_paye"
                GROUP BY month_key
                ORDER BY month_key DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getCompletedTripsFinancialRows(): array
    {
        $sql = 'SELECT t.id,
                       t.conducteur_id,
                       t.ville_depart,
                       t.ville_arrivee,
                       t.date_depart,
                       t.heure_depart,
                       t.completed_at,
                       c.nom AS conducteur_nom,
                       c.prenom AS conducteur_prenom,
                       c.email AS conducteur_email,
                       c.telephone AS conducteur_telephone,
                       COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN 1 ELSE 0 END), 0) AS confirmed_count,
                       COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN 1 ELSE 0 END), 0) AS paid_declared_count,
                       COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN COALESCE(r.paid_amount, r.prix_snapshot, t.prix) ELSE 0 END), 0) AS declared_total
                FROM trajets t
                INNER JOIN utilisateurs c ON t.conducteur_id = c.id
                LEFT JOIN reservations r ON r.trajet_id = t.id
                WHERE t.statut_trajet = "termine"
                GROUP BY t.id
                ORDER BY t.completed_at DESC, t.id DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    // Write

    public function createSafe(int $trajetId, int $passagerId, array $reservationData = []): array
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'SELECT conducteur_id, places_restantes, prix
                 FROM trajets
                 WHERE id = ?
                 FOR UPDATE'
            );
            $stmt->execute([$trajetId]);
            $trajet = $stmt->fetch();

            if (!$trajet) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Trajet introuvable.'];
            }

            if ((int) $trajet['places_restantes'] <= 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Plus de places disponibles.'];
            }

            if ((int) $trajet['conducteur_id'] === $passagerId) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Vous ne pouvez pas reserver votre propre trajet.'];
            }

            $check = $this->pdo->prepare(
                'SELECT COUNT(*) FROM reservations WHERE trajet_id = ? AND passager_id = ?'
            );
            $check->execute([$trajetId, $passagerId]);
            if ((int) $check->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Vous avez deja reserve ce trajet.'];
            }

            $reservationPrice = isset($reservationData['reservation_price'])
                ? (float) $reservationData['reservation_price']
                : null;
            $prixSnapshot = $reservationPrice !== null ? $reservationPrice : (float) $trajet['prix'];

            $insert = $this->pdo->prepare(
                'INSERT INTO reservations (
                    trajet_id, passager_id, statut, prix_snapshot,
                    reservation_point_lat, reservation_point_lng, reservation_point_type,
                    reservation_distance_km, reservation_duree_minutes, reservation_price
                 )
                 VALUES (?, ?, "en_attente", ?, ?, ?, ?, ?, ?, ?)'
            );
            $insert->execute([
                $trajetId,
                $passagerId,
                $prixSnapshot,
                $reservationData['reservation_point_lat'] ?? null,
                $reservationData['reservation_point_lng'] ?? null,
                $reservationData['reservation_point_type'] ?? null,
                $reservationData['reservation_distance_km'] ?? null,
                $reservationData['reservation_duree_minutes'] ?? null,
                $reservationPrice,
            ]);
            $reservationId = (int) $this->pdo->lastInsertId();

            $decrement = $this->pdo->prepare(
                'UPDATE trajets
                 SET places_restantes = places_restantes - 1
                 WHERE id = ?'
            );
            $decrement->execute([$trajetId]);

            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Demande de reservation envoyee. En attente de confirmation par le conducteur.',
                'reservation_id' => $reservationId,
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[RESERVATION ERROR] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la reservation. Reessayez.'];
        }
    }

    public function updateStatus(int $reservationId, string $statut, int $conducteurId): bool
    {
        $allowedStatuts = ['confirmee', 'refusee'];
        if (!in_array($statut, $allowedStatuts, true)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'SELECT r.id, r.trajet_id, r.statut
                 FROM reservations r
                 INNER JOIN trajets t ON r.trajet_id = t.id
                 WHERE r.id = ? AND t.conducteur_id = ?
                 FOR UPDATE'
            );
            $stmt->execute([$reservationId, $conducteurId]);
            $res = $stmt->fetch();

            if (!$res) {
                $this->pdo->rollBack();
                return false;
            }

            $updateSql = 'UPDATE reservations
                          SET statut = :statut_set,
                              confirmed_at = CASE WHEN :statut_confirm = "confirmee" THEN NOW() ELSE confirmed_at END,
                              refused_at = CASE WHEN :statut_refuse = "refusee" THEN NOW() ELSE refused_at END
                          WHERE id = :id';
            $update = $this->pdo->prepare($updateSql);
            $update->execute([
                ':statut_set' => $statut,
                ':statut_confirm' => $statut,
                ':statut_refuse' => $statut,
                ':id' => $reservationId,
            ]);

            if ($statut === 'refusee' && $res['statut'] === 'en_attente') {
                $restore = $this->pdo->prepare(
                    'UPDATE trajets
                     SET places_restantes = places_restantes + 1
                     WHERE id = ?'
                );
                $restore->execute([$res['trajet_id']]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[RESERVATION STATUS ERROR] ' . $e->getMessage());
            return false;
        }
    }

    public function cancelByPassager(int $reservationId, int $passagerId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'SELECT *
                 FROM reservations
                 WHERE id = ? AND passager_id = ?
                 FOR UPDATE'
            );
            $stmt->execute([$reservationId, $passagerId]);
            $res = $stmt->fetch();

            if (!$res) {
                $this->pdo->rollBack();
                return false;
            }

            $update = $this->pdo->prepare(
                'UPDATE reservations
                 SET statut = "annulee", cancelled_at = NOW()
                 WHERE id = ?'
            );
            $update->execute([$reservationId]);

            if (in_array((string) $res['statut'], ['en_attente', 'confirmee'], true)) {
                $restore = $this->pdo->prepare(
                    'UPDATE trajets
                     SET places_restantes = places_restantes + 1
                     WHERE id = ?'
                );
                $restore->execute([$res['trajet_id']]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[RESERVATION CANCEL ERROR] ' . $e->getMessage());
            return false;
        }
    }
}
