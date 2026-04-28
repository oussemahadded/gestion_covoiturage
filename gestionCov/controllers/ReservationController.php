<?php
/**
 * controllers/ReservationController.php
 * Reservation workflow for passengers and drivers.
 */

class ReservationController
{
    private Reservation $resModel;
    private Trajet $trajetModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->resModel = new Reservation();
        $this->trajetModel = new Trajet();
        $this->auditLog = new AuditLog();
    }

    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->flash('error', 'Vous devez être connecté.');
            $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
        }
    }

    private function requirePassenger(): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'], ['etudiant', 'professeur'], true)) {
            http_response_code(403);
            die('Accès interdit.');
        }
    }

    private function requireConducteur(): void
    {
        $this->requireAuth();
        if (($_SESSION['user']['role'] ?? '') !== 'conducteur') {
            http_response_code(403);
            die('Accès interdit.');
        }
    }

    private function audit(string $action, string $entityType, ?int $entityId = null, array $details = []): void
    {
        $userId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;

        try {
            $ok = $this->auditLog->create($userId, $action, $entityType, $entityId, $details);
            if (!$ok) {
                error_log('[AUDIT] Failed to write log: ' . $action);
            }
        } catch (Throwable $e) {
            error_log('[AUDIT] ' . $e->getMessage());
        }
    }

    public function book(): void
    {
        $this->requirePassenger();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }

        $trajetId = (int) ($_POST['trajet_id'] ?? 0);
        $passagerId = (int) ($_SESSION['user']['id'] ?? 0);
        $trajet = $this->trajetModel->findById($trajetId);

        if (!$trajet) {
            $this->flash('error', 'Trajet introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }

        if ((int) $trajet['conducteur_id'] === $passagerId) {
            $this->flash('error', 'Vous ne pouvez pas réserver votre propre trajet.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        $result = $this->resModel->createSafe($trajetId, $passagerId);
        if (!empty($result['success'])) {
            $this->audit(
                'reservation_created',
                'reservation',
                isset($result['reservation_id']) ? (int) $result['reservation_id'] : null,
                [
                    'trajet_id' => $trajetId,
                    'passager_id' => $passagerId,
                    'conducteur_id' => (int) $trajet['conducteur_id'],
                    'statut' => 'en_attente',
                    'prix_par_passager' => (float) $trajet['prix'],
                ]
            );
        }

        $this->flash(!empty($result['success']) ? 'success' : 'error', (string) ($result['message'] ?? 'Erreur.'));
        $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
    }

    public function myReservations(): void
    {
        $this->requirePassenger();
        $reservations = $this->resModel->getByPassager((int) $_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/reservations/my_reservations.php';
    }

    public function driverRequests(): void
    {
        $this->requireConducteur();
        $requests = $this->resModel->getByTrajetConducteur((int) $_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/reservations/driver_requests.php';
    }

    public function updateStatus(): void
    {
        $this->requireConducteur();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
        }

        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';

        if (!in_array($statut, ['confirmee', 'refusee'], true)) {
            $this->flash('error', 'Statut invalide.');
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
        }

        $existingReservation = $this->resModel->findById($reservationId);
        $ok = $this->resModel->updateStatus($reservationId, $statut, (int) $_SESSION['user']['id']);
        if ($ok) {
            $this->audit(
                $statut === 'confirmee' ? 'reservation_confirmed' : 'reservation_refused',
                'reservation',
                $reservationId,
                [
                    'reservation_id' => $reservationId,
                    'trajet_id' => isset($existingReservation['trajet_id']) ? (int) $existingReservation['trajet_id'] : null,
                    'passager_id' => isset($existingReservation['passager_id']) ? (int) $existingReservation['passager_id'] : null,
                    'previous_status' => (string) ($existingReservation['statut'] ?? ''),
                    'new_status' => $statut,
                    'conducteur_id' => (int) $_SESSION['user']['id'],
                ]
            );
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Réservation mise à jour.' : 'Erreur lors de la mise à jour.');
        $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
    }

    public function cancel(): void
    {
        $this->requirePassenger();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=myReservations');
        }

        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $passagerId = (int) $_SESSION['user']['id'];
        $existingReservation = $this->resModel->findById($reservationId);
        $ok = $this->resModel->cancelByPassager($reservationId, $passagerId);

        if ($ok) {
            $this->audit(
                'reservation_cancelled',
                'reservation',
                $reservationId,
                [
                    'reservation_id' => $reservationId,
                    'trajet_id' => isset($existingReservation['trajet_id']) ? (int) $existingReservation['trajet_id'] : null,
                    'passager_id' => isset($existingReservation['passager_id']) ? (int) $existingReservation['passager_id'] : $passagerId,
                    'previous_status' => (string) ($existingReservation['statut'] ?? ''),
                    'new_status' => 'annulee',
                ]
            );
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Réservation annulée.' : "Impossible d'annuler.");
        $this->redirect(BASE_URL . '/index.php?page=reservation&action=myReservations');
    }
}
