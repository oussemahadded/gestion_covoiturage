<?php
/**
 * models/Trajet.php
 * Trajet model using PDO.
 */

class Trajet
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Read

    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT t.*, u.nom, u.prenom, u.telephone
             FROM trajets t
             INNER JOIN utilisateurs u ON t.conducteur_id = u.id
             ORDER BY t.date_depart DESC, t.heure_depart ASC'
        )->fetchAll();
    }

    public function getByConducteur(int $conducteurId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*,
                    COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN 1 ELSE 0 END), 0) AS confirmed_count,
                    COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN 1 ELSE 0 END), 0) AS paid_declared_count,
                    COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN COALESCE(r.paid_amount, r.prix_snapshot, t.prix) ELSE 0 END), 0) AS declared_total
             FROM trajets t
             LEFT JOIN reservations r ON r.trajet_id = t.id
             WHERE t.conducteur_id = ?
             GROUP BY t.id
             ORDER BY t.date_depart DESC, t.heure_depart DESC'
        );
        $stmt->execute([$conducteurId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, u.nom, u.prenom, u.email, u.telephone
             FROM trajets t
             INNER JOIN utilisateurs u ON t.conducteur_id = u.id
             WHERE t.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search(string $depart, string $arrivee, string $date): array
    {
        $sql = 'SELECT t.*, u.nom, u.prenom, u.telephone
                FROM trajets t
                INNER JOIN utilisateurs u ON t.conducteur_id = u.id
                WHERE t.ville_depart LIKE :depart
                  AND t.ville_arrivee LIKE :arrivee
                  AND t.places_restantes > 0';

        $params = [
            ':depart' => '%' . $depart . '%',
            ':arrivee' => '%' . $arrivee . '%',
        ];

        if ($date !== '') {
            $sql .= ' AND t.date_depart = :date_depart';
            $params[':date_depart'] = $date;
        }

        $sql .= ' ORDER BY t.date_depart ASC, t.heure_depart ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM trajets')->fetchColumn();
    }

    public function getTraceabilityRows(array $filters = []): array
    {
        $sql = 'SELECT t.id,
                       t.conducteur_id,
                       t.ville_depart,
                       t.ville_arrivee,
                       t.date_depart,
                       t.heure_depart,
                       t.distance_km,
                       t.duree_minutes,
                       t.prix_par_km,
                       t.point_lat,
                       t.point_lng,
                       t.route_geometry,
                       t.route_provider,
                       t.route_calculated_at,
                       t.prix,
                       t.places_total,
                       t.places_restantes,
                       t.description,
                       t.statut_trajet,
                       t.completed_at,
                       t.created_at,
                       t.updated_at,
                       c.nom AS conducteur_nom,
                       c.prenom AS conducteur_prenom,
                       c.email AS conducteur_email,
                       c.telephone AS conducteur_telephone,
                       COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN 1 ELSE 0 END), 0) AS confirmed_count,
                       COALESCE(SUM(CASE WHEN r.statut = "en_attente" THEN 1 ELSE 0 END), 0) AS pending_count,
                       COALESCE(SUM(CASE WHEN r.statut = "refusee" THEN 1 ELSE 0 END), 0) AS refused_count,
                       COALESCE(SUM(CASE WHEN r.statut = "annulee" THEN 1 ELSE 0 END), 0) AS cancelled_count,
                       COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN COALESCE(r.prix_snapshot, t.prix) ELSE 0 END), 0) AS estimated_confirmed_revenue,
                       COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN 1 ELSE 0 END), 0) AS paid_declared_count,
                       COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN COALESCE(r.paid_amount, r.prix_snapshot, t.prix) ELSE 0 END), 0) AS declared_total
                FROM trajets t
                INNER JOIN utilisateurs c ON t.conducteur_id = c.id
                LEFT JOIN reservations r ON r.trajet_id = t.id
                WHERE 1=1';

        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= ' AND t.date_depart >= :date_from';
            $params[':date_from'] = (string) $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND t.date_depart <= :date_to';
            $params[':date_to'] = (string) $filters['date_to'];
        }

        if (!empty($filters['email'])) {
            $sql .= ' AND c.email LIKE :email';
            $params[':email'] = '%' . (string) $filters['email'] . '%';
        }

        if (!empty($filters['route_query'])) {
            $sql .= ' AND (
                        t.ville_depart LIKE :route_query
                        OR t.ville_arrivee LIKE :route_query
                      )';
            $params[':route_query'] = '%' . (string) $filters['route_query'] . '%';
        }

        $sql .= ' GROUP BY t.id
                  ORDER BY t.date_depart DESC, t.heure_depart DESC';

        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 300;
        $limit = max(1, min(1000, $limit));
        $sql .= ' LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findDetailedForAdmin(int $trajetId): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*,
                    c.nom AS conducteur_nom,
                    c.prenom AS conducteur_prenom,
                    c.email AS conducteur_email,
                    c.telephone AS conducteur_telephone,
                    c.role AS conducteur_role,
                    c.statut_compte AS conducteur_statut_compte,
                    c.created_at AS conducteur_created_at
             FROM trajets t
             INNER JOIN utilisateurs c ON t.conducteur_id = c.id
             WHERE t.id = ?
             LIMIT 1'
        );
        $stmt->execute([$trajetId]);
        return $stmt->fetch();
    }

    public function getReservationSummaryForTrip(int $trajetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total_reservations,
                COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN 1 ELSE 0 END), 0) AS confirmed_count,
                COALESCE(SUM(CASE WHEN r.statut = "en_attente" THEN 1 ELSE 0 END), 0) AS pending_count,
                COALESCE(SUM(CASE WHEN r.statut = "refusee" THEN 1 ELSE 0 END), 0) AS refused_count,
                COALESCE(SUM(CASE WHEN r.statut = "annulee" THEN 1 ELSE 0 END), 0) AS cancelled_count,
                COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN COALESCE(r.prix_snapshot, t.prix) ELSE 0 END), 0) AS estimated_confirmed_revenue,
                COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN 1 ELSE 0 END), 0) AS paid_declared_count,
                COALESCE(SUM(CASE WHEN r.payment_status = "declare_paye" THEN COALESCE(r.paid_amount, r.prix_snapshot, t.prix) ELSE 0 END), 0) AS declared_total
             FROM reservations r
             INNER JOIN trajets t ON r.trajet_id = t.id
             WHERE r.trajet_id = ?'
        );
        $stmt->execute([$trajetId]);
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'total_reservations' => 0,
                'confirmed_count' => 0,
                'pending_count' => 0,
                'refused_count' => 0,
                'cancelled_count' => 0,
                'estimated_confirmed_revenue' => 0.0,
                'paid_declared_count' => 0,
                'declared_total' => 0.0,
            ];
        }

        return [
            'total_reservations' => (int) ($row['total_reservations'] ?? 0),
            'confirmed_count' => (int) ($row['confirmed_count'] ?? 0),
            'pending_count' => (int) ($row['pending_count'] ?? 0),
            'refused_count' => (int) ($row['refused_count'] ?? 0),
            'cancelled_count' => (int) ($row['cancelled_count'] ?? 0),
            'estimated_confirmed_revenue' => (float) ($row['estimated_confirmed_revenue'] ?? 0),
            'paid_declared_count' => (int) ($row['paid_declared_count'] ?? 0),
            'declared_total' => (float) ($row['declared_total'] ?? 0),
        ];
    }

    // Write

    public function completeTrip(int $trajetId, int $conducteurId): array
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'SELECT id, conducteur_id, date_depart, heure_depart, prix, statut_trajet,
                        (TIMESTAMP(date_depart, heure_depart) <= NOW()) AS is_past_departure
                 FROM trajets
                 WHERE id = ?
                 FOR UPDATE'
            );
            $stmt->execute([$trajetId]);
            $trajet = $stmt->fetch();

            if (!$trajet || (int) $trajet['conducteur_id'] !== $conducteurId) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Trajet introuvable ou accès refusé.',
                    'completed_reservations' => 0,
                    'declared_total' => 0.0,
                ];
            }

            if (($trajet['statut_trajet'] ?? 'publie') !== 'publie') {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => (($trajet['statut_trajet'] ?? '') === 'termine')
                        ? 'Ce trajet est déjà terminé.'
                        : 'Ce trajet ne peut pas être marqué terminé.',
                    'completed_reservations' => 0,
                    'declared_total' => 0.0,
                ];
            }

            if ((int) ($trajet['is_past_departure'] ?? 0) !== 1) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => "Ce trajet ne peut être marqué terminé qu'après son horaire de départ.",
                    'completed_reservations' => 0,
                    'declared_total' => 0.0,
                ];
            }

            $confirmedStmt = $this->pdo->prepare(
                'SELECT id, COALESCE(prix_snapshot, ?) AS declared_amount
                 FROM reservations
                 WHERE trajet_id = ?
                   AND statut = "confirmee"
                 FOR UPDATE'
            );
            $confirmedStmt->execute([(float) $trajet['prix'], $trajetId]);
            $confirmedRows = $confirmedStmt->fetchAll();
            $completedReservations = count($confirmedRows);

            if ($completedReservations === 0) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucune réservation confirmée à déclarer pour ce trajet.',
                    'completed_reservations' => 0,
                    'declared_total' => 0.0,
                ];
            }

            $declaredTotal = 0.0;
            foreach ($confirmedRows as $row) {
                $declaredTotal += (float) ($row['declared_amount'] ?? 0);
            }

            $updateTrip = $this->pdo->prepare(
                'UPDATE trajets
                 SET statut_trajet = "termine",
                     completed_at = NOW()
                 WHERE id = ?'
            );
            $updateTrip->execute([$trajetId]);

            $updateReservations = $this->pdo->prepare(
                'UPDATE reservations r
                 INNER JOIN trajets t ON r.trajet_id = t.id
                 SET r.payment_status = "declare_paye",
                     r.paid_amount = COALESCE(r.prix_snapshot, t.prix),
                     r.paid_at = NOW()
                 WHERE r.trajet_id = ?
                   AND r.statut = "confirmee"'
            );
            $updateReservations->execute([$trajetId]);

            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Trajet terminé. Paiements en espèces déclarés pour les réservations confirmées.',
                'completed_reservations' => $completedReservations,
                'declared_total' => $declaredTotal,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[TRIP COMPLETE ERROR] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la déclaration du trajet terminé.',
                'completed_reservations' => 0,
                'declared_total' => 0.0,
            ];
        }
    }

    public function create(array $data): int
    {
        $routeCalculatedAt = null;
        if ($data['distance_km'] !== null || !empty($data['route_geometry'])) {
            $routeCalculatedAt = date('Y-m-d H:i:s');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO trajets
             (conducteur_id, ville_depart, ville_arrivee, date_depart, heure_depart,
              distance_km, duree_minutes, prix_par_km, point_lat, point_lng,
              route_geometry, route_provider, route_calculated_at,
              prix, places_total, places_restantes, description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['conducteur_id'],
            $data['ville_depart'],
            $data['ville_arrivee'],
            $data['date_depart'],
            $data['heure_depart'],
            $data['distance_km'],
            $data['duree_minutes'],
            $data['prix_par_km'],
            $data['point_lat'],
            $data['point_lng'],
            $data['route_geometry'],
            $data['route_provider'],
            $routeCalculatedAt,
            $data['prix'],
            $data['places_total'],
            $data['places_total'],
            $data['description'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $routeCalculatedAt = null;
        if ($data['distance_km'] !== null || !empty($data['route_geometry'])) {
            $routeCalculatedAt = date('Y-m-d H:i:s');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE trajets
             SET ville_depart = ?, ville_arrivee = ?, date_depart = ?, heure_depart = ?,
                 distance_km = ?, duree_minutes = ?, prix_par_km = ?, point_lat = ?, point_lng = ?,
                 route_geometry = ?, route_provider = ?, route_calculated_at = ?,
                 prix = ?, places_total = ?, description = ?
             WHERE id = ? AND conducteur_id = ?'
        );
        return $stmt->execute([
            $data['ville_depart'],
            $data['ville_arrivee'],
            $data['date_depart'],
            $data['heure_depart'],
            $data['distance_km'],
            $data['duree_minutes'],
            $data['prix_par_km'],
            $data['point_lat'],
            $data['point_lng'],
            $data['route_geometry'],
            $data['route_provider'],
            $routeCalculatedAt,
            $data['prix'],
            $data['places_total'],
            $data['description'] ?? null,
            $id,
            $data['conducteur_id'],
        ]);
    }

    public function delete(int $id, int $conducteurId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM trajets WHERE id = ? AND conducteur_id = ?'
        );
        return $stmt->execute([$id, $conducteurId]);
    }

    public function adminDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM trajets WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
