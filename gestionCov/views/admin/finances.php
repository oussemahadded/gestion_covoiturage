<?php
$pageTitle = 'Administration - Suivi des points';
require_once ROOT_PATH . '/views/layouts/header.php';

if (!function_exists('admin_finance_money')) {
    function admin_finance_money(mixed $value): string
    {
        return (int)$value . ' points';
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

<div class="container admin-page finance-page">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('reward', 'icon icon-md') ?>
            <span>Suivi des points</span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour au dashboard</span>
        </a>
    </div>

    <section class="finance-kpi-grid">
        <article class="finance-kpi-card">
            <div class="finance-kpi-icon"><?= ui_icon('reward', 'icon icon-md') ?></div>
            <div class="finance-kpi-content">
                <div class="finance-kpi-label">Total points distribués</div>
                <div class="finance-kpi-value"><?= (int) ($financeStats['total_global'] ?? 0) ?> points</div>
            </div>
        </article>
        <article class="finance-kpi-card">
            <div class="finance-kpi-icon"><?= ui_icon('calendar', 'icon icon-md') ?></div>
            <div class="finance-kpi-content">
                <div class="finance-kpi-label">Points distribués cette semaine</div>
                <div class="finance-kpi-value"><?= (int) ($financeStats['total_week'] ?? 0) ?> points</div>
            </div>
        </article>
        <article class="finance-kpi-card">
            <div class="finance-kpi-icon"><?= ui_icon('calendar', 'icon icon-md') ?></div>
            <div class="finance-kpi-content">
                <div class="finance-kpi-label">Points distribués ce mois</div>
                <div class="finance-kpi-value"><?= (int) ($financeStats['total_month'] ?? 0) ?> points</div>
            </div>
        </article>
        <article class="finance-kpi-card">
            <div class="finance-kpi-icon"><?= ui_icon('route', 'icon icon-md') ?></div>
            <div class="finance-kpi-content">
                <div class="finance-kpi-label">Trajets complétés</div>
                <div class="finance-kpi-value"><?= (int) ($financeStats['completed_trips_count'] ?? 0) ?></div>
            </div>
        </article>
        <article class="finance-kpi-card">
            <div class="finance-kpi-icon"><?= ui_icon('check', 'icon icon-md') ?></div>
            <div class="finance-kpi-content">
                <div class="finance-kpi-label">Conducteurs à 100+ points</div>
                <div class="finance-kpi-value"><?= (int) ($financeStats['eligible_conductors_count'] ?? 0) ?></div>
            </div>
        </article>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Points par conducteur</h2>
        <div class="table-wrapper data-card">
            <table class="data-table table-modern finance-table data-sortable-table">
                <thead>
                <tr>
                    <th class="data-sortable-column" data-sort-type="text">Conducteur</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th class="data-sortable-column" data-sort-type="number">Trajets terminés</th>
                    <th class="data-sortable-column" data-sort-type="number">Points totaux</th>
                    <th class="data-sortable-column" data-sort-type="number">Statut SESAME</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($revenueByConducteur)): ?>
                    <tr><td colspan="6" class="empty-state">Aucune donnée de points.</td></tr>
                <?php else: ?>
                    <?php foreach ($revenueByConducteur as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(trim(($row['conducteur_prenom'] ?? '') . ' ' . ($row['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars((string) ($row['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($row['conducteur_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-sort-value="<?= (int) ($row['completed_trips_count'] ?? 0) ?>"><?= (int) ($row['completed_trips_count'] ?? 0) ?></td>
                            <td data-sort-value="<?= (int) ($row['points_total'] ?? 0) ?>"><strong><?= (int) ($row['points_total'] ?? 0) ?> pts</strong></td>
                            <td data-sort-value="<?= !empty($row['eligibilite_remise_sesame_at']) ? 'eligible' : 'ineligible' ?>">
                                <?php if (!empty($row['eligibilite_remise_sesame_at'])): ?>
                                    <span class="badge badge-success">✓ Éligible</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">— Inéligible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Points distribués par semaine</h2>
        <div class="table-wrapper data-card">
            <table class="data-table table-modern finance-table data-sortable-table">
                <thead>
                <tr>
                    <th class="data-sortable-column" data-sort-type="date">Semaine</th>
                    <th class="data-sortable-column" data-sort-type="number">Réservations complétées</th>
                    <th class="data-sortable-column" data-sort-type="number">Total points</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($revenueByWeek)): ?>
                    <tr><td colspan="3" class="empty-state">Aucun point distribué cette semaine.</td></tr>
                <?php else: ?>
                    <?php foreach ($revenueByWeek as $row): ?>
                        <tr>
                            <td data-sort-value="<?= admin_finance_date((string) ($row['week_start'] ?? '')) ?>">Semaine du <?= admin_finance_date((string) ($row['week_start'] ?? '')) ?></td>
                            <td data-sort-value="<?= (int) ($row['paid_reservations_count'] ?? 0) ?>"><?= (int) ($row['paid_reservations_count'] ?? 0) ?></td>
                            <td data-sort-value="<?= (int) ($row['declared_total'] ?? 0) ?>"><span class="money-value"><?= admin_finance_money($row['declared_total'] ?? 0) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Points distribués par mois</h2>
        <div class="table-wrapper data-card">
            <table class="data-table table-modern finance-table data-sortable-table">
                <thead>
                <tr>
                    <th class="data-sortable-column" data-sort-type="date">Mois</th>
                    <th class="data-sortable-column" data-sort-type="number">Réservations complétées</th>
                    <th class="data-sortable-column" data-sort-type="number">Total points</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($revenueByMonth)): ?>
                    <tr><td colspan="3" class="empty-state">Aucun point distribué ce mois.</td></tr>
                <?php else: ?>
                    <?php foreach ($revenueByMonth as $row): ?>
                        <tr>
                            <?php $monthLabel = admin_finance_month((string) ($row['month_key'] ?? '')); ?>
                            <td data-sort-value="<?= htmlspecialchars('01/' . $monthLabel, ENT_QUOTES, 'UTF-8') ?>"><?= $monthLabel ?></td>
                            <td data-sort-value="<?= (int) ($row['paid_reservations_count'] ?? 0) ?>"><?= (int) ($row['paid_reservations_count'] ?? 0) ?></td>
                            <td data-sort-value="<?= (int) ($row['declared_total'] ?? 0) ?>"><span class="money-value"><?= admin_finance_money($row['declared_total'] ?? 0) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Trajets terminés</h2>
        <div class="table-wrapper data-card">
            <table class="data-table table-modern finance-table data-sortable-table">
                <thead>
                <tr>
                    <th class="data-sortable-column" data-sort-type="number">Trip ID</th>
                    <th class="data-sortable-column" data-sort-type="text">Conducteur</th>
                    <th class="data-sortable-column" data-sort-type="text">Route</th>
                    <th class="data-sortable-column" data-sort-type="date">Date trajet</th>
                    <th class="data-sortable-column" data-sort-type="date">Terminé le</th>
                    <th class="data-sortable-column" data-sort-type="number">Réservations confirmées</th>
                    <th class="data-sortable-column" data-sort-type="number">Points attribués</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($completedTripRows)): ?>
                    <tr><td colspan="8" class="empty-state">Aucun trajet terminé.</td></tr>
                <?php else: ?>
                    <?php foreach ($completedTripRows as $trip): ?>
                        <tr class="completed-trip-row">
                            <td data-sort-value="<?= (int) ($trip['id'] ?? 0) ?>">#<?= (int) ($trip['id'] ?? 0) ?></td>
                            <td>
                                <strong><?= htmlspecialchars(trim(($trip['conducteur_prenom'] ?? '') . ' ' . ($trip['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small><?= htmlspecialchars((string) ($trip['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= htmlspecialchars((string) ($trip['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($trip['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= admin_finance_date((string) ($trip['date_depart'] ?? '')) ?> <?= htmlspecialchars(substr((string) ($trip['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= admin_finance_datetime((string) ($trip['completed_at'] ?? '')) ?></td>
                            <td data-sort-value="<?= (int) ($trip['confirmed_count'] ?? 0) ?>"><?= (int) ($trip['confirmed_count'] ?? 0) ?></td>
                            <td data-sort-value="<?= (int) ($trip['declared_total'] ?? 0) ?>"><span class="money-value"><?= admin_finance_money($trip['declared_total'] ?? 0) ?></span></td>
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

