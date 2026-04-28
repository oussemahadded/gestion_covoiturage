<?php
$pageTitle = 'Administration - Tableau de bord';
require_once ROOT_PATH . '/views/layouts/header.php';
?>

<div class="container">
    <h1 class="page-title">
        <?= ui_icon('admin', 'icon icon-md') ?>
        <span>Tableau de bord Administration</span>
    </h1>

    <div class="stats-admin-grid">
        <article class="stat-admin-card stat-users">
            <div class="stat-admin-icon"><?= ui_icon('users', 'icon icon-lg') ?></div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= (int) $stats['users'] ?></span>
                <span class="stat-admin-label">Utilisateurs</span>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=admin&action=users" class="stat-admin-link">Gérer</a>
        </article>

        <article class="stat-admin-card stat-trajets">
            <div class="stat-admin-icon"><?= ui_icon('route', 'icon icon-lg') ?></div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= (int) $stats['trajets'] ?></span>
                <span class="stat-admin-label">Trajets</span>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=admin&action=trajets" class="stat-admin-link">Gérer</a>
        </article>

        <article class="stat-admin-card stat-res">
            <div class="stat-admin-icon"><?= ui_icon('reservation', 'icon icon-lg') ?></div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= (int) $stats['reservations'] ?></span>
                <span class="stat-admin-label">Réservations</span>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=admin&action=traceability" class="stat-admin-link">Traçabilité</a>
        </article>
    </div>

    <div class="admin-quick-nav">
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=users" class="admin-nav-btn">
            <?= ui_icon('users', 'icon icon-sm') ?>
            <span>Gestion des utilisateurs</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=trajets" class="admin-nav-btn">
            <?= ui_icon('route', 'icon icon-sm') ?>
            <span>Gestion des trajets</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=traceability" class="admin-nav-btn">
            <?= ui_icon('traceability', 'icon icon-sm') ?>
            <span>Traçabilité globale</span>
        </a>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>

