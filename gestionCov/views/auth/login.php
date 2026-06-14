<?php $pageTitle = 'Connexion'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="auth-page page-shell">
    <div class="auth-card app-card section-card">
        <a href="<?= BASE_URL ?>/index.php" class="back-link">
            <?= ui_icon('arrow-left', 'icon icon-xs') ?>
            Retour à l'accueil
        </a>
        <div class="auth-header">
            <span class="auth-icon-wrap"><?= ui_icon('login', 'icon icon-lg') ?></span>
            <h1>Connexion</h1>
            <p>Bienvenue sur SesameRide. Connectez-vous à votre compte.</p>
        </div>

        <?php
        $errors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);
        ?>
        <?php if (!empty($errors)): ?>
            <ul class="error-list app-alert">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?page=auth&action=login" method="POST" class="auth-form" novalidate>
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" placeholder="vous@exemple.tn" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <div class="input-password">
                    <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="toggle-pwd" data-toggle-target="mot_de_passe" aria-label="Afficher le mot de passe">
                        <?= ui_icon('view', 'icon icon-sm') ?>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <?= ui_icon('login', 'icon icon-sm') ?>
                <span>Se connecter</span>
            </button>
        </form>

        <p class="auth-switch">
            Pas encore de compte ?
            <a href="<?= BASE_URL ?>/index.php?page=auth&action=register">S'inscrire gratuitement</a>
        </p>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
