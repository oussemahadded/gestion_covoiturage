<?php $pageTitle = 'Laisser un avis'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container container--narrow">
    <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= (int) $trajet['id'] ?>" class="back-link">
        <?= ui_icon('arrow-left', 'icon icon-sm') ?>
        <span>Retour au trajet</span>
    </a>
    <h1 class="page-title">
        <?= ui_icon('star', 'icon icon-md') ?>
        <span>Laisser un avis</span>
    </h1>

    <div class="form-card">
        <div class="review-trajet-info">
            <strong><?= htmlspecialchars($trajet['ville_depart'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($trajet['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= ui_icon('calendar', 'icon icon-xs') ?> <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?></span>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=avis&action=create&trajet_id=<?= (int) $trajet['id'] ?>" method="POST">
            <div class="form-group">
                <label>Note *</label>
                <div class="star-picker" id="starPicker">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="star-label" for="star<?= $i ?>">
                            <input type="radio" name="note" id="star<?= $i ?>" value="<?= $i ?>" required>
                            <span class="star-pick"><?= ui_icon('star', 'icon icon-md') ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="commentaire">Commentaire (optionnel)</label>
                <textarea id="commentaire" name="commentaire" rows="4" placeholder="Partagez votre expérience avec ce conducteur..."></textarea>
            </div>
            <div class="form-actions">
                <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= (int) $trajet['id'] ?>" class="btn btn-outline">
                    <?= ui_icon('x', 'icon icon-sm') ?>
                    <span>Annuler</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= ui_icon('send', 'icon icon-sm') ?>
                    <span>Publier l'avis</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
