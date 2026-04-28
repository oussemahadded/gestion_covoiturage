<?php
$pageTitle = 'Modifier le trajet';
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
require_once ROOT_PATH . '/views/layouts/header.php';

$villeDepart = $old['ville_depart'] ?? $trajet['ville_depart'];
$villeArrivee = $old['ville_arrivee'] ?? $trajet['ville_arrivee'];
$dateIso = $old['date_depart'] ?? $trajet['date_depart'];
$heureDepart = $old['heure_depart'] ?? substr($trajet['heure_depart'], 0, 5);
$prix = $old['prix'] ?? $trajet['prix'];
$placesTotal = $old['places_total'] ?? $trajet['places_total'];
$description = $old['description'] ?? ($trajet['description'] ?? '');

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
if (!empty($dateIso)) {
    $dt = DateTime::createFromFormat('Y-m-d', $dateIso);
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
        <?= ui_icon('edit', 'icon icon-md') ?>
        <span>Modifier le trajet</span>
    </h1>

    <?php if (!empty($errors)): ?>
        <ul class="error-list">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="form-card premium-form-card">
        <form action="<?= BASE_URL ?>/index.php?page=trajet&action=edit&id=<?= (int) $trajet['id'] ?>" method="POST" class="date-fr-form">
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
                           <?= $direction === 'depuis_sesame' ? 'readonly' : '' ?>
                           required>
                </div>
                <div class="form-group">
                    <label for="ville_arrivee"><?= ui_icon('arrival', 'icon icon-xs') ?> Ville d'arrivée *</label>
                    <input type="text"
                           id="ville_arrivee"
                           name="ville_arrivee"
                           value="<?= htmlspecialchars($villeArrivee, ENT_QUOTES, 'UTF-8') ?>"
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
                    <input type="hidden" id="date_depart" name="date_depart" value="<?= htmlspecialchars($dateIso, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="heure_depart"><?= ui_icon('clock', 'icon icon-xs') ?> Heure *</label>
                    <input type="time" id="heure_depart" name="heure_depart" value="<?= htmlspecialchars($heureDepart, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prix"><?= ui_icon('price', 'icon icon-xs') ?> Prix (TND) *</label>
                    <input type="number" id="prix" name="prix" step="0.01" min="0" value="<?= htmlspecialchars((string) $prix, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group">
                    <label for="places_total"><?= ui_icon('seats', 'icon icon-xs') ?> Places totales *</label>
                    <input type="number" id="places_total" name="places_total" min="1" max="8" value="<?= htmlspecialchars((string) $placesTotal, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description"><?= ui_icon('messages', 'icon icon-xs') ?> Description</label>
                <textarea id="description" name="description" rows="3"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="btn btn-outline">
                    <?= ui_icon('x', 'icon icon-sm') ?>
                    <span>Annuler</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= ui_icon('check', 'icon icon-sm') ?>
                    <span>Enregistrer</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
