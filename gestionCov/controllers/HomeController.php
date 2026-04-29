<?php
/**
 * controllers/HomeController.php
 * Accueil de l'application
 */

class HomeController
{
    public function index(): void
    {
        $trajetModel = new Trajet();
        // Afficher les 6 derniers trajets disponibles sur la page d'accueil
        $trajets = $trajetModel->getAvailable(6);
        require_once ROOT_PATH . '/views/home.php';
    }
}
