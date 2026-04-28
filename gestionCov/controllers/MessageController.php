<?php
/**
 * controllers/MessageController.php
 * Messaging between authenticated users.
 */

class MessageController
{
    private Message $msgModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->msgModel = new Message();
        $this->auditLog = new AuditLog();
        $this->requireAuth();
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
            header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
            exit;
        }

        if (!in_array($_SESSION['user']['role'], ['admin', 'conducteur', 'etudiant', 'professeur'], true)) {
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

    public function index(): void
    {
        $userId = (int) $_SESSION['user']['id'];
        $contacts = $this->msgModel->getContacts($userId);
        require_once ROOT_PATH . '/views/messages/index.php';
    }

    public function conversation(): void
    {
        $userId = (int) $_SESSION['user']['id'];
        $contactId = (int) ($_GET['contact'] ?? 0);

        if ($contactId <= 0 || $contactId === $userId) {
            $this->redirect(BASE_URL . '/index.php?page=message');
        }

        $this->msgModel->markAsRead($contactId, $userId);
        $messages = $this->msgModel->getConversation($userId, $contactId);

        $userModel = new User();
        $contact = $userModel->findById($contactId);

        if (!$contact) {
            $this->flash('error', 'Interlocuteur introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=message');
        }

        require_once ROOT_PATH . '/views/messages/conversation.php';
    }

    public function send(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=message');
        }

        $expediteurId = (int) $_SESSION['user']['id'];
        $destinataireId = (int) ($_POST['destinataire_id'] ?? 0);
        $contenu = trim(htmlspecialchars($_POST['contenu'] ?? '', ENT_QUOTES, 'UTF-8'));
        $trajetId = !empty($_POST['trajet_id']) ? (int) $_POST['trajet_id'] : null;

        if ($contenu === '' || $destinataireId <= 0 || $destinataireId === $expediteurId) {
            $this->flash('error', 'Message invalide.');
            $this->redirect(BASE_URL . '/index.php?page=message&action=conversation&contact=' . $destinataireId);
        }

        $messageId = $this->msgModel->send($expediteurId, $destinataireId, $contenu, $trajetId);
        if ($messageId !== false) {
            $this->audit(
                'message_sent',
                'message',
                (int) $messageId,
                [
                    'message_id' => (int) $messageId,
                    'expediteur_id' => $expediteurId,
                    'destinataire_id' => $destinataireId,
                    'trajet_id' => $trajetId,
                ]
            );
        }

        $this->redirect(BASE_URL . '/index.php?page=message&action=conversation&contact=' . $destinataireId);
    }
}

