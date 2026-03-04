<?php
$pageTitle = 'Modifier le trajet';
$errors    = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
require_once ROOT_PATH . '/views/layouts/header.php';
?>

<div class="container container--narrow">
    <a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="back-link">← Mes trajets</a>
    <h1 class="page-title">✏️ Modifier le trajet</h1>

    <?php if (!empty($errors)): ?>
    <ul class="error-list">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <div class="form-card">
        <form action="<?= BASE_URL ?>/index.php?page=trajet&action=edit&id=<?= $trajet['id'] ?>" method="POST" novalidate>

            <div class="form-row">
                <div class="form-group">
                    <label for="ville_depart">🏙 Ville de départ *</label>
                    <input type="text" id="ville_depart" name="ville_depart"
                           value="<?= htmlspecialchars($trajet['ville_depart']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="ville_arrivee">🎯 Ville d'arrivée *</label>
                    <input type="text" id="ville_arrivee" name="ville_arrivee"
                           value="<?= htmlspecialchars($trajet['ville_arrivee']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date_depart">📅 Date de départ *</label>
                    <input type="date" id="date_depart" name="date_depart"
                           value="<?= htmlspecialchars($trajet['date_depart']) ?>"
                           min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="heure_depart">🕐 Heure *</label>
                    <input type="time" id="heure_depart" name="heure_depart"
                           value="<?= htmlspecialchars(substr($trajet['heure_depart'], 0, 5)) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prix">💰 Prix (TND) *</label>
                    <input type="number" id="prix" name="prix" step="0.01" min="0"
                           value="<?= htmlspecialchars($trajet['prix']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="places_total">💺 Places totales *</label>
                    <input type="number" id="places_total" name="places_total" min="1" max="8"
                           value="<?= htmlspecialchars($trajet['places_total']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">📝 Description</label>
                <textarea id="description" name="description" rows="3"><?= htmlspecialchars($trajet['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
