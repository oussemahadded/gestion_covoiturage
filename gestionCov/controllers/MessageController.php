<?php
/**
 * controllers/MessageController.php
 * Chat entre utilisateurs (Bonus)
 */

class MessageController
{
    private Message $msgModel;

    public function __construct()
    {
        $this->msgModel = new Message();
        $this->requireAuth();
    }

    private function redirect(string $url): void { header("Location: $url"); exit; }
    private function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
            exit;
        }
    }

    /** Liste des conversations (contacts) */
    public function index(): void
    {
        $userId   = $_SESSION['user']['id'];
        $contacts = $this->msgModel->getContacts($userId);
        require_once ROOT_PATH . '/views/messages/index.php';
    }

    /** Conversation avec un contact spécifique */
    public function conversation(): void
    {
        $userId      = $_SESSION['user']['id'];
        $contactId   = (int) ($_GET['contact'] ?? 0);

        if ($contactId <= 0 || $contactId === $userId) {
            $this->redirect(BASE_URL . '/index.php?page=message');
        }

        // Marquer les messages reçus comme lus
        $this->msgModel->markAsRead($contactId, $userId);

        $messages = $this->msgModel->getConversation($userId, $contactId);

        $userModel = new User();
        $contact   = $userModel->findById($contactId);

        if (!$contact) {
            $this->flash('error', 'Interlocuteur introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=message');
        }

        require_once ROOT_PATH . '/views/messages/conversation.php';
    }

    /** Envoyer un message */
    public function send(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=message');
        }

        $expediteurId  = $_SESSION['user']['id'];
        $destinataireId = (int) ($_POST['destinataire_id'] ?? 0);
        $contenu        = trim(htmlspecialchars($_POST['contenu'] ?? '', ENT_QUOTES, 'UTF-8'));
        $trajetId       = !empty($_POST['trajet_id']) ? (int) $_POST['trajet_id'] : null;

        if (empty($contenu) || $destinataireId <= 0 || $destinataireId === $expediteurId) {
            $this->flash('error', 'Message invalide.');
            $this->redirect(BASE_URL . '/index.php?page=message&action=conversation&contact=' . $destinataireId);
        }

        $this->msgModel->send($expediteurId, $destinataireId, $contenu, $trajetId);
        $this->redirect(BASE_URL . '/index.php?page=message&action=conversation&contact=' . $destinataireId);
    }
}
