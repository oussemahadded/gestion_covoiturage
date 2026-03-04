<?php
/**
 * models/Reservation.php
 * Modèle Réservation — logique critique avec transactions
 */

class Reservation
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Lecture ──────────────────────────────────────────────────────────────

    /** Réservations d'un passager */
    public function getByPassager(int $passagerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart, t.prix,
                    u.nom AS conducteur_nom, u.prenom AS conducteur_prenom, u.telephone AS conducteur_tel
             FROM reservations r
             INNER JOIN trajets t       ON r.trajet_id  = t.id
             INNER JOIN utilisateurs u  ON t.conducteur_id = u.id
             WHERE r.passager_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$passagerId]);
        return $stmt->fetchAll();
    }

    /** Demandes de réservation pour les trajets d'un conducteur */
    public function getByTrajetConducteur(int $conducteurId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart,
                    u.nom AS passager_nom, u.prenom AS passager_prenom, u.telephone AS passager_tel
             FROM reservations r
             INNER JOIN trajets t       ON r.trajet_id  = t.id
             INNER JOIN utilisateurs u  ON r.passager_id = u.id
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

    /** Vérifie si le passager a déjà réservé ce trajet */
    public function exists(int $trajetId, int $passagerId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE trajet_id = ? AND passager_id = ?'
        );
        $stmt->execute([$trajetId, $passagerId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    }

    // ── Écriture (avec gestion de la concurrence) ────────────────────────────

    /**
     * Crée une réservation de manière sécurisée.
     *
     * Prévention des race conditions :
     * - On utilise SELECT ... FOR UPDATE à l'intérieur d'une transaction.
     *   Cela pose un verrou exclusif sur la ligne du trajet PENDANT la transaction,
     *   empêchant deux requêtes concurrentes de lire simultanément places_restantes = 1
     *   et d'accepter toutes les deux la réservation alors qu'il ne reste qu'une place.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function createSafe(int $trajetId, int $passagerId): array
    {
        try {
            $this->pdo->beginTransaction();

            // Verrou exclusif sur la ligne du trajet
            $stmt = $this->pdo->prepare(
                'SELECT places_restantes FROM trajets WHERE id = ? FOR UPDATE'
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

            // Vérifier la double réservation
            $check = $this->pdo->prepare(
                'SELECT COUNT(*) FROM reservations WHERE trajet_id = ? AND passager_id = ?'
            );
            $check->execute([$trajetId, $passagerId]);
            if ((int) $check->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Vous avez déjà réservé ce trajet.'];
            }

            // Insérer la réservation
            $insert = $this->pdo->prepare(
                'INSERT INTO reservations (trajet_id, passager_id, statut) VALUES (?, ?, "en_attente")'
            );
            $insert->execute([$trajetId, $passagerId]);

            // Décrémenter les places restantes de manière atomique
            $decrement = $this->pdo->prepare(
                'UPDATE trajets SET places_restantes = places_restantes - 1 WHERE id = ?'
            );
            $decrement->execute([$trajetId]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Réservation envoyée avec succès.'];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('[RESERVATION ERROR] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la réservation. Réessayez.'];
        }
    }

    /**
     * Le conducteur accepte ou refuse une réservation.
     * Statut autorisé : 'confirmee' | 'refusee'
     */
    public function updateStatus(int $reservationId, string $statut, int $conducteurId): bool
    {
        $allowedStatuts = ['confirmee', 'refusee', 'annulee'];
        if (!in_array($statut, $allowedStatuts, true)) {
            return false;
        }

        // Si on refuse, on restitue la place
        if ($statut === 'refusee') {
            try {
                $this->pdo->beginTransaction();

                $stmt = $this->pdo->prepare(
                    'SELECT r.id, r.trajet_id, r.statut
                     FROM reservations r
                     INNER JOIN trajets t ON r.trajet_id = t.id
                     WHERE r.id = ? AND t.conducteur_id = ? FOR UPDATE'
                );
                $stmt->execute([$reservationId, $conducteurId]);
                $res = $stmt->fetch();

                if (!$res) { $this->pdo->rollBack(); return false; }

                $update = $this->pdo->prepare(
                    'UPDATE reservations SET statut = ? WHERE id = ?'
                );
                $update->execute([$statut, $reservationId]);

                // Restituer la place si elle était en_attente
                if ($res['statut'] === 'en_attente') {
                    $this->pdo->prepare(
                        'UPDATE trajets SET places_restantes = places_restantes + 1 WHERE id = ?'
                    )->execute([$res['trajet_id']]);
                }

                $this->pdo->commit();
                return true;

            } catch (PDOException $e) {
                $this->pdo->rollBack();
                error_log('[STATUS ERROR] ' . $e->getMessage());
                return false;
            }
        }

        // Confirmation (pas de modification des places)
        $stmt = $this->pdo->prepare(
            'UPDATE reservations r
             INNER JOIN trajets t ON r.trajet_id = t.id
             SET r.statut = ?
             WHERE r.id = ? AND t.conducteur_id = ?'
        );
        return $stmt->execute([$statut, $reservationId, $conducteurId]);
    }

    /** Annulation par le passager */
    public function cancelByPassager(int $reservationId, int $passagerId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'SELECT * FROM reservations WHERE id = ? AND passager_id = ? FOR UPDATE'
            );
            $stmt->execute([$reservationId, $passagerId]);
            $res = $stmt->fetch();

            if (!$res) { $this->pdo->rollBack(); return false; }

            $this->pdo->prepare('UPDATE reservations SET statut = "annulee" WHERE id = ?')
                      ->execute([$reservationId]);

            // Restituer la place si elle était en_attente ou confirmee
            if (in_array($res['statut'], ['en_attente', 'confirmee'], true)) {
                $this->pdo->prepare(
                    'UPDATE trajets SET places_restantes = places_restantes + 1 WHERE id = ?'
                )->execute([$res['trajet_id']]);
            }

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
