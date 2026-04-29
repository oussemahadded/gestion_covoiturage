<?php $pageTitle = 'Paramètres administratifs'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container container-sm">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline btn-sm">
            <?= ui_icon('arrow-left', 'icon icon-xs') ?>
            <span>Retour</span>
        </a>
        <h1 class="page-title" style="margin-bottom:0; font-size:1.5rem;">
            <?= ui_icon('edit', 'icon icon-md') ?>
            <span>Paramètres administratifs</span>
        </h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Tarif kilométrique</h2>
            <p class="text-muted" style="margin-top:.25rem; font-size:.875rem;">
                Ce tarif est appliqué aux nouveaux trajets. Les trajets déjà créés conservent leur tarif enregistré pour la traçabilité.
            </p>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/index.php?page=admin&action=updatePricing" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="prix_par_km">Tarif kilométrique par défaut (TND / km)</label>
                    <input type="number" 
                           id="prix_par_km" 
                           name="prix_par_km" 
                           step="0.001" 
                           min="0.001" 
                           max="10.000" 
                           value="<?= htmlspecialchars(number_format((float) $currentPrixParKm, 3, '.', ''), ENT_QUOTES, 'UTF-8') ?>" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;">
                    <?= ui_icon('check', 'icon icon-sm') ?>
                    <span>Enregistrer le tarif</span>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
