<?php
$pageTitle = 'Recherche de trajets';
$searchPerformed = $searchPerformed ?? false;
require_once ROOT_PATH . '/views/layouts/header.php';

$dateDisplayValue = trim((string) ($_GET['date_display'] ?? ''));
if ($dateDisplayValue === '' && !empty($_GET['date'])) {
    $dt = DateTime::createFromFormat('Y-m-d', (string) $_GET['date']);
    if ($dt) {
        $dateDisplayValue = $dt->format('d/m/Y');
    }
}
?>

<div class="container search-page">
    <h1 class="page-title">
        <?= ui_icon('search', 'icon icon-md') ?>
        <span>Rechercher un trajet</span>
    </h1>

    <form action="<?= BASE_URL ?>/index.php" method="GET" class="search-form-card filter-card date-fr-form">
        <input type="hidden" name="page" value="trajet">
        <input type="hidden" name="action" value="index">
        <div class="search-grid">
            <div class="form-group">
                <label for="depart"><?= ui_icon('departure', 'icon icon-xs') ?> Ville de départ</label>
                <input type="text"
                       id="depart"
                       name="depart"
                       value="<?= htmlspecialchars($_GET['depart'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Ex: Tunis">
            </div>
            <div class="form-group">
                <label for="arrivee"><?= ui_icon('arrival', 'icon icon-xs') ?> Ville d'arrivée</label>
                <input type="text"
                       id="arrivee"
                       name="arrivee"
                       value="<?= htmlspecialchars($_GET['arrivee'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Ex: Sesame">
            </div>
            <div class="form-group date-fr-group">
                <label for="date_display"><?= ui_icon('calendar', 'icon icon-xs') ?> Date</label>
                <input type="text"
                       id="date_display"
                       name="date_display"
                       class="date-fr-input"
                       value="<?= htmlspecialchars($dateDisplayValue, ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="jj/mm/aaaa"
                       inputmode="numeric"
                       maxlength="10"
                       autocomplete="off">
                <input type="hidden" id="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-search">
                <?= ui_icon('search', 'icon icon-sm') ?>
                <span>Rechercher</span>
            </button>
        </div>
    </form>

    <?php if ($searchPerformed): ?>
        <p class="results-count meta-muted"><?= count($trajets) ?> trajet(s) trouvé(s)</p>
    <?php endif; ?>

    <?php if (empty($trajets) && $searchPerformed): ?>
        <div class="empty-state-box empty-state-polished">
            <span class="empty-illustration"><?= ui_icon('warning', 'icon icon-xl') ?></span>
            <p>Aucun trajet ne correspond à votre recherche.</p>
            <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-outline">
                <?= ui_icon('route', 'icon icon-sm') ?>
                <span>Voir tous les trajets</span>
            </a>
        </div>
    <?php elseif (!empty($trajets)): ?>
        <div class="trajets-list">
            <?php foreach ($trajets as $t): ?>
                <article class="trajet-list-card route-card data-card">
                    <div class="tlc-route route-timeline">
                        <div class="route-line">
                            <span class="dot dot-from"></span>
                            <span class="route-dash"></span>
                            <span class="dot dot-to"></span>
                        </div>
                        <div class="route-cities">
                            <span><?= htmlspecialchars($t['ville_depart'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span><?= htmlspecialchars($t['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                    <div class="tlc-info meta-muted">
                        <span><?= ui_icon('calendar', 'icon icon-xs') ?> <?= date('d/m/Y', strtotime($t['date_depart'])) ?></span>
                        <span><?= ui_icon('clock', 'icon icon-xs') ?> <?= substr($t['heure_depart'], 0, 5) ?></span>
                        <?php if (isset($t['distance_km']) && $t['distance_km'] !== null): ?>
                            <span><?= ui_icon('distance', 'icon icon-xs') ?> <?= number_format((float) $t['distance_km'], 2) ?> km</span>
                        <?php endif; ?>
                        <?php if (isset($t['duree_minutes']) && $t['duree_minutes'] !== null): ?>
                            <span><?= ui_icon('clock', 'icon icon-xs') ?> <?= (int) $t['duree_minutes'] ?> min</span>
                        <?php endif; ?>
                        <span><?= ui_icon('user', 'icon icon-xs') ?> <?= htmlspecialchars($t['prenom'] . ' ' . $t['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="places-badge <?= (int) $t['places_restantes'] === 0 ? 'places-full' : '' ?>">
                            <?= ui_icon('seats', 'icon icon-xs') ?> <?= (int) $t['places_restantes'] ?> place(s)
                        </span>
                    </div>
                    <div class="tlc-action">
                        <span class="tlc-price money-value"><?= number_format((float) $t['prix'], 2) ?> TND</span>
                        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= (int) $t['id'] ?>" class="btn btn-primary btn-sm">
                            <?= ui_icon('view', 'icon icon-xs') ?>
                            <span>Voir</span>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
