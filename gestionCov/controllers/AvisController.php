<?php
/**
 * controllers/AvisController.php
 * Avis management for eligible passengers.
 */

class AvisController
{
    private Avis $avisModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->avisModel = new Avis();
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

    private function requirePassenger(): void
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['etudiant', 'professeur'], true)) {
            $this->flash('error', 'Accès non autorisé.');
            $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
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

    public function create(): void
    {
        $this->requirePassenger();

        $trajetId = (int) ($_GET['trajet_id'] ?? 0);
        $passagerId = (int) ($_SESSION['user']['id'] ?? 0);

        if (!$this->avisModel->canReview($trajetId, $passagerId)) {
            $this->flash('error', 'Vous devez avoir une réservation confirmée pour laisser un avis.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ($this->avisModel->existsForPassager($trajetId, $passagerId)) {
            $this->flash('error', 'Vous avez déjà laissé un avis pour ce trajet.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $note = (int) ($_POST['note'] ?? 0);
            $commentaire = trim(htmlspecialchars($_POST['commentaire'] ?? '', ENT_QUOTES, 'UTF-8'));

            if ($note < 1 || $note > 5) {
                $this->flash('error', 'La note doit être entre 1 et 5.');
            } else {
                $ok = $this->avisModel->create($trajetId, $passagerId, $note, $commentaire);
                if ($ok) {
                    $this->audit(
                        'avis_created',
                        'avis',
                        null,
                        [
                            'trajet_id' => $trajetId,
                            'passager_id' => $passagerId,
                            'note' => $note,
                        ]
                    );
                }

                $this->flash('success', 'Votre avis a été publié !');
                $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
            }
        }

        $trajetModel = new Trajet();
        $trajet = $trajetModel->findById($trajetId);
        require_once ROOT_PATH . '/views/avis/create.php';
    }
}

