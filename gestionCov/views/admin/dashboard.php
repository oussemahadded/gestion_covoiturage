<?php $pageTitle = 'Admin — Tableau de bord'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <h1 class="page-title">⚙️ Tableau de bord administrateur</h1>

    <!-- Statistiques -->
    <div class="stats-admin-grid">
        <div class="stat-admin-card stat-users">
            <div class="stat-admin-icon">👥</div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= $stats['users'] ?></span>
                <span class="stat-admin-label">Utilisateurs</span>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=admin&action=users" class="stat-admin-link">Gérer →</a>
        </div>
        <div class="stat-admin-card stat-trajets">
            <div class="stat-admin-icon">🚗</div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= $stats['trajets'] ?></span>
                <span class="stat-admin-label">Trajets</span>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=admin&action=trajets" class="stat-admin-link">Gérer →</a>
        </div>
        <div class="stat-admin-card stat-res">
            <div class="stat-admin-icon">🎫</div>
            <div class="stat-admin-info">
                <span class="stat-admin-num"><?= $stats['reservations'] ?></span>
                <span class="stat-admin-label">Réservations</span>
            </div>
        </div>
    </div>

    <!-- Navigation admin rapide -->
    <div class="admin-quick-nav">
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=users"   class="admin-nav-btn">👥 Gestion des utilisateurs</a>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=trajets" class="admin-nav-btn">🚗 Gestion des trajets</a>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
