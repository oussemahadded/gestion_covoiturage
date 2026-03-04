<?php $pageTitle = 'Page introuvable'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>
<div class="container text-center" style="padding: 5rem 1rem;">
    <h1 style="font-size:6rem;margin:0;line-height:1;">404</h1>
    <h2>Page introuvable</h2>
    <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">← Retour à l'accueil</a>
</div>
<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
