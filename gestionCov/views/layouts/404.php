<?php $pageTitle = 'Page introuvable'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>
<div class="container">
    <section class="error-404-card">
        <span class="error-404-icon"><?= ui_icon('warning', 'icon icon-xl') ?></span>
        <h1>404</h1>
        <h2>Page introuvable</h2>
        <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour à l'accueil</span>
        </a>
    </section>
</div>
<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
