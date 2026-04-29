<?php
/**
 * controllers/AdminController.php
 * Administration dashboard and traceability pages.
 */

class AdminController
{
    private User $userModel;
    private Trajet $trajetModel;
    private Reservation $resModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->userModel = new User();
        $this->trajetModel = new Trajet();
        $this->resModel = new Reservation();
        $this->auditLog = new AuditLog();
        $this->requireAdmin();
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

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die("Accès réservé à l'administration.");
        }
    }

    private function ensurePostToUsersList(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }
    }

    private function currentUserId(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    private function normalizeStatus(array $user): string
    {
        $status = (string) ($user['statut_compte'] ?? 'actif');
        return in_array($status, ['actif', 'en_attente', 'refuse', 'desactive'], true) ? $status : 'actif';
    }

    private function audit(string $action, string $entityType, ?int $entityId = null, array $details = []): void
    {
        $adminId = $this->currentUserId();

        try {
            $ok = $this->auditLog->create($adminId > 0 ? $adminId : null, $action, $entityType, $entityId, $details);
            if (!$ok) {
                error_log('[AUDIT] Failed to write log: ' . $action);
            }
        } catch (Throwable $e) {
            error_log('[AUDIT] ' . $e->getMessage());
        }
    }

    /**
     * @return array{valid:bool,id:int,user:array|null}
     */
    private function resolveTargetUserFromPost(): array
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('error', 'Utilisateur invalide.');
            return ['valid' => false, 'id' => 0, 'user' => null];
        }

        if ($id === $this->currentUserId()) {
            $this->flash('error', 'Vous ne pouvez pas modifier votre propre compte.');
            return ['valid' => false, 'id' => $id, 'user' => null];
        }

        $target = $this->userModel->findById($id);
        if (!$target) {
            $this->flash('error', 'Utilisateur introuvable.');
            return ['valid' => false, 'id' => $id, 'user' => null];
        }

        return ['valid' => true, 'id' => $id, 'user' => $target];
    }

    public function index(): void
    {
        $stats = [
            'users' => $this->userModel->count(),
            'trajets' => $this->trajetModel->count(),
            'reservations' => $this->resModel->count(),
        ];

        require_once ROOT_PATH . '/views/admin/dashboard.php';
    }

    public function users(): void
    {
        $users = $this->userModel->getAll();
        require_once ROOT_PATH . '/views/admin/users.php';
    }

    public function activateUser(): void
    {
        $this->ensurePostToUsersList();
        $resolved = $this->resolveTargetUserFromPost();
        if (!$resolved['valid']) {
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }

        $target = (array) $resolved['user'];
        $previousStatus = $this->normalizeStatus($target);
        $ok = $this->userModel->updateAccountStatus($resolved['id'], 'actif');

        if ($ok) {
            if (($target['role'] ?? '') === 'conducteur' && $previousStatus === 'en_attente') {
                $this->audit('conducteur_approved', 'utilisateur', (int) $resolved['id'], [
                    'target_role' => $target['role'] ?? null,
                    'previous_status' => $previousStatus,
                    'new_status' => 'actif',
                ]);
            } else {
                $this->audit('account_activated', 'utilisateur', (int) $resolved['id'], [
                    'target_role' => $target['role'] ?? null,
                    'previous_status' => $previousStatus,
                    'new_status' => 'actif',
                ]);
            }
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Compte activé.' : 'Impossible d’activer ce compte.');
        $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
    }

    // Backward-compatible entry point
    public function approveUser(): void
    {
        $this->activateUser();
    }

    public function refuseUser(): void
    {
        $this->ensurePostToUsersList();
        $resolved = $this->resolveTargetUserFromPost();
        if (!$resolved['valid']) {
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }

        $target = (array) $resolved['user'];
        $status = $this->normalizeStatus($target);
        if (($target['role'] ?? '') !== 'conducteur' || $status !== 'en_attente') {
            $this->flash('error', 'Seuls les comptes conducteur en attente peuvent être refusés.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }

        $ok = $this->userModel->updateAccountStatus($resolved['id'], 'refuse');
        if ($ok) {
            $this->audit('conducteur_refused', 'utilisateur', (int) $resolved['id'], [
                'target_role' => $target['role'] ?? null,
                'previous_status' => $status,
                'new_status' => 'refuse',
            ]);
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Compte conducteur refusé.' : 'Impossible de refuser ce compte.');
        $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
    }

    public function deactivateUser(): void
    {
        $this->ensurePostToUsersList();
        $resolved = $this->resolveTargetUserFromPost();
        if (!$resolved['valid']) {
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }

        $target = (array) $resolved['user'];
        $status = $this->normalizeStatus($target);
        if ($status !== 'actif') {
            $this->flash('error', 'Seuls les comptes actifs peuvent être désactivés.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }

        $ok = $this->userModel->updateAccountStatus($resolved['id'], 'desactive');
        if ($ok) {
            $this->audit('account_deactivated', 'utilisateur', (int) $resolved['id'], [
                'target_role' => $target['role'] ?? null,
                'previous_status' => $status,
                'new_status' => 'desactive',
            ]);
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Compte désactivé.' : 'Impossible de désactiver ce compte.');
        $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
    }

    public function deleteUser(): void
    {
        $this->ensurePostToUsersList();
        $this->flash('error', 'La suppression des comptes est désactivée. Utilisez la désactivation.');
        $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
    }

    public function trajets(): void
    {
        $trajets = $this->trajetModel->getAll();
        require_once ROOT_PATH . '/views/admin/trajets.php';
    }

    public function deleteTrajet(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=admin&action=trajets');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $toDelete = $this->trajetModel->findById($id);
        $ok = $this->trajetModel->adminDelete($id);

        if ($ok) {
            $this->audit('trajet_deleted', 'trajet', $id, [
                'deleted_by_admin' => $this->currentUserId(),
                'ville_depart' => $toDelete['ville_depart'] ?? null,
                'ville_arrivee' => $toDelete['ville_arrivee'] ?? null,
                'date_depart' => $toDelete['date_depart'] ?? null,
            ]);
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Trajet supprimé.' : 'Erreur de suppression.');
        $this->redirect(BASE_URL . '/index.php?page=admin&action=trajets');
    }

    public function traceability(): void
    {
        $statusFilter = trim((string) ($_GET['status'] ?? ''));
        $roleFilter = trim((string) ($_GET['role'] ?? ''));
        $emailFilter = trim((string) ($_GET['email'] ?? ''));
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));

        $reservationFilters = [
            'statut' => $statusFilter,
            'role' => $roleFilter,
            'email' => $emailFilter,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => 300,
        ];

        $tripFilters = [
            'email' => $emailFilter,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => 300,
        ];

        $auditFilters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'query' => $emailFilter,
            'limit' => 150,
        ];

        $traceStats = $this->resModel->getTraceabilityStats();
        $reservationRows = $this->resModel->getTraceabilityRows($reservationFilters);
        $tripRows = $this->trajetModel->getTraceabilityRows($tripFilters);
        $auditRows = $this->auditLog->search($auditFilters);

        require_once ROOT_PATH . '/views/admin/traceability.php';
    }

    public function finances(): void
    {
        $financeStats = $this->resModel->getDeclaredRevenueStats();
        $revenueByConducteur = $this->resModel->getRevenueByConducteur();
        $revenueByWeek = $this->resModel->getRevenueByWeek();
        $revenueByMonth = $this->resModel->getRevenueByMonth();
        $completedTripRows = $this->resModel->getCompletedTripsFinancialRows();

        require_once ROOT_PATH . '/views/admin/finances.php';
    }

    public function tripDetails(): void
    {
        $tripId = (int) ($_GET['id'] ?? 0);
        if ($tripId <= 0) {
            $this->flash('error', 'Trajet invalide.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=traceability');
        }

        $trip = $this->trajetModel->findDetailedForAdmin($tripId);
        if (!$trip) {
            $this->flash('error', 'Trajet introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=traceability');
        }

        $tripReservationRows = $this->resModel->getTraceabilityRows([
            'trajet_id' => $tripId,
            'limit' => 1000,
        ]);
        $tripSummary = $this->trajetModel->getReservationSummaryForTrip($tripId);

        require_once ROOT_PATH . '/views/admin/trip_details.php';
    }

    public function reservationDetails(): void
    {
        $reservationId = (int) ($_GET['id'] ?? 0);
        if ($reservationId <= 0) {
            $this->flash('error', 'Réservation invalide.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=traceability');
        }

        $reservation = $this->resModel->findDetailedById($reservationId);
        if (!$reservation) {
            $this->flash('error', 'Réservation introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=traceability');
        }

        require_once ROOT_PATH . '/views/admin/reservation_details.php';
    }

    public function settings(): void
    {
        require_once ROOT_PATH . '/models/AppSetting.php';
        $settingModel = new AppSetting();
        $currentPrixParKm = $settingModel->getPrixParKm();

        require_once ROOT_PATH . '/views/admin/settings.php';
    }

    public function updatePricing(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=admin&action=settings');
        }

        $prixParKmRaw = trim((string) ($_POST['prix_par_km'] ?? ''));
        if ($prixParKmRaw === '' || !is_numeric($prixParKmRaw) || (float) $prixParKmRaw <= 0) {
            $this->flash('error', 'Tarif kilométrique invalide.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=settings');
        }

        $prixParKm = (float) $prixParKmRaw;

        require_once ROOT_PATH . '/models/AppSetting.php';
        $settingModel = new AppSetting();
        $ok = $settingModel->updatePrixParKm($prixParKm);

        if ($ok) {
            $this->audit('settings_updated', 'app_setting', null, [
                'setting_key' => 'prix_par_km',
                'new_value' => $prixParKm,
            ]);
            $this->flash('success', 'Tarif kilométrique mis à jour avec succès.');
        } else {
            $this->flash('error', 'Impossible de mettre à jour le tarif kilométrique.');
        }

        $this->redirect(BASE_URL . '/index.php?page=admin&action=settings');
    }
}
