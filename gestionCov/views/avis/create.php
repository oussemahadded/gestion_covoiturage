<?php $pageTitle = 'Laisser un avis'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container container--narrow">
    <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= $trajet['id'] ?>" class="back-link">← Retour au trajet</a>
    <h1 class="page-title">⭐ Laisser un avis</h1>

    <div class="form-card">
        <div class="review-trajet-info">
            <strong><?= htmlspecialchars($trajet['ville_depart']) ?> → <?= htmlspecialchars($trajet['ville_arrivee']) ?></strong>
            <span>📅 <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?></span>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=avis&action=create&trajet_id=<?= $trajet['id'] ?>" method="POST">
            <div class="form-group">
                <label>Note *</label>
                <div class="star-picker" id="starPicker">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label class="star-label" for="star<?= $i ?>">
                        <input type="radio" name="note" id="star<?= $i ?>" value="<?= $i ?>" required>
                        <span class="star-pick">★</span>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="commentaire">Commentaire (optionnel)</label>
                <textarea id="commentaire" name="commentaire" rows="4"
                          placeholder="Partagez votre expérience avec ce conducteur..."></textarea>
            </div>
            <div class="form-actions">
                <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= $trajet['id'] ?>" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">📨 Publier l'avis</button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
