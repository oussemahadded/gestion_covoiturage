<?php
$pageTitle = 'Administration - Recettes déclarées';
require_once ROOT_PATH . '/views/layouts/header.php';

if (!function_exists('admin_finance_money')) {
    function admin_finance_money(mixed $value): string
    {
        return number_format((float) $value, 2) . ' TND';
    }
}

if (!function_exists('admin_finance_date')) {
    function admin_finance_date(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y', $ts) : '-';
    }
}

if (!function_exists('admin_finance_datetime')) {
    function admin_finance_datetime(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y H:i', $ts) : '-';
    }
}

if (!function_exists('admin_finance_month')) {
    function admin_finance_month(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value . '-01');
        return $ts ? date('m/Y', $ts) : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="container">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('price', 'icon icon-md') ?>
            <span>Recettes déclarées</span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour au dashboard</span>
        </a>
    </div>

    <section class="finance-grid">
        <article class="finance-card">
            <div class="finance-card-title">Total global déclaré</div>
            <div class="finance-card-value money-value"><?= admin_finance_money($financeStats['total_global'] ?? 0) ?></div>
        </article>
        <article class="finance-card">
            <div class="finance-card-title">Total déclaré cette semaine</div>
            <div class="finance-card-value money-value"><?= admin_finance_money($financeStats['total_week'] ?? 0) ?></div>
        </article>
        <article class="finance-card">
            <div class="finance-card-title">Total déclaré ce mois</div>
            <div class="finance-card-value money-value"><?= admin_finance_money($financeStats['total_month'] ?? 0) ?></div>
        </article>
        <article class="finance-card">
            <div class="finance-card-title">Trajets terminés</div>
            <div class="finance-card-value"><?= (int) ($financeStats['completed_trips_count'] ?? 0) ?></div>
        </article>
        <article class="finance-card">
            <div class="finance-card-title">Réservations déclarées payées</div>
            <div class="finance-card-value"><?= (int) ($financeStats['paid_reservations_count'] ?? 0) ?></div>
        </article>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Recette conducteur déclarée</h2>
        <div class="table-wrapper">
            <table class="data-table finance-table">
                <thead>
                <tr>
                    <th>Conducteur</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Trajets terminés</th>
                    <th>Réservations déclarées payées</th>
                    <th>Total déclaré</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($revenueByConducteur)): ?>
                    <tr><td colspan="6" class="empty-state">Aucune recette déclarée.</td></tr>
                <?php else: ?>
                    <?php foreach ($revenueByConducteur as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(trim(($row['conducteur_prenom'] ?? '') . ' ' . ($row['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars((string) ($row['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($row['conducteur_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) ($row['completed_trips_count'] ?? 0) ?></td>
                            <td><?= (int) ($row['paid_reservations_count'] ?? 0) ?></td>
                            <td><span class="money-value"><?= admin_finance_money($row['declared_total'] ?? 0) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Recette déclarée par semaine</h2>
        <div class="table-wrapper">
            <table class="data-table finance-table">
                <thead>
                <tr>
                    <th>Semaine</th>
                    <th>Réservations déclarées</th>
                    <th>Total déclaré</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($revenueByWeek)): ?>
                    <tr><td colspan="3" class="empty-state">Aucune recette déclarée par semaine.</td></tr>
                <?php else: ?>
                    <?php foreach ($revenueByWeek as $row): ?>
                        <tr>
                            <td>Semaine du <?= admin_finance_date((string) ($row['week_start'] ?? '')) ?></td>
                            <td><?= (int) ($row['paid_reservations_count'] ?? 0) ?></td>
                            <td><span class="money-value"><?= admin_finance_money($row['declared_total'] ?? 0) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Recette déclarée par mois</h2>
        <div class="table-wrapper">
            <table class="data-table finance-table">
                <thead>
                <tr>
                    <th>Mois</th>
                    <th>Réservations déclarées</th>
                    <th>Total déclaré</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($revenueByMonth)): ?>
                    <tr><td colspan="3" class="empty-state">Aucune recette déclarée par mois.</td></tr>
                <?php else: ?>
                    <?php foreach ($revenueByMonth as $row): ?>
                        <tr>
                            <td><?= admin_finance_month((string) ($row['month_key'] ?? '')) ?></td>
                            <td><?= (int) ($row['paid_reservations_count'] ?? 0) ?></td>
                            <td><span class="money-value"><?= admin_finance_money($row['declared_total'] ?? 0) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Trajets terminés</h2>
        <div class="table-wrapper">
            <table class="data-table finance-table">
                <thead>
                <tr>
                    <th>Trip ID</th>
                    <th>Conducteur</th>
                    <th>Route</th>
                    <th>Date trajet</th>
                    <th>Terminé le</th>
                    <th>Réservations confirmées</th>
                    <th>Total déclaré</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($completedTripRows)): ?>
                    <tr><td colspan="8" class="empty-state">Aucun trajet terminé.</td></tr>
                <?php else: ?>
                    <?php foreach ($completedTripRows as $trip): ?>
                        <tr class="completed-trip-row">
                            <td>#<?= (int) ($trip['id'] ?? 0) ?></td>
                            <td>
                                <strong><?= htmlspecialchars(trim(($trip['conducteur_prenom'] ?? '') . ' ' . ($trip['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small><?= htmlspecialchars((string) ($trip['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= htmlspecialchars((string) ($trip['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($trip['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= admin_finance_date((string) ($trip['date_depart'] ?? '')) ?> <?= htmlspecialchars(substr((string) ($trip['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= admin_finance_datetime((string) ($trip['completed_at'] ?? '')) ?></td>
                            <td><?= (int) ($trip['confirmed_count'] ?? 0) ?></td>
                            <td><span class="money-value"><?= admin_finance_money($trip['declared_total'] ?? 0) ?></span></td>
                            <td>
                                <a href="<?= BASE_URL ?>/index.php?page=admin&action=tripDetails&id=<?= (int) ($trip['id'] ?? 0) ?>" class="btn btn-outline btn-xs">
                                    <?= ui_icon('view', 'icon icon-xs') ?>
                                    <span>Détails</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
