<?php
$pageTitle   = 'Recherche de trajets';
$searchPerformed = $searchPerformed ?? false;
require_once ROOT_PATH . '/views/layouts/header.php';
?>

<div class="container">
    <h1 class="page-title">🗺 Rechercher un trajet</h1>

    <!-- Formulaire de recherche -->
    <form action="<?= BASE_URL ?>/index.php" method="GET" class="search-form-card">
        <input type="hidden" name="page"   value="trajet">
        <input type="hidden" name="action" value="index">
        <div class="search-grid">
            <div class="form-group">
                <label for="depart">🏙 Ville de départ</label>
                <input type="text" id="depart" name="depart"
                       value="<?= htmlspecialchars($_GET['depart'] ?? '') ?>"
                       placeholder="Ex: Tunis">
            </div>
            <div class="form-group">
                <label for="arrivee">🎯 Ville d'arrivée</label>
                <input type="text" id="arrivee" name="arrivee"
                       value="<?= htmlspecialchars($_GET['arrivee'] ?? '') ?>"
                       placeholder="Ex: Sfax">
            </div>
            <div class="form-group">
                <label for="date">📅 Date</label>
                <input type="date" id="date" name="date"
                       value="<?= htmlspecialchars($_GET['date'] ?? '') ?>"
                       min="<?= date('Y-m-d') ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-search">🔍 Rechercher</button>
        </div>
    </form>

    <!-- Résultats -->
    <?php if ($searchPerformed): ?>
        <p class="results-count">
            <?= count($trajets) ?> trajet(s) trouvé(s)
        </p>
    <?php endif; ?>

    <?php if (empty($trajets) && $searchPerformed): ?>
        <div class="empty-state-box">
            <span>😔</span>
            <p>Aucun trajet ne correspond à votre recherche.</p>
            <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-outline">Voir tous les trajets</a>
        </div>
    <?php elseif (!empty($trajets)): ?>
        <div class="trajets-list">
            <?php foreach ($trajets as $t): ?>
            <div class="trajet-list-card">
                <div class="tlc-route">
                    <div class="route-line">
                        <span class="dot dot-from"></span>
                        <span class="route-dash"></span>
                        <span class="dot dot-to"></span>
                    </div>
                    <div class="route-cities">
                        <span><?= htmlspecialchars($t['ville_depart']) ?></span>
                        <span><?= htmlspecialchars($t['ville_arrivee']) ?></span>
                    </div>
                </div>
                <div class="tlc-info">
                    <span>📅 <?= date('d/m/Y', strtotime($t['date_depart'])) ?></span>
                    <span>🕐 <?= substr($t['heure_depart'], 0, 5) ?></span>
                    <span>🚗 <?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?></span>
                    <span class="places-badge <?= $t['places_restantes'] == 0 ? 'places-full' : '' ?>">
                        💺 <?= $t['places_restantes'] ?> place(s)
                    </span>
                </div>
                <div class="tlc-action">
                    <span class="tlc-price"><?= number_format($t['prix'], 2) ?> TND</span>
                    <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= $t['id'] ?>"
                       class="btn btn-primary btn-sm">Voir →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
