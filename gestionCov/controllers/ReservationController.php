<?php
/**
 * controllers/ReservationController.php
 * Gestion des réservations — passager & conducteur
 */

class ReservationController
{
    private Reservation $resModel;

    public function __construct()
    {
        $this->resModel = new Reservation();
    }

    private function redirect(string $url): void { header("Location: $url"); exit; }
    private function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->flash('error', 'Vous devez être connecté.');
            $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
        }
    }

    private function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            http_response_code(403);
            die('Accès interdit.');
        }
    }

    // ── Réserver un trajet (passager) ─────────────────────────────────────────

    public function book(): void
    {
        $this->requireRole('passager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }

        $trajetId   = (int) ($_POST['trajet_id'] ?? 0);
        $passagerId = $_SESSION['user']['id'];

        $result = $this->resModel->createSafe($trajetId, $passagerId);

        $this->flash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
    }

    // ── Mes réservations (passager) ───────────────────────────────────────────

    public function myReservations(): void
    {
        $this->requireRole('passager');
        $reservations = $this->resModel->getByPassager($_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/reservations/my_reservations.php';
    }

    // ── Demandes reçues (conducteur) ──────────────────────────────────────────

    public function driverRequests(): void
    {
        $this->requireRole('conducteur');
        $requests = $this->resModel->getByTrajetConducteur($_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/reservations/driver_requests.php';
    }

    // ── Changer le statut (conducteur : confirmer / refuser) ──────────────────

    public function updateStatus(): void
    {
        $this->requireRole('conducteur');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
        }

        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $statut        = $_POST['statut'] ?? '';

        if (!in_array($statut, ['confirmee', 'refusee'], true)) {
            $this->flash('error', 'Statut invalide.');
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
        }

        $ok = $this->resModel->updateStatus($reservationId, $statut, $_SESSION['user']['id']);
        $this->flash($ok ? 'success' : 'error', $ok ? 'Réservation mise à jour.' : 'Erreur lors de la mise à jour.');
        $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
    }

    // ── Annuler une réservation (passager) ────────────────────────────────────

    public function cancel(): void
    {
        $this->requireRole('passager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=myReservations');
        }

        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $ok = $this->resModel->cancelByPassager($reservationId, $_SESSION['user']['id']);
        $this->flash($ok ? 'success' : 'error', $ok ? 'Réservation annulée.' : 'Impossible d\'annuler.');
        $this->redirect(BASE_URL . '/index.php?page=reservation&action=myReservations');
    }
}
