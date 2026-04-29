<?php
$pageTitle = 'Mes trajets';

if (!function_exists('trip_status_label')) {
    function trip_status_label(string $status): string
    {
        return match ($status) {
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            default => 'Publié',
        };
    }
}

if (!function_exists('trip_status_icon')) {
    function trip_status_icon(string $status): string
    {
        return match ($status) {
            'termine' => 'success',
            'annule' => 'cancelled',
            default => 'pending',
        };
    }
}

if (!function_exists('trip_datetime_passed')) {
    function trip_datetime_passed(array $trip): bool
    {
        $ts = strtotime((string) ($trip['date_depart'] ?? '') . ' ' . (string) ($trip['heure_depart'] ?? ''));
        return $ts !== false && $ts <= time();
    }
}

if (!function_exists('format_trip_datetime')) {
    function format_trip_datetime(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y H:i', $ts) : '-';
    }
}

require_once ROOT_PATH . '/views/layouts/header.php';
?>

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
                    <th>Distance</th>
                    <th>Durée</th>
                    <th>Prix</th>
                    <th>Places</th>
                    <th>Statut trajet</th>
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
                        <td>
                            <?php if (isset($t['distance_km']) && $t['distance_km'] !== null): ?>
                                <?= number_format((float) $t['distance_km'], 2) ?> km
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($t['duree_minutes']) && $t['duree_minutes'] !== null): ?>
                                <?= (int) $t['duree_minutes'] ?> min
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= number_format((float) $t['prix'], 2) ?> TND</td>
                        <td>
                            <span class="places-badge <?= (int) $t['places_restantes'] === 0 ? 'places-full' : '' ?>">
                                <?= (int) $t['places_restantes'] ?>/<?= (int) $t['places_total'] ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $tripStatus = (string) ($t['statut_trajet'] ?? 'publie');
                            $hasPassed = trip_datetime_passed($t);
                            $confirmedCount = (int) ($t['confirmed_count'] ?? 0);
                            ?>
                            <span class="trip-status-badge trip-status-<?= htmlspecialchars($tripStatus, ENT_QUOTES, 'UTF-8') ?>">
                                <?= ui_icon(trip_status_icon($tripStatus), 'icon icon-xs') ?>
                                <span><?= htmlspecialchars(trip_status_label($tripStatus), ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                            <?php if ($tripStatus === 'termine'): ?>
                                <small class="trip-status-note">Trajet terminé le <?= format_trip_datetime((string) ($t['completed_at'] ?? '')) ?></small>
                            <?php elseif ($tripStatus === 'publie' && !$hasPassed): ?>
                                <small class="trip-status-note">Disponible après l'horaire de départ.</small>
                            <?php elseif ($tripStatus === 'publie' && $confirmedCount === 0): ?>
                                <small class="trip-status-note">Aucune réservation confirmée à déclarer.</small>
                            <?php endif; ?>
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
                            <?php if ($tripStatus === 'publie' && $hasPassed && $confirmedCount > 0): ?>
                                <form action="<?= BASE_URL ?>/index.php?page=trajet&action=complete"
                                      method="POST"
                                      class="inline-form"
                                      onsubmit="return confirm('Confirmer que ce trajet est terminé ? Les réservations confirmées seront déclarées payées en espèces.')">
                                    <input type="hidden" name="trajet_id" value="<?= (int) $t['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-xs">
                                        <?= ui_icon('check', 'icon icon-xs') ?>
                                        <span>Marquer comme terminé</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
