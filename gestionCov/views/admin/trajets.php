<?php $pageTitle = 'Admin — Trajets'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="page-header-row">
        <h1 class="page-title">🚗 Gestion des trajets</h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">← Dashboard</a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Trajet</th><th>Conducteur</th><th>Date</th><th>Heure</th>
                    <th>Prix</th><th>Places</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trajets as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($t['ville_depart']) ?></strong>
                        <span class="arrow-small">→</span>
                        <strong><?= htmlspecialchars($t['ville_arrivee']) ?></strong>
                    </td>
                    <td><?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?></td>
                    <td><?= date('d/m/Y', strtotime($t['date_depart'])) ?></td>
                    <td><?= substr($t['heure_depart'], 0, 5) ?></td>
                    <td><?= number_format($t['prix'], 2) ?> TND</td>
                    <td>
                        <span class="places-badge <?= $t['places_restantes'] == 0 ? 'places-full' : '' ?>">
                            <?= $t['places_restantes'] ?>/<?= $t['places_total'] ?>
                        </span>
                    </td>
                    <td>
                        <form action="<?= BASE_URL ?>/index.php?page=admin&action=deleteTrajet" method="POST"
                              class="inline-form"
                              onsubmit="return confirm('Supprimer ce trajet et toutes ses réservations ?')">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">🗑 Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
