<?php
/**
 * controllers/AdminController.php
 * Tableau de bord administrateur
 */

class AdminController
{
    private User    $userModel;
    private Trajet  $trajetModel;
    private Reservation $resModel;

    public function __construct()
    {
        $this->userModel   = new User();
        $this->trajetModel = new Trajet();
        $this->resModel    = new Reservation();
        $this->requireAdmin();
    }

    private function redirect(string $url): void { header("Location: $url"); exit; }
    private function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

    /** Réserve l'accès aux admins uniquement */
    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            die('Accès réservé aux administrateurs.');
        }
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index(): void
    {
        $stats = [
            'users'        => $this->userModel->count(),
            'trajets'      => $this->trajetModel->count(),
            'reservations' => $this->resModel->count(),
        ];
        require_once ROOT_PATH . '/views/admin/dashboard.php';
    }

    // ── Gestion des utilisateurs ──────────────────────────────────────────────

    public function users(): void
    {
        $users = $this->userModel->getAll();
        require_once ROOT_PATH . '/views/admin/users.php';
    }

    public function deleteUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }

        $id = (int) ($_POST['id'] ?? 0);

        // Protéger l'admin contre l'auto-suppression
        if ($id === $_SESSION['user']['id']) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
        }

        $ok = $this->userModel->deleteById($id);
        $this->flash($ok ? 'success' : 'error', $ok ? 'Utilisateur supprimé.' : 'Erreur de suppression.');
        $this->redirect(BASE_URL . '/index.php?page=admin&action=users');
    }

    // ── Gestion des trajets ───────────────────────────────────────────────────

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
        $ok = $this->trajetModel->adminDelete($id);
        $this->flash($ok ? 'success' : 'error', $ok ? 'Trajet supprimé.' : 'Erreur de suppression.');
        $this->redirect(BASE_URL . '/index.php?page=admin&action=trajets');
    }
}
