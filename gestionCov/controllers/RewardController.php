<?php
/**
 * controllers/RewardController.php
 * Reward & Remise system — CHAYA3NI
 *
 * Routes:
 *   ?page=reward&action=index        → conducteur reward dashboard card
 *   ?page=reward&action=admin        → admin "Conducteurs Récompensés" list
 */

class RewardController
{
    private Reward $rewardModel;

    public function __construct()
    {
        $this->rewardModel = new Reward();
        $this->requireAuth();
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
        }
    }

    private function requireAdmin(): void
    {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die('Accès réservé à l\'administration.');
        }
    }

    private function requireConducteur(): void
    {
        if (($_SESSION['user']['role'] ?? '') !== 'conducteur') {
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }
    }

    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    // ── Conducteur: personal reward dashboard ─────────────────────────────────

    public function index(): void
    {
        $this->requireConducteur();

        $currentUser = $_SESSION['user'];
        $rewardData  = $this->rewardModel->getConducteurRewardData($currentUser);
        $allLevels   = $this->rewardModel->getAllLevels();

        $pageTitle = 'Mes Récompenses';
        require_once ROOT_PATH . '/views/rewards/dashboard.php';
    }

    // ── Admin: rewarded conducteurs list ─────────────────────────────────────

    public function admin(): void
    {
        $this->requireAdmin();

        require_once ROOT_PATH . '/core/PaginationHelper.php';

        $search      = trim((string) ($_GET['search']      ?? ''));
        $levelFilter = trim((string) ($_GET['level_filter'] ?? ''));

        $totalRows   = $this->rewardModel->countEligibleConducteurs($search, $levelFilter);
        $pagination  = new PaginationHelper($totalRows, 10);

        $conducteurs = $this->rewardModel->getEligibleConducteurs(
            $pagination->limit,
            $pagination->offset,
            $search,
            $levelFilter
        );

        $rewardStats = $this->rewardModel->getAdminRewardStats();
        $allLevels   = $this->rewardModel->getAllLevels();

        $pageTitle = 'Conducteurs Récompensés';
        require_once ROOT_PATH . '/views/admin/rewards.php';
    }
}
