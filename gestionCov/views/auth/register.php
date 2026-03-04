<?php
$pageTitle = 'Inscription';
$errors    = $_SESSION['form_errors'] ?? [];
$old       = $_SESSION['form_data']   ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
require_once ROOT_PATH . '/views/layouts/header.php';
?>

<div class="auth-page">
    <div class="auth-card auth-card--wide">
        <div class="auth-header">
            <span class="auth-icon">🚀</span>
            <h1>Créer un compte</h1>
            <p>Rejoignez la communauté <strong>CHAYA3NI</strong> en Tunisie</p>
        </div>

        <?php if (!empty($errors)): ?>
        <ul class="error-list">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?page=auth&action=register" method="POST"
              class="auth-form" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom"
                           value="<?= htmlspecialchars($old['nom'] ?? '') ?>"
                           placeholder="Ben Salah" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom"
                           value="<?= htmlspecialchars($old['prenom'] ?? '') ?>"
                           placeholder="Ahmed" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       placeholder="ahmed@exemple.tn" required>
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone <small class="text-muted">(optionnel)</small></label>
                <input type="tel" id="telephone" name="telephone"
                       value="<?= htmlspecialchars($old['telephone'] ?? '') ?>"
                       placeholder="98765432"
                       pattern="^[0-9]{8}$"
                       maxlength="8"
                       inputmode="numeric"
                       title="Entrez exactement 8 chiffres (ex: 98765432)">
                <small class="field-hint">8 chiffres sans espace ni indicatif</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <div class="input-password">
                        <input type="password" id="mot_de_passe" name="mot_de_passe"
                               placeholder="8 caractères min." required minlength="8">
                        <button type="button" class="toggle-pwd" onclick="togglePwd('mot_de_passe')">👁</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="mdp_confirm">Confirmation</label>
                    <div class="input-password">
                        <input type="password" id="mdp_confirm" name="mdp_confirm"
                               placeholder="Répétez le mot de passe" required>
                        <button type="button" class="toggle-pwd" onclick="togglePwd('mdp_confirm')">👁</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Vous êtes :</label>
                <div class="role-selector">
                    <label class="role-option <?= ($old['role'] ?? 'passager') === 'passager' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="passager"
                               <?= ($old['role'] ?? 'passager') === 'passager' ? 'checked' : '' ?>>
                        <span class="role-icon">🎫</span>
                        <span>Passager</span>
                        <small>Je cherche un trajet</small>
                    </label>
                    <label class="role-option <?= ($old['role'] ?? '') === 'conducteur' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="conducteur"
                               <?= ($old['role'] ?? '') === 'conducteur' ? 'checked' : '' ?>>
                        <span class="role-icon">🚗</span>
                        <span>Conducteur</span>
                        <small>Je propose des trajets</small>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Créer mon compte</button>
        </form>

        <p class="auth-switch">
            Déjà un compte ?
            <a href="<?= BASE_URL ?>/index.php?page=auth&action=login">Se connecter</a>
        </p>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
