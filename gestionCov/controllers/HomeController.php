<?php
/**
 * controllers/HomeController.php
 * Accueil de l'application
 */

class HomeController
{
    public function index(): void
    {
        if (isset($_SESSION['user'])) {
            $role = $_SESSION['user']['role'] ?? '';
            if ($role === 'admin') {
                header('Location: ' . BASE_URL . '/index.php?page=admin');
                exit;
            } elseif ($role === 'conducteur') {
                header('Location: ' . BASE_URL . '/index.php?page=trajet&action=myTrajets');
                exit;
            } else {
                header('Location: ' . BASE_URL . '/index.php?page=trajet');
                exit;
            }
        }

        $trajetModel = new Trajet();
        $userModel = new User();
        $db = Database::getInstance();

        $trajets = $trajetModel->getAvailable(6);
        $totalTrajets = $trajetModel->count();
        $totalUsers = $userModel->count();
        $gouvernorats = (int) $db->query("SELECT COUNT(DISTINCT ville_depart) FROM trajets")->fetchColumn();
        
        $avgRating = (float) $db->query("SELECT AVG(note) FROM avis")->fetchColumn();
        $avgRatingFormatted = $avgRating > 0 ? number_format($avgRating, 1) : "4.8";

        require_once ROOT_PATH . '/views/home.php';
    }
}
