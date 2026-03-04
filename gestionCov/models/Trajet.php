<?php
/**
 * models/Trajet.php
 * Modèle Trajet — accès aux données PDO
 */

class Trajet
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Lecture ──────────────────────────────────────────────────────────────

    /** Tous les trajets (admin ou accueil) */
    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT t.*, u.nom, u.prenom, u.telephone
             FROM trajets t
             INNER JOIN utilisateurs u ON t.conducteur_id = u.id
             ORDER BY t.date_depart DESC, t.heure_depart ASC'
        )->fetchAll();
    }

    /** Trajets d'un conducteur spécifique */
    public function getByConducteur(int $conducteurId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM trajets WHERE conducteur_id = ? ORDER BY date_depart DESC'
        );
        $stmt->execute([$conducteurId]);
        return $stmt->fetchAll();
    }

    /** Détail d'un trajet */
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

    /**
     * Recherche de trajets par ville et date
     * L'index idx_recherche_trajet est utilisé automatiquement.
     */
    public function search(string $depart, string $arrivee, string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, u.nom, u.prenom, u.telephone
             FROM trajets t
             INNER JOIN utilisateurs u ON t.conducteur_id = u.id
             WHERE t.ville_depart    LIKE ?
               AND t.ville_arrivee  LIKE ?
               AND t.date_depart    = ?
               AND t.places_restantes > 0
             ORDER BY t.heure_depart ASC'
        );
        $stmt->execute(["%$depart%", "%$arrivee%", $date]);
        return $stmt->fetchAll();
    }

    /** Nombre total de trajets (admin) */
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM trajets')->fetchColumn();
    }

    // ── Écriture ─────────────────────────────────────────────────────────────

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
            $data['places_total'],   // places_restantes initialisé à places_total
            $data['description'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE trajets
             SET ville_depart=?, ville_arrivee=?, date_depart=?, heure_depart=?, prix=?,
                 places_total=?, description=?
             WHERE id=? AND conducteur_id=?'
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

    /** Suppression admin (sans vérification du conducteur) */
    public function adminDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM trajets WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
