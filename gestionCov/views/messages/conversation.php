<?php
$pageTitle = 'Conversation avec ' . htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']);
require_once ROOT_PATH . '/views/layouts/header.php';
$myId = $_SESSION['user']['id'];
?>

<div class="container container--narrow">
    <a href="<?= BASE_URL ?>/index.php?page=message" class="back-link">← Conversations</a>

    <div class="chat-header">
        <div class="contact-avatar">
            <?= strtoupper(substr(htmlspecialchars($contact['prenom']), 0, 1)) ?>
        </div>
        <h2><?= htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']) ?></h2>
    </div>

    <!-- Zone messages -->
    <div class="chat-window" id="chatWindow">
        <?php if (empty($messages)): ?>
            <p class="chat-empty">Démarrez la conversation ci-dessous !</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
            <div class="chat-bubble <?= $msg['expediteur_id'] == $myId ? 'bubble-out' : 'bubble-in' ?>">
                <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                <small class="bubble-time">
                    <?= date('d/m H:i', strtotime($msg['created_at'])) ?>
                    <?php if ($msg['expediteur_id'] == $myId): ?>
                        <?= $msg['lu'] ? ' ✓✓' : ' ✓' ?>
                    <?php endif; ?>
                </small>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Formulaire d'envoi -->
    <form action="<?= BASE_URL ?>/index.php?page=message&action=send" method="POST" class="chat-form">
        <input type="hidden" name="destinataire_id" value="<?= $contact['id'] ?>">
        <div class="chat-input-row">
            <textarea name="contenu" id="chatInput" class="chat-input"
                      placeholder="Écrivez votre message..." rows="1" required
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
            <button type="submit" class="btn btn-primary chat-send">📨</button>
        </div>
    </form>
</div>

<script>
    // Scroll automatique vers le bas
    const w = document.getElementById('chatWindow');
    if (w) w.scrollTop = w.scrollHeight;
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
