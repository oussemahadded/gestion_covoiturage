<?php
$pageTitle = 'Administration - Tableau de bord';
require_once ROOT_PATH . '/views/layouts/header.php';

$etudiants  = $detailedStats['role_breakdown']['etudiant']   ?? 0;
$profs      = $detailedStats['role_breakdown']['professeur'] ?? 0;
$conducteurs= $detailedStats['role_breakdown']['conducteur'] ?? 0;
?>

<div class="container">
    <h1 class="page-title">
        <?= ui_icon('admin', 'icon icon-lg') ?>
        <span>Vue d'ensemble de la Plateforme</span>
    </h1>

    <div class="stats-admin-grid">

        <!-- Points en circulation -->
        <article class="stat-admin-card stat-points">
            <div class="stat-admin-icon" style="background:rgba(124,58,237,.12); color:#7C3AED;">
                <?= ui_icon('price', 'icon icon-md') ?>
            </div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= number_format($detailedStats['total_points'], 0, ',', ' ') ?></span>
                <span class="stat-admin-label">Points en circulation</span>
                <a href="<?= BASE_URL ?>/index.php?page=admin&action=finances" class="stat-admin-link">Détails financiers →</a>
            </div>
        </article>

        <!-- Nouveaux utilisateurs -->
        <article class="stat-admin-card stat-res">
            <div class="stat-admin-icon" style="background:var(--clr-success-bg); color:var(--clr-success);">
                <?= ui_icon('users', 'icon icon-md') ?>
            </div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= $detailedStats['new_users_30d'] ?></span>
                <span class="stat-admin-label">Nouveaux (30 j.)</span>
                <a href="<?= BASE_URL ?>/index.php?page=admin&action=users" class="stat-admin-link">Gérer →</a>
            </div>
        </article>

        <!-- Total inscrits -->
        <article class="stat-admin-card stat-users">
            <div class="stat-admin-icon" style="background:var(--clr-primary-light); color:var(--clr-primary);">
                <?= ui_icon('student', 'icon icon-md') ?>
            </div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= (int) $stats['users'] ?></span>
                <span class="stat-admin-label">Total Inscrits</span>
                <span class="admin-muted"><?= $etudiants ?> Étu. · <?= $profs ?> Profs · <?= $conducteurs ?> Cond.</span>
            </div>
        </article>

        <!-- Trajets terminés -->
        <article class="stat-admin-card stat-trajets">
            <div class="stat-admin-icon" style="background:var(--clr-warning-bg); color:var(--clr-warning);">
                <?= ui_icon('route', 'icon icon-md') ?>
            </div>
            <div class="stat-admin-info">
                <span class="stat-admin-num">
                    <?= $detailedStats['completed_trajets'] ?>
                    <small style="font-size:.45em; font-weight:500; color:var(--clr-text-muted)">/ <?= $detailedStats['total_trajets'] ?></small>
                </span>
                <span class="stat-admin-label">Trajets Terminés</span>
                <a href="<?= BASE_URL ?>/index.php?page=admin&action=trajets" class="stat-admin-link">Gérer →</a>
            </div>
        </article>

        <!-- Réservations -->
        <article class="stat-admin-card">
            <div class="stat-admin-icon" style="background:rgba(124,58,237,.1); color:#7C3AED;">
                <?= ui_icon('reservation', 'icon icon-md') ?>
            </div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= (int) $stats['reservations'] ?></span>
                <span class="stat-admin-label">Réservations totales</span>
                <a href="<?= BASE_URL ?>/index.php?page=admin&action=traceability" class="stat-admin-link">Traçabilité →</a>
            </div>
        </article>

    </div>

    <h2 class="section-subtitle" style="margin-bottom:1rem;">Accès Rapides</h2>
    <div class="admin-quick-nav">
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=users" class="admin-nav-btn">
            <?= ui_icon('users', 'icon icon-sm') ?>
            <span>Utilisateurs</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=trajets" class="admin-nav-btn">
            <?= ui_icon('route', 'icon icon-sm') ?>
            <span>Trajets</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=traceability" class="admin-nav-btn">
            <?= ui_icon('traceability', 'icon icon-sm') ?>
            <span>Traçabilité</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=finances" class="admin-nav-btn">
            <?= ui_icon('price', 'icon icon-sm') ?>
            <span>Finances / Points</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=settings" class="admin-nav-btn">
            <?= ui_icon('edit', 'icon icon-sm') ?>
            <span>Paramètres</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=reward&action=admin" class="admin-nav-btn">
            <?= ui_icon('trophy', 'icon icon-sm') ?>
            <span>Récompenses</span>
        </a>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
