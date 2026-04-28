<?php
/**
 * models/Avis.php
 * Modèle Avis (Reviews) — Bonus
 */

class Avis
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Avis pour un trajet spécifique */
    public function getByTrajet(int $trajetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, u.nom, u.prenom
             FROM avis a
             INNER JOIN utilisateurs u ON a.passager_id = u.id
             WHERE a.trajet_id = ?
             ORDER BY a.created_at DESC'
        );
        $stmt->execute([$trajetId]);
        return $stmt->fetchAll();
    }

    /** Vérifie si le passager a déjà posté un avis pour ce trajet */
    public function existsForPassager(int $trajetId, int $passagerId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM avis WHERE trajet_id = ? AND passager_id = ?'
        );
        $stmt->execute([$trajetId, $passagerId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie que le passager a bien eu une réservation confirmée pour ce trajet
     * (condition obligatoire pour poster un avis)
     */
    public function canReview(int $trajetId, int $passagerId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations
             WHERE trajet_id = ? AND passager_id = ? AND statut = "confirmee"'
        );
        $stmt->execute([$trajetId, $passagerId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(int $trajetId, int $passagerId, int $note, string $commentaire): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO avis (trajet_id, passager_id, note, commentaire) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$trajetId, $passagerId, $note, $commentaire]);
    }

    /** Note moyenne d'un conducteur (toutes ses courses) */
    public function getAverageForConducteur(int $conducteurId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT AVG(a.note)
             FROM avis a
             INNER JOIN trajets t ON a.trajet_id = t.id
             WHERE t.conducteur_id = ?'
        );
        $stmt->execute([$conducteurId]);
        $avg = $stmt->fetchColumn();
        return $avg ? round((float) $avg, 1) : 0.0;
    }
}
