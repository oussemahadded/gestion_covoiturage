<?php $pageTitle = 'Messages'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <h1 class="page-title">
        <?= ui_icon('messages', 'icon icon-md') ?>
        <span>Mes conversations</span>
    </h1>

    <?php if (empty($contacts)): ?>
        <div class="empty-state-box">
            <span class="empty-illustration"><?= ui_icon('messages', 'icon icon-xl') ?></span>
            <p>Aucune conversation pour le moment.</p>
            <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-primary">
                <?= ui_icon('search', 'icon icon-sm') ?>
                <span>Trouver un trajet</span>
            </a>
        </div>
    <?php else: ?>
        <div class="contacts-list">
            <?php foreach ($contacts as $c): ?>
                <a href="<?= BASE_URL ?>/index.php?page=message&action=conversation&contact=<?= (int) $c['contact_id'] ?>"
                   class="contact-card <?= (int) $c['non_lus'] > 0 ? 'contact-unread' : '' ?>">
                    <div class="contact-avatar">
                        <?= strtoupper(substr(htmlspecialchars($c['prenom'], ENT_QUOTES, 'UTF-8'), 0, 1)) ?>
                    </div>
                    <div class="contact-info">
                        <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <?php if ((int) $c['non_lus'] > 0): ?>
                        <span class="badge"><?= (int) $c['non_lus'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
