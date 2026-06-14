<?php
$pageTitle = 'Administration - Détail trajet';
$includeMap = !empty($trip['route_geometry']);
require_once ROOT_PATH . '/views/layouts/header.php';

if (!function_exists('admin_detail_date')) {
    function admin_detail_date(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y', $ts) : '-';
    }
}

if (!function_exists('admin_detail_datetime')) {
    function admin_detail_datetime(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y H:i', $ts) : '-';
    }
}

if (!function_exists('admin_res_status_label')) {
    function admin_res_status_label(string $status): string
    {
        return match ($status) {
            'en_attente' => 'En attente',
            'confirmee' => 'Confirmée',
            'refusee' => 'Refusée',
            'annulee' => 'Annulée',
            default => ucfirst($status),
        };
    }
}

if (!function_exists('admin_role_label_detail')) {
    function admin_role_label_detail(string $role): string
    {
        return match ($role) {
            'admin' => 'Administration',
            'conducteur' => 'Conducteur',
            'etudiant' => 'Étudiant',
            'professeur' => 'Professeur',
            default => ucfirst($role),
        };
    }
}

if (!function_exists('admin_res_status_class')) {
    function admin_res_status_class(string $status): string
    {
        return in_array($status, ['en_attente', 'confirmee', 'refusee', 'annulee'], true) ? $status : 'en_attente';
    }
}

if (!function_exists('admin_res_status_icon')) {
    function admin_res_status_icon(string $status): string
    {
        return match ($status) {
            'confirmee' => 'success',
            'refusee' => 'refused',
            'annulee' => 'cancelled',
            default => 'pending',
        };
    }
}

if (!function_exists('admin_trip_status_label_detail')) {
    function admin_trip_status_label_detail(string $status): string
    {
        return match ($status) {
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            default => 'Publié',
        };
    }
}

if (!function_exists('admin_trip_status_icon_detail')) {
    function admin_trip_status_icon_detail(string $status): string
    {
        return match ($status) {
            'termine' => 'success',
            'annule' => 'cancelled',
            default => 'pending',
        };
    }
}
?>

<div class="container admin-page">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('route', 'icon icon-md') ?>
            <span>Détail trajet #<?= (int) ($trip['id'] ?? 0) ?></span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=traceability" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour traçabilité</span>
        </a>
    </div>

    <div class="admin-detail-grid detail-grid">
        <section class="admin-detail-card detail-card app-card">
            <h2>Informations trajet</h2>
            <p><strong>Trajet:</strong> <?= htmlspecialchars((string) ($trip['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($trip['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Date:</strong> <?= admin_detail_date((string) ($trip['date_depart'] ?? '')) ?></p>
            <p><strong>Heure:</strong> <?= htmlspecialchars(substr((string) ($trip['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Points conducteur:</strong> <span class="money-value"><?= number_format(round(($trip['distance_km'] ?? 0) * ($trip['prix_par_km'] ?? 0)), 0, ',', ' ') ?> pts</span></p>
            <p>
                <strong>Distance:</strong>
                <?php if (isset($trip['distance_km']) && $trip['distance_km'] !== null): ?>
                    <?= number_format((float) $trip['distance_km'], 2) ?> km
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
            <p>
                <strong>Durée estimée:</strong>
                <?php if (isset($trip['duree_minutes']) && $trip['duree_minutes'] !== null): ?>
                    <?= (int) $trip['duree_minutes'] ?> min
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
            <p>
                <strong>Barème de points:</strong>
                <?php if (isset($trip['prix_par_km']) && $trip['prix_par_km'] !== null): ?>
                    <?= number_format((float) $trip['prix_par_km'], 0) ?> pts / km
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
            <p>
                <strong>Coordonnées point:</strong>
                <?php if (isset($trip['point_lat'], $trip['point_lng']) && $trip['point_lat'] !== null && $trip['point_lng'] !== null): ?>
                    <?= number_format((float) $trip['point_lat'], 6) ?>, <?= number_format((float) $trip['point_lng'], 6) ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
            <p><strong>Route:</strong> <?= htmlspecialchars((string) ($trip['route_provider'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Calculé le:</strong> <?= admin_detail_datetime((string) ($trip['route_calculated_at'] ?? '')) ?></p>
            <p><strong>Places:</strong> <?= (int) ($trip['places_total'] ?? 0) ?> total / <?= (int) ($trip['places_restantes'] ?? 0) ?> restantes</p>
            <?php $tripStatus = (string) ($trip['statut_trajet'] ?? 'publie'); ?>
            <p>
                <strong>Statut trajet:</strong>
                <span class="trip-status-badge trip-status-<?= htmlspecialchars($tripStatus, ENT_QUOTES, 'UTF-8') ?>">
                    <?= ui_icon(admin_trip_status_icon_detail($tripStatus), 'icon icon-xs') ?>
                    <span><?= htmlspecialchars(admin_trip_status_label_detail($tripStatus), ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            </p>
            <p><strong>Terminé le:</strong> <?= admin_detail_datetime((string) ($trip['completed_at'] ?? '')) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars((string) ($trip['description'] ?? '-'), ENT_QUOTES, 'UTF-8')) ?></p>
            <p><strong>Créé le:</strong> <?= admin_detail_datetime((string) ($trip['created_at'] ?? '')) ?></p>
            <p><strong>Mis à jour le:</strong> <?= admin_detail_datetime((string) ($trip['updated_at'] ?? '')) ?></p>
        </section>

        <section class="admin-detail-card detail-card app-card">
            <h2>Conducteur</h2>
            <p><strong>Nom:</strong> <?= htmlspecialchars(trim(($trip['conducteur_prenom'] ?? '') . ' ' . ($trip['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($trip['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars((string) ($trip['conducteur_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Rôle:</strong> <?= htmlspecialchars(admin_role_label_detail((string) ($trip['conducteur_role'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Statut compte:</strong> <?= htmlspecialchars((string) ($trip['conducteur_statut_compte'] ?? 'actif'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="admin-detail-card detail-card app-card">
            <h2>Résumé financier estimé</h2>
            <p><strong>Total réservations:</strong> <?= (int) ($tripSummary['total_reservations'] ?? 0) ?></p>
            <p><strong>Confirmées:</strong> <?= (int) ($tripSummary['confirmed_count'] ?? 0) ?></p>
            <p><strong>En attente:</strong> <?= (int) ($tripSummary['pending_count'] ?? 0) ?></p>
            <p><strong>Refusées:</strong> <?= (int) ($tripSummary['refused_count'] ?? 0) ?></p>
            <p><strong>Annulées:</strong> <?= (int) ($tripSummary['cancelled_count'] ?? 0) ?></p>
            <p><strong>Points confirmés estimés:</strong> <span class="money-value"><?= number_format((float) ($tripSummary['estimated_confirmed_revenue'] ?? 0), 0, '.', ' ') ?> pts</span></p>
            <p><strong>Réservations déclarées payées:</strong> <?= (int) ($tripSummary['paid_declared_count'] ?? 0) ?></p>
            <p><strong>Points attribués:</strong> <span class="money-value"><?= number_format((float) ($tripSummary['declared_total'] ?? 0), 0, '.', ' ') ?> pts</span></p>
        </section>

        <?php if (!empty($trip['route_geometry'])): ?>
            <section class="admin-detail-card detail-card app-card">
                <h2>Circuit proposé</h2>
                <div id="tripPreviewMap"
                     class="circuit-preview-map compact-map"
                     data-sesame-lat="<?= htmlspecialchars((string) (defined('SESAME_LAT') ? SESAME_LAT : 0), ENT_QUOTES, 'UTF-8') ?>"
                     data-sesame-lng="<?= htmlspecialchars((string) (defined('SESAME_LNG') ? SESAME_LNG : 0), ENT_QUOTES, 'UTF-8') ?>"
                     data-route-geometry="<?= htmlspecialchars((string) $trip['route_geometry'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </section>
        <?php endif; ?>
    </div>

    <section class="detail-section">
        <h2 class="section-subtitle">Réservations liées à ce trajet</h2>
        <div class="table-wrapper data-card">
            <table class="data-table table-modern table-compact data-sortable-table">
                <thead>
                <tr>
                    <th class="data-sortable-column" data-sort-type="number">ID</th>
                    <th class="data-sortable-column" data-sort-type="text">Passager</th>
                    <th class="data-sortable-column" data-sort-type="text">Contact</th>
                    <th class="data-sortable-column" data-sort-type="text">Statut</th>
                    <th class="data-sortable-column" data-sort-type="date">Réservé le</th>
                    <th class="data-sortable-column" data-sort-type="text">Point</th>
                    <th class="data-sortable-column" data-sort-type="number">Distance</th>
                    <th class="data-sortable-column" data-sort-type="number">Points estimés</th>
                    <th class="data-sortable-column" data-sort-type="text">Points gagnés</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tripReservationRows)): ?>
                    <tr>
                        <td colspan="10" class="empty-state">Aucune réservation pour ce trajet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tripReservationRows as $row): ?>
                        <?php
                        $status = (string) ($row['statut'] ?? 'en_attente');
                        $reservationPrice = $row['reservation_price'] ?? $row['prix_snapshot'] ?? $row['trajet_prix'] ?? 0;
                        $reservationDistance = $row['reservation_distance_km'] ?? null;
                        $reservationType = (string) ($row['reservation_point_type'] ?? '');
                        $pointLabel = $reservationType === 'prise_en_charge'
                            ? 'Prise en charge'
                            : ($reservationType === 'depose' ? 'Dépose' : '');
                        
                        $prixParKm = (float) ($trip['prix_par_km'] ?? 250);
                        if ($prixParKm <= 10) $prixParKm = 250;
                        $pointsEstimes = $reservationDistance !== null
                            ? max(1, (int) round($reservationDistance * $prixParKm))
                            : max(0, (int) round((float) $reservationPrice));
                            
                        $paymentDeclared = ($row['payment_status'] ?? '') === 'declare_paye';
                        $pointsGagnes = (int) ($row['points_earned'] ?? 0);
                        ?>
                        <tr>
                            <td data-sort-value="<?= (int) ($row['reservation_id'] ?? 0) ?>">#<?= (int) ($row['reservation_id'] ?? 0) ?></td>
                            <td class="admin-user-cell">
                                <strong><?= htmlspecialchars(trim(($row['passager_prenom'] ?? '') . ' ' . ($row['passager_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small class="admin-muted"><?= htmlspecialchars(admin_role_label_detail((string) ($row['passager_role'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td class="admin-user-cell">
                                <?= htmlspecialchars((string) ($row['passager_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?><br>
                                <small class="admin-muted"><?= htmlspecialchars((string) ($row['passager_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <span class="status-badge status-pill status-<?= htmlspecialchars(admin_res_status_class($status), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ui_icon(admin_res_status_icon($status), 'icon icon-xs') ?>
                                    <span><?= htmlspecialchars(admin_res_status_label($status), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </td>
                            <td><?= admin_detail_datetime((string) ($row['reservation_created_at'] ?? '')) ?></td>
                            <td class="compact-point">
                                <?php if ($pointLabel !== '' && isset($row['reservation_point_lat'], $row['reservation_point_lng'])): ?>
                                    <span><?= htmlspecialchars($pointLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                    <small class="admin-muted"><?= number_format((float) $row['reservation_point_lat'], 5) ?>, <?= number_format((float) $row['reservation_point_lng'], 5) ?></small>
                                <?php else: ?>
                                    <span class="admin-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-sort-value="<?= $reservationDistance !== null ? number_format((float) $reservationDistance, 2, '.', '') : '' ?>">
                                <?php if ($reservationDistance !== null): ?>
                                    <?= number_format((float) $reservationDistance, 2) ?> km
                                <?php else: ?>
                                    <span class="admin-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-sort-value="<?= $pointsEstimes ?>"><span class="money-value"><?= number_format($pointsEstimes, 0, '.', ' ') ?> pts</span></td>
                            <td>
                                <?php if ($pointsGagnes > 0): ?>
                                    <span class="compact-payment is-declared"><?= number_format($pointsGagnes, 0, '.', ' ') ?> pts</span>
                                <?php else: ?>
                                    <span class="compact-payment">En attente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/index.php?page=admin&action=reservationDetails&id=<?= (int) ($row['reservation_id'] ?? 0) ?>" class="btn btn-outline btn-xs">
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

