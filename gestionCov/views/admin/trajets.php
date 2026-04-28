<?php $pageTitle = 'Administration - Trajets'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('route', 'icon icon-md') ?>
            <span>Gestion des trajets</span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Dashboard</span>
        </a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Trajet</th>
                <th>Conducteur</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Prix</th>
                <th>Places</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($trajets as $t): ?>
                <tr>
                    <td><?= (int) $t['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($t['ville_depart'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="arrow-small">→</span>
                        <strong><?= htmlspecialchars($t['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </td>
                    <td><?= htmlspecialchars($t['prenom'] . ' ' . $t['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= date('d/m/Y', strtotime($t['date_depart'])) ?></td>
                    <td><?= substr($t['heure_depart'], 0, 5) ?></td>
                    <td><?= number_format((float) $t['prix'], 2) ?> TND</td>
                    <td>
                        <span class="places-badge <?= (int) $t['places_restantes'] === 0 ? 'places-full' : '' ?>">
                            <?= (int) $t['places_restantes'] ?>/<?= (int) $t['places_total'] ?>
                        </span>
                    </td>
                    <td>
                        <form action="<?= BASE_URL ?>/index.php?page=admin&action=deleteTrajet"
                              method="POST"
                              class="inline-form"
                              onsubmit="return confirm('Supprimer ce trajet et toutes ses réservations ?')">
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
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
