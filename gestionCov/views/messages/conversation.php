<?php
$pageTitle = 'Conversation avec ' . htmlspecialchars($contact['prenom'] . ' ' . $contact['nom'], ENT_QUOTES, 'UTF-8');
require_once ROOT_PATH . '/views/layouts/header.php';
$myId = (int) $_SESSION['user']['id'];
?>

<div class="container container--narrow">
    <a href="<?= BASE_URL ?>/index.php?page=message" class="back-link">
        <?= ui_icon('arrow-left', 'icon icon-sm') ?>
        <span>Conversations</span>
    </a>

    <div class="chat-header">
        <div class="contact-avatar">
            <?= strtoupper(substr(htmlspecialchars($contact['prenom'], ENT_QUOTES, 'UTF-8'), 0, 1)) ?>
        </div>
        <h2><?= htmlspecialchars($contact['prenom'] . ' ' . $contact['nom'], ENT_QUOTES, 'UTF-8') ?></h2>
    </div>

    <div class="chat-window" id="chatWindow">
        <?php if (empty($messages)): ?>
            <p class="chat-empty">Démarrez la conversation ci-dessous.</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="chat-bubble <?= (int) $msg['expediteur_id'] === $myId ? 'bubble-out' : 'bubble-in' ?>">
                    <p><?= nl2br(htmlspecialchars($msg['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <small class="bubble-time">
                        <?= date('d/m H:i', strtotime($msg['created_at'])) ?>
                        <?php if ((int) $msg['expediteur_id'] === $myId): ?>
                            <span class="message-read-icon">
                                <?= (int) $msg['lu'] === 1 ? ui_icon('success', 'icon icon-xxs') : ui_icon('check', 'icon icon-xxs') ?>
                            </span>
                        <?php endif; ?>
                    </small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form action="<?= BASE_URL ?>/index.php?page=message&action=send" method="POST" class="chat-form">
        <input type="hidden" name="destinataire_id" value="<?= (int) $contact['id'] ?>">
        <div class="chat-input-row">
            <textarea name="contenu"
                      id="chatInput"
                      class="chat-input"
                      placeholder="Écrivez votre message..."
                      rows="1"
                      required
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
            <button type="submit" class="btn btn-primary chat-send" aria-label="Envoyer">
                <?= ui_icon('send', 'icon icon-sm') ?>
            </button>
        </div>
    </form>
</div>

<script>
const chatWindow = document.getElementById('chatWindow');
if (chatWindow) {
    chatWindow.scrollTop = chatWindow.scrollHeight;
}
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
