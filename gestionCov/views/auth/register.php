<?php
$pageTitle = 'Inscription';
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
require_once ROOT_PATH . '/views/layouts/header.php';

$selectedRole = $old['role'] ?? 'etudiant';
?>

<div class="auth-page page-shell">
    <div class="auth-card auth-card--wide app-card section-card">
        <div class="auth-header">
            <span class="auth-icon-wrap"><?= ui_icon('register', 'icon icon-lg') ?></span>
            <h1>Créer un compte</h1>
            <p>Rejoignez la communauté CHAYA3NI</p>
        </div>

        <?php if (!empty($errors)): ?>
            <ul class="error-list app-alert">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?page=auth&action=register" method="POST" class="auth-form" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($old['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ben Salah" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ahmed" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="vous@exemple.tn" required>
                <small class="field-hint">
                    Étudiants et professeurs : utilisez votre adresse @sesame.com.tn. Conducteurs : utilisez une adresse email valide.
                </small>
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone <small class="text-muted">(optionnel)</small></label>
                <input type="tel"
                       id="telephone"
                       name="telephone"
                       value="<?= htmlspecialchars($old['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="98765432"
                       pattern="^[0-9]{8}$"
                       maxlength="8"
                       inputmode="numeric"
                       title="Entrez exactement 8 chiffres (ex: 98765432)">
                <small class="field-hint">8 caractères numériques sans espace.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <div class="input-password">
                        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="8 caractères minimum" required minlength="8">
                        <button type="button" class="toggle-pwd" data-toggle-target="mot_de_passe" aria-label="Afficher le mot de passe">
                            <?= ui_icon('view', 'icon icon-sm') ?>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="mdp_confirm">Répétez le mot de passe</label>
                    <div class="input-password">
                        <input type="password" id="mdp_confirm" name="mdp_confirm" placeholder="Répétez le mot de passe" required>
                        <button type="button" class="toggle-pwd" data-toggle-target="mdp_confirm" aria-label="Afficher le mot de passe">
                            <?= ui_icon('view', 'icon icon-sm') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Vous êtes :</label>
                <div class="role-selector detail-grid">
                    <label class="role-option <?= $selectedRole === 'etudiant' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="etudiant" <?= $selectedRole === 'etudiant' ? 'checked' : '' ?>>
                        <span class="role-icon"><?= ui_icon('student', 'icon icon-md') ?></span>
                        <span>Étudiant</span>
                        <small>Je cherche et réserve des trajets</small>
                    </label>
                    <label class="role-option <?= $selectedRole === 'professeur' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="professeur" <?= $selectedRole === 'professeur' ? 'checked' : '' ?>>
                        <span class="role-icon"><?= ui_icon('teacher', 'icon icon-md') ?></span>
                        <span>Professeur</span>
                        <small>Je cherche et réserve des trajets</small>
                    </label>
                    <label class="role-option <?= $selectedRole === 'conducteur' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="conducteur" <?= $selectedRole === 'conducteur' ? 'checked' : '' ?>>
                        <span class="role-icon"><?= ui_icon('car', 'icon icon-md') ?></span>
                        <span>Conducteur</span>
                        <small>Je propose des trajets</small>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <?= ui_icon('register', 'icon icon-sm') ?>
                <span>Créer mon compte</span>
            </button>
        </form>

        <p class="auth-switch">
            Déjà inscrit ?
            <a href="<?= BASE_URL ?>/index.php?page=auth&action=login">Se connecter</a>
        </p>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
