<?php
$pageTitle = 'Proposer un trajet';
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
require_once ROOT_PATH . '/views/layouts/header.php';

$villeDepart = $old['ville_depart'] ?? '';
$villeArrivee = $old['ville_arrivee'] ?? '';
$direction = 'vers_sesame';

if (strcasecmp($villeDepart, 'Sesame') === 0 && strcasecmp($villeArrivee, 'Sesame') !== 0) {
    $direction = 'depuis_sesame';
}

if ($direction === 'vers_sesame' && $villeArrivee === '') {
    $villeArrivee = 'Sesame';
}
if ($direction === 'depuis_sesame' && $villeDepart === '') {
    $villeDepart = 'Sesame';
}

$dateDisplay = '';
if (!empty($old['date_depart'])) {
    $dt = DateTime::createFromFormat('Y-m-d', $old['date_depart']);
    if ($dt) {
        $dateDisplay = $dt->format('d/m/Y');
    }
}
?>

<div class="container container--narrow">
    <a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="back-link">
        <?= ui_icon('arrow-left', 'icon icon-sm') ?>
        <span>Mes trajets</span>
    </a>
    <h1 class="page-title">
        <?= ui_icon('car', 'icon icon-md') ?>
        <span>Proposer un trajet</span>
    </h1>

    <?php if (!empty($errors)): ?>
        <ul class="error-list">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="form-card premium-form-card">
        <form action="<?= BASE_URL ?>/index.php?page=trajet&action=create" method="POST" class="date-fr-form">
            <div class="form-group">
                <label>Direction du trajet</label>
                <div class="trip-direction-selector" data-default-direction="<?= htmlspecialchars($direction, ENT_QUOTES, 'UTF-8') ?>">
                    <label class="direction-option <?= $direction === 'vers_sesame' ? 'selected' : '' ?>">
                        <input type="radio" name="direction" value="vers_sesame" <?= $direction === 'vers_sesame' ? 'checked' : '' ?>>
                        <span class="direction-icon"><?= ui_icon('arrival', 'icon icon-md') ?></span>
                        <span class="direction-title">Vers Sesame</span>
                    </label>
                    <label class="direction-option <?= $direction === 'depuis_sesame' ? 'selected' : '' ?>">
                        <input type="radio" name="direction" value="depuis_sesame" <?= $direction === 'depuis_sesame' ? 'checked' : '' ?>>
                        <span class="direction-icon"><?= ui_icon('departure', 'icon icon-md') ?></span>
                        <span class="direction-title">Depuis Sesame</span>
                    </label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="ville_depart"><?= ui_icon('departure', 'icon icon-xs') ?> Ville de départ *</label>
                    <input type="text"
                           id="ville_depart"
                           name="ville_depart"
                           value="<?= htmlspecialchars($villeDepart, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: Tunis"
                           <?= $direction === 'depuis_sesame' ? 'readonly' : '' ?>
                           required>
                </div>
                <div class="form-group">
                    <label for="ville_arrivee"><?= ui_icon('arrival', 'icon icon-xs') ?> Ville d'arrivée *</label>
                    <input type="text"
                           id="ville_arrivee"
                           name="ville_arrivee"
                           value="<?= htmlspecialchars($villeArrivee, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: Nabeul"
                           <?= $direction === 'vers_sesame' ? 'readonly' : '' ?>
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group date-fr-group">
                    <label for="date_depart_display"><?= ui_icon('calendar', 'icon icon-xs') ?> Date de départ *</label>
                    <input type="text"
                           id="date_depart_display"
                           name="date_depart_display"
                           class="date-fr-input"
                           placeholder="jj/mm/aaaa"
                           inputmode="numeric"
                           maxlength="10"
                           autocomplete="off"
                           value="<?= htmlspecialchars($dateDisplay, ENT_QUOTES, 'UTF-8') ?>"
                           required>
                    <input type="hidden" id="date_depart" name="date_depart" value="<?= htmlspecialchars($old['date_depart'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="heure_depart"><?= ui_icon('clock', 'icon icon-xs') ?> Heure de départ *</label>
                    <input type="time" id="heure_depart" name="heure_depart" value="<?= htmlspecialchars($old['heure_depart'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prix"><?= ui_icon('price', 'icon icon-xs') ?> Prix par personne (TND) *</label>
                    <input type="number"
                           id="prix"
                           name="prix"
                           step="0.01"
                           min="0"
                           value="<?= htmlspecialchars((string) ($old['prix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="15.000"
                           required>
                </div>
                <div class="form-group">
                    <label for="places_total"><?= ui_icon('seats', 'icon icon-xs') ?> Nombre de places *</label>
                    <input type="number"
                           id="places_total"
                           name="places_total"
                           min="1"
                           max="8"
                           value="<?= htmlspecialchars((string) ($old['places_total'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="3"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="description"><?= ui_icon('messages', 'icon icon-xs') ?> Description (optionnelle)</label>
                <textarea id="description" name="description" rows="3" placeholder="Informations complémentaires : bagages autorisés, point de rendez-vous..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="btn btn-outline">
                    <?= ui_icon('x', 'icon icon-sm') ?>
                    <span>Annuler</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= ui_icon('check', 'icon icon-sm') ?>
                    <span>Publier le trajet</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
