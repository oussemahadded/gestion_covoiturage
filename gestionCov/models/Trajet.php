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
            'SELECT * FROM trajets WHERE conducteur_id = ? ORDER BY date_depart DESC'
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
                       t.prix,
                       t.places_total,
                       t.places_restantes,
                       t.description,
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
                       COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN COALESCE(r.prix_snapshot, t.prix) ELSE 0 END), 0) AS estimated_confirmed_revenue
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
                COALESCE(SUM(CASE WHEN r.statut = "confirmee" THEN COALESCE(r.prix_snapshot, t.prix) ELSE 0 END), 0) AS estimated_confirmed_revenue
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
            ];
        }

        return [
            'total_reservations' => (int) ($row['total_reservations'] ?? 0),
            'confirmed_count' => (int) ($row['confirmed_count'] ?? 0),
            'pending_count' => (int) ($row['pending_count'] ?? 0),
            'refused_count' => (int) ($row['refused_count'] ?? 0),
            'cancelled_count' => (int) ($row['cancelled_count'] ?? 0),
            'estimated_confirmed_revenue' => (float) ($row['estimated_confirmed_revenue'] ?? 0),
        ];
    }

    // Write

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO trajets
             (conducteur_id, ville_depart, ville_arrivee, date_depart, heure_depart, prix, places_total, places_restantes, description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['conducteur_id'],
            $data['ville_depart'],
            $data['ville_arrivee'],
            $data['date_depart'],
            $data['heure_depart'],
            $data['prix'],
            $data['places_total'],
            $data['places_total'],
            $data['description'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE trajets
             SET ville_depart = ?, ville_arrivee = ?, date_depart = ?, heure_depart = ?, prix = ?,
                 places_total = ?, description = ?
             WHERE id = ? AND conducteur_id = ?'
        );
        return $stmt->execute([
            $data['ville_depart'],
            $data['ville_arrivee'],
            $data['date_depart'],
            $data['heure_depart'],
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

