<?php $pageTitle = 'Mes trajets'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="page-header-row">
        <h1 class="page-title">📋 Mes trajets</h1>
        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=create" class="btn btn-primary">+ Proposer un trajet</a>
    </div>

    <?php if (empty($trajets)): ?>
    <div class="empty-state-box">
        <span style="font-size:3rem;">🚗</span>
        <p>Vous n'avez pas encore proposé de trajet.</p>
        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=create" class="btn btn-primary">Proposer mon premier trajet</a>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Trajet</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Prix</th>
                    <th>Places</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trajets as $t): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($t['ville_depart']) ?></strong>
                        <span class="arrow-small">→</span>
                        <strong><?= htmlspecialchars($t['ville_arrivee']) ?></strong>
                    </td>
                    <td><?= date('d/m/Y', strtotime($t['date_depart'])) ?></td>
                    <td><?= substr($t['heure_depart'], 0, 5) ?></td>
                    <td><?= number_format($t['prix'], 2) ?> TND</td>
                    <td>
                        <span class="places-badge <?= $t['places_restantes'] == 0 ? 'places-full' : '' ?>">
                            <?= $t['places_restantes'] ?>/<?= $t['places_total'] ?>
                        </span>
                    </td>
                    <td class="table-actions">
                        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= $t['id'] ?>"
                           class="btn btn-outline btn-xs">👁 Voir</a>
                        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=edit&id=<?= $t['id'] ?>"
                           class="btn btn-warning btn-xs">✏️ Modifier</a>
                        <form action="<?= BASE_URL ?>/index.php?page=trajet&action=delete" method="POST"
                              class="inline-form"
                              onsubmit="return confirm('Supprimer ce trajet définitivement ?')">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">🗑 Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
