<?php
/**
 * controllers/AvisController.php
 * Gestion des avis (Bonus)
 */

class AvisController
{
    private Avis $avisModel;

    public function __construct()
    {
        $this->avisModel = new Avis();
    }

    private function redirect(string $url): void { header("Location: $url"); exit; }
    private function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

    private function requireRole(string ...$roles): void
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $roles, true)) {
            $this->flash('error', 'Accès non autorisé.');
            $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
        }
    }

    public function create(): void
    {
        $this->requireRole('passager');

        $trajetId   = (int) ($_GET['trajet_id'] ?? 0);
        $passagerId = $_SESSION['user']['id'];

        // Vérifier l'éligibilité : réservation confirmée obligatoire
        if (!$this->avisModel->canReview($trajetId, $passagerId)) {
            $this->flash('error', 'Vous devez avoir une réservation confirmée pour laisser un avis.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ($this->avisModel->existsForPassager($trajetId, $passagerId)) {
            $this->flash('error', 'Vous avez déjà laissé un avis pour ce trajet.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $note        = (int) ($_POST['note']        ?? 0);
            $commentaire = trim(htmlspecialchars($_POST['commentaire'] ?? '', ENT_QUOTES, 'UTF-8'));

            if ($note < 1 || $note > 5) {
                $this->flash('error', 'La note doit être entre 1 et 5.');
            } else {
                $this->avisModel->create($trajetId, $passagerId, $note, $commentaire);
                $this->flash('success', 'Votre avis a été publié !');
                $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
            }
        }

        $trajetModel = new Trajet();
        $trajet = $trajetModel->findById($trajetId);
        require_once ROOT_PATH . '/views/avis/create.php';
    }
}
