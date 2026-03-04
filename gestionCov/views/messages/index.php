<?php $pageTitle = 'Messages'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <h1 class="page-title">💬 Mes conversations</h1>

    <?php if (empty($contacts)): ?>
    <div class="empty-state-box">
        <span style="font-size:3rem;">💬</span>
        <p>Aucune conversation pour le moment.</p>
        <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-primary">Trouver un trajet</a>
    </div>
    <?php else: ?>
    <div class="contacts-list">
        <?php foreach ($contacts as $c): ?>
        <a href="<?= BASE_URL ?>/index.php?page=message&action=conversation&contact=<?= $c['contact_id'] ?>"
           class="contact-card <?= $c['non_lus'] > 0 ? 'contact-unread' : '' ?>">
            <div class="contact-avatar">
                <?= strtoupper(substr(htmlspecialchars($c['prenom']), 0, 1)) ?>
            </div>
            <div class="contact-info">
                <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
            </div>
            <?php if ($c['non_lus'] > 0): ?>
            <span class="badge"><?= $c['non_lus'] ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
