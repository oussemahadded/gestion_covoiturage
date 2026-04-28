<?php $pageTitle = 'Mes trajets'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('car', 'icon icon-md') ?>
            <span>Mes trajets</span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=create" class="btn btn-primary">
            <?= ui_icon('plus', 'icon icon-sm') ?>
            <span>Proposer un trajet</span>
        </a>
    </div>

    <?php if (empty($trajets)): ?>
        <div class="empty-state-box">
            <span class="empty-illustration"><?= ui_icon('car', 'icon icon-xl') ?></span>
            <p>Vous n'avez pas encore proposé de trajet.</p>
            <a href="<?= BASE_URL ?>/index.php?page=trajet&action=create" class="btn btn-primary">
                <?= ui_icon('plus', 'icon icon-sm') ?>
                <span>Proposer mon premier trajet</span>
            </a>
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
                            <strong><?= htmlspecialchars($t['ville_depart'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="arrow-small">→</span>
                            <strong><?= htmlspecialchars($t['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </td>
                        <td><?= date('d/m/Y', strtotime($t['date_depart'])) ?></td>
                        <td><?= substr($t['heure_depart'], 0, 5) ?></td>
                        <td><?= number_format((float) $t['prix'], 2) ?> TND</td>
                        <td>
                            <span class="places-badge <?= (int) $t['places_restantes'] === 0 ? 'places-full' : '' ?>">
                                <?= (int) $t['places_restantes'] ?>/<?= (int) $t['places_total'] ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= (int) $t['id'] ?>" class="btn btn-outline btn-xs">
                                <?= ui_icon('view', 'icon icon-xs') ?>
                                <span>Voir</span>
                            </a>
                            <a href="<?= BASE_URL ?>/index.php?page=trajet&action=edit&id=<?= (int) $t['id'] ?>" class="btn btn-warning btn-xs">
                                <?= ui_icon('edit', 'icon icon-xs') ?>
                                <span>Modifier</span>
                            </a>
                            <form action="<?= BASE_URL ?>/index.php?page=trajet&action=delete"
                                  method="POST"
                                  class="inline-form"
                                  onsubmit="return confirm('Supprimer ce trajet définitivement ?')">
                                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <?= ui_icon('delete', 'icon icon-xs') ?>
                                    <span>Supprimer</span>
                                </button>
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
