<?php
$pageTitle = 'Proposer un trajet';
$includeMap = true;
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

$pointLat = $old['point_lat'] ?? '';
$pointLng = $old['point_lng'] ?? '';
$distanceKm = $old['distance_km'] ?? '';
$dureeMinutes = $old['duree_minutes'] ?? '';
$routeGeometry = $old['route_geometry'] ?? '';
$routeProvider = $old['route_provider'] ?? (defined('ROUTING_PROVIDER') ? ROUTING_PROVIDER : 'osrm');
$prixParKm = $old['prix_par_km'] ?? ($currentPrixParKm ?? 1.0);

$dateDisplay = '';
if (!empty($old['date_depart'])) {
    $dt = DateTime::createFromFormat('Y-m-d', $old['date_depart']);
    if ($dt) {
        $dateDisplay = $dt->format('d/m/Y');
    }
}
?>

<div class="container container--narrow publish-ride-page">
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

    <div class="form-card premium-form-card app-card section-card">
        <form action="<?= BASE_URL ?>/index.php?page=trajet&action=create" method="POST" class="date-fr-form publish-ride-form">
            <div class="publish-ride-steps" aria-hidden="true">
                <span class="route-pill">1. Direction</span>
                <span class="route-pill">2. Circuit</span>
                <span class="route-pill">3. Prix et places</span>
                <span class="route-pill">4. Date et heure</span>
                <span class="route-pill">5. Confirmation</span>
            </div>
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

            <div class="map-picker-card map-card section-card">
                <div class="map-picker-header">
                    <div>
                        <label class="map-picker-title">Point du trajet sur la carte</label>
                        <p class="map-hint">Cliquez pour positionner le point non Sesame, puis ajustez le marqueur.</p>
                    </div>
                    <div class="map-picker-subtitle">Circuit proposé</div>
                </div>
                <div id="tripMap"
                     class="trip-map"
                     data-sesame-lat="<?= htmlspecialchars((string) (defined('SESAME_LAT') ? SESAME_LAT : 0), ENT_QUOTES, 'UTF-8') ?>"
                     data-sesame-lng="<?= htmlspecialchars((string) (defined('SESAME_LNG') ? SESAME_LNG : 0), ENT_QUOTES, 'UTF-8') ?>"
                     data-osrm-url="<?= htmlspecialchars((string) (defined('OSRM_ROUTE_URL') ? OSRM_ROUTE_URL : ''), ENT_QUOTES, 'UTF-8') ?>"
                     data-default-prix-par-km="<?= htmlspecialchars(number_format((float) $prixParKm, 3, '.', ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="route-summary">
                    <div class="distance-summary">
                        <span class="meta-icon"><?= ui_icon('distance', 'icon icon-sm') ?></span>
                        <div>
                            <small>Distance du trajet</small>
                            <strong><span id="distanceValue">-</span> km</strong>
                        </div>
                    </div>
                    <div class="duration-summary">
                        <span class="meta-icon"><?= ui_icon('clock', 'icon icon-sm') ?></span>
                        <div>
                            <small>Durée estimée</small>
                            <strong><span id="durationValue">-</span> min</strong>
                        </div>
                    </div>
                </div>

                <div class="route-warning is-hidden" id="routeWarning"></div>

                <input type="hidden" id="point_lat" name="point_lat" value="<?= htmlspecialchars((string) $pointLat, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" id="point_lng" name="point_lng" value="<?= htmlspecialchars((string) $pointLng, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" id="distance_km" name="distance_km" value="<?= htmlspecialchars((string) $distanceKm, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" id="duree_minutes" name="duree_minutes" value="<?= htmlspecialchars((string) $dureeMinutes, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" id="route_geometry" name="route_geometry" value="<?= htmlspecialchars((string) $routeGeometry, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" id="route_provider" name="route_provider" value="<?= htmlspecialchars((string) $routeProvider, ENT_QUOTES, 'UTF-8') ?>">

                <div class="price-estimate-box section-card">
                    <div class="form-row">
                        <div class="form-group">
                            <label><?= ui_icon('price', 'icon icon-xs') ?> Tarif appliqué aux nouveaux trajets</label>
                            <div class="admin-rate-display">
                                <span class="admin-rate-value"><?= htmlspecialchars(number_format((float) $prixParKm, 3, '.', ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="admin-rate-unit">TND / km</span>
                                <?= ui_icon('lock', 'icon icon-xs') ?>
                            </div>
                            <input type="hidden" id="prix_par_km" name="prix_par_km" value="<?= htmlspecialchars(number_format((float) $prixParKm, 3, '.', ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label>Prix calculé automatiquement</label>
                            <div class="suggested-price-row">
                                <span class="suggested-price-value" id="suggestedPriceValue">-</span>
                                <span class="suggested-price-unit">TND</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prix">Prix final par passager (TND)</label>
                            <input type="number"
                                   id="prix"
                                   name="prix"
                                   step="0.01"
                                   min="0"
                                   value="<?= htmlspecialchars((string) ($old['prix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="0.00"
                                   readonly
                                   tabindex="-1"
                                   class="input-computed">
                            <p class="form-hint"><?= ui_icon('info', 'icon icon-xs') ?> Prix calculé automatiquement selon la distance du trajet.</p>
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
