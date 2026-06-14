<?php $pageTitle = 'Paramètres administratifs'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container container-sm">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline btn-sm">
            <?= ui_icon('arrow-left', 'icon icon-xs') ?>
            <span>Retour</span>
        </a>
        <h1 class="page-title">
            <?= ui_icon('edit', 'icon icon-md') ?>
            <span>Paramètres administratifs</span>
        </h1>
    </div>

    <div class="card form-card">
        <div class="card-header">
            <h2 class="section-subtitle">Barème de points</h2>
            <p class="field-hint">
                Ce barème définit le nombre de points gagnés par kilomètre (règle : 1 km = N points).
                Par défaut 250 pts/km. Les trajets déjà créés conservent leur barème d'origine.
            </p>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/index.php?page=admin&action=updatePricing" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="prix_par_km">Points par kilomètre (pts / km)</label>
                    <input type="number" 
                           id="prix_par_km" 
                           name="prix_par_km" 
                           step="1" 
                           min="1" 
                           max="10000" 
                           value="<?= htmlspecialchars(number_format((float) $currentPrixParKm, 0, '.', ''), ENT_QUOTES, 'UTF-8') ?>" 
                           required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">
                        <?= ui_icon('check', 'icon icon-sm') ?>
                        <span>Enregistrer le barème</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
