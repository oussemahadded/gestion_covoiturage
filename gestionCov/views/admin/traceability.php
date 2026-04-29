<?php
$pageTitle = 'Administration - Traçabilité';
require_once ROOT_PATH . '/views/layouts/header.php';

if (!function_exists('admin_format_date')) {
    function admin_format_date(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y', $ts) : '-';
    }
}

if (!function_exists('admin_format_datetime')) {
    function admin_format_datetime(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y H:i', $ts) : '-';
    }
}

if (!function_exists('admin_reservation_status_label')) {
    function admin_reservation_status_label(string $status): string
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

if (!function_exists('admin_reservation_status_class')) {
    function admin_reservation_status_class(string $status): string
    {
        return in_array($status, ['en_attente', 'confirmee', 'refusee', 'annulee'], true) ? $status : 'en_attente';
    }
}

if (!function_exists('admin_reservation_status_icon')) {
    function admin_reservation_status_icon(string $status): string
    {
        return match ($status) {
            'confirmee' => 'success',
            'refusee' => 'refused',
            'annulee' => 'cancelled',
            default => 'pending',
        };
    }
}

if (!function_exists('admin_role_label_trace')) {
    function admin_role_label_trace(string $role): string
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

if (!function_exists('admin_trip_status_label_trace')) {
    function admin_trip_status_label_trace(string $status): string
    {
        return match ($status) {
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            default => 'Publié',
        };
    }
}

if (!function_exists('admin_trip_status_icon_trace')) {
    function admin_trip_status_icon_trace(string $status): string
    {
        return match ($status) {
            'termine' => 'success',
            'annule' => 'cancelled',
            default => 'pending',
        };
    }
}
?>

<div class="container admin-page traceability-page">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('traceability', 'icon icon-md') ?>
            <span>Traçabilité globale</span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour au dashboard</span>
        </a>
    </div>

    <section class="traceability-grid traceability-dashboard-grid kpi-grid">
        <article class="trace-card kpi-card metric-card">
            <div class="trace-card-title">Total trajets</div>
            <div class="trace-card-value"><?= (int) ($traceStats['total_trajets'] ?? 0) ?></div>
        </article>
        <article class="trace-card kpi-card metric-card">
            <div class="trace-card-title">Total réservations</div>
            <div class="trace-card-value"><?= (int) ($traceStats['total_reservations'] ?? 0) ?></div>
        </article>
        <article class="trace-card kpi-card metric-card">
            <div class="trace-card-title">Réservations confirmées</div>
            <div class="trace-card-value"><?= (int) ($traceStats['reservations_confirmees'] ?? 0) ?></div>
        </article>
        <article class="trace-card kpi-card metric-card">
            <div class="trace-card-title">Réservations en attente</div>
            <div class="trace-card-value"><?= (int) ($traceStats['reservations_en_attente'] ?? 0) ?></div>
        </article>
        <article class="trace-card kpi-card metric-card">
            <div class="trace-card-title">Recette estimée confirmée</div>
            <div class="trace-card-value money-value"><?= number_format((float) ($traceStats['recette_confirmee_estimee'] ?? 0), 2) ?> TND</div>
        </article>
        <article class="trace-card kpi-card metric-card">
            <div class="trace-card-title">Recette estimée totale (demandes actives)</div>
            <div class="trace-card-value money-value"><?= number_format((float) ($traceStats['recette_estimee_active'] ?? 0), 2) ?> TND</div>
        </article>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Filtres</h2>
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="search-form-card filter-card">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="traceability">
            <div class="search-grid">
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="">Tous</option>
                        <?php foreach (['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'refusee' => 'Refusée', 'annulee' => 'Annulée'] as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= (($_GET['status'] ?? '') === $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="role">Rôle passager</label>
                    <select id="role" name="role">
                        <option value="">Tous</option>
                        <?php foreach (['etudiant' => 'Étudiant', 'professeur' => 'Professeur'] as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= (($_GET['role'] ?? '') === $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email (passager/conducteur)</label>
                    <input id="email" type="text" name="email" value="<?= htmlspecialchars((string) ($_GET['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="exemple@domaine.tn">
                </div>

                <div class="form-group">
                    <label for="date_from">Date départ</label>
                    <input id="date_from" type="date" name="date_from" value="<?= htmlspecialchars((string) ($_GET['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="search-grid search-grid-secondary">
                <div class="form-group">
                    <label for="date_to">Date fin</label>
                    <input id="date_to" type="date" name="date_to" value="<?= htmlspecialchars((string) ($_GET['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <?= ui_icon('search', 'icon icon-sm') ?>
                        <span>Filtrer</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    <section class="detail-section">
        <h2 class="section-subtitle">Traçabilité des réservations</h2>
        <div class="table-wrapper data-card">
            <table class="data-table table-modern">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Passager</th>
                    <th>Conducteur</th>
                    <th>Trajet</th>
                    <th>Statut trajet</th>
                    <th>Date trajet</th>
                    <th>Réservé le</th>
                    <th>Statut</th>
                    <th>Montant estimé</th>
                    <th>Point sélectionné</th>
                    <th>Distance facturée</th>
                    <th>Prix réservation</th>
                    <th>Paiement déclaré</th>
                    <th>Horodatage statut</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($reservationRows)): ?>
                    <tr>
                        <td colspan="15" class="empty-state">Aucune réservation trouvée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservationRows as $row): ?>
                        <?php
                        $status = (string) ($row['statut'] ?? 'en_attente');
                        $statusTimestamp = match ($status) {
                            'confirmee' => $row['confirmed_at'] ?? null,
                            'refusee' => $row['refused_at'] ?? null,
                            'annulee' => $row['cancelled_at'] ?? null,
                            default => null,
                        };
                        $montant = isset($row['prix_snapshot']) && $row['prix_snapshot'] !== null
                            ? (float) $row['prix_snapshot']
                            : (float) ($row['trajet_prix'] ?? 0);
                        $reservationPrice = $row['reservation_price'] ?? $row['prix_snapshot'] ?? $row['trajet_prix'] ?? 0;
                        $reservationDistance = $row['reservation_distance_km'] ?? null;
                        $reservationType = (string) ($row['reservation_point_type'] ?? '');
                        $pointLabel = $reservationType === 'prise_en_charge'
                            ? 'Point de prise en charge'
                            : ($reservationType === 'depose' ? 'Point de dépose' : '');
                        $pointIcon = $reservationType === 'prise_en_charge' ? 'departure' : 'arrival';
                        $tripStatus = (string) ($row['statut_trajet'] ?? 'publie');
                        $paymentDeclared = ($row['payment_status'] ?? '') === 'declare_paye';
                        ?>
                        <tr>
                            <td>#<?= (int) $row['reservation_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars(trim(($row['passager_prenom'] ?? '') . ' ' . ($row['passager_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small><?= htmlspecialchars(admin_role_label_trace((string) ($row['passager_role'] ?? '')), ENT_QUOTES, 'UTF-8') ?></small><br>
                                <small><?= htmlspecialchars((string) ($row['passager_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?><?= !empty($row['passager_telephone']) ? ' | ' . htmlspecialchars((string) $row['passager_telephone'], ENT_QUOTES, 'UTF-8') : '' ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars(trim(($row['conducteur_prenom'] ?? '') . ' ' . ($row['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small><?= htmlspecialchars((string) ($row['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= htmlspecialchars((string) ($row['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($row['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="trip-status-badge trip-status-<?= htmlspecialchars($tripStatus, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ui_icon(admin_trip_status_icon_trace($tripStatus), 'icon icon-xs') ?>
                                    <span><?= htmlspecialchars(admin_trip_status_label_trace($tripStatus), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                                <?php if (!empty($row['completed_at'])): ?>
                                    <br><small>Terminé le <?= admin_format_datetime((string) $row['completed_at']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= admin_format_date((string) ($row['date_depart'] ?? '')) ?> <?= htmlspecialchars(substr((string) ($row['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= admin_format_datetime((string) ($row['reservation_created_at'] ?? '')) ?></td>
                            <td>
                                <span class="status-badge status-pill status-<?= htmlspecialchars(admin_reservation_status_class($status), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ui_icon(admin_reservation_status_icon($status), 'icon icon-xs') ?>
                                    <span><?= htmlspecialchars(admin_reservation_status_label($status), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </td>
                            <td><span class="money-value"><?= number_format($montant, 2) ?> TND</span></td>
                            <td>
                                <?php if ($pointLabel !== '' && isset($row['reservation_point_lat'], $row['reservation_point_lng'])): ?>
                                    <?= ui_icon($pointIcon, 'icon icon-xs') ?>
                                    <?= htmlspecialchars($pointLabel, ENT_QUOTES, 'UTF-8') ?><br>
                                    <small><?= number_format((float) $row['reservation_point_lat'], 5) ?>, <?= number_format((float) $row['reservation_point_lng'], 5) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($reservationDistance !== null): ?>
                                    <?= number_format((float) $reservationDistance, 2) ?> km
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="money-value"><?= number_format((float) $reservationPrice, 2) ?> TND</span></td>
                            <td>
                                <?php if ($paymentDeclared): ?>
                                    <span class="payment-status-badge payment-status-declare_paye">
                                        <?= ui_icon('price', 'icon icon-xs') ?>
                                        <span>Paiement en espèces déclaré</span>
                                    </span><br>
                                    <small>Montant déclaré: <?= number_format((float) ($row['paid_amount'] ?? $montant), 2) ?> TND</small><br>
                                    <small>Déclaré le <?= admin_format_datetime((string) ($row['paid_at'] ?? '')) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Non applicable</span>
                                <?php endif; ?>
                            </td>
                            <td><?= admin_format_datetime(is_string($statusTimestamp) ? $statusTimestamp : null) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/index.php?page=admin&action=reservationDetails&id=<?= (int) $row['reservation_id'] ?>" class="btn btn-outline btn-xs">
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

    <section class="detail-section">
        <h2 class="section-subtitle">Traçabilité des trajets</h2>
        <div class="table-wrapper data-card">
            <table class="data-table table-modern">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Conducteur</th>
                    <th>Trajet</th>
                    <th>Statut trajet</th>
                    <th>Date / heure</th>
                    <th>Distance</th>
                    <th>Durée</th>
                    <th>Prix par km</th>
                    <th>Route</th>
                    <th>Calculé le</th>
                    <th>Prix par passager</th>
                    <th>Places</th>
                    <th>Confirmées</th>
                    <th>En attente</th>
                    <th>Total déclaré</th>
                    <th>Recette confirmée estimée</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tripRows)): ?>
                    <tr>
                        <td colspan="17" class="empty-state">Aucun trajet trouvé.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tripRows as $trip): ?>
                        <?php $tripStatus = (string) ($trip['statut_trajet'] ?? 'publie'); ?>
                        <tr>
                            <td>#<?= (int) $trip['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars(trim(($trip['conducteur_prenom'] ?? '') . ' ' . ($trip['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small><?= htmlspecialchars((string) ($trip['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= htmlspecialchars((string) ($trip['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($trip['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="trip-status-badge trip-status-<?= htmlspecialchars($tripStatus, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ui_icon(admin_trip_status_icon_trace($tripStatus), 'icon icon-xs') ?>
                                    <span><?= htmlspecialchars(admin_trip_status_label_trace($tripStatus), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                                <?php if (!empty($trip['completed_at'])): ?>
                                    <br><small>Terminé le <?= admin_format_datetime((string) $trip['completed_at']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= admin_format_date((string) ($trip['date_depart'] ?? '')) ?> <?= htmlspecialchars(substr((string) ($trip['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (isset($trip['distance_km']) && $trip['distance_km'] !== null): ?>
                                    <?= number_format((float) $trip['distance_km'], 2) ?> km
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($trip['duree_minutes']) && $trip['duree_minutes'] !== null): ?>
                                    <?= (int) $trip['duree_minutes'] ?> min
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($trip['prix_par_km']) && $trip['prix_par_km'] !== null): ?>
                                    <?= number_format((float) $trip['prix_par_km'], 3) ?> TND / km
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars((string) ($trip['route_provider'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= admin_format_datetime((string) ($trip['route_calculated_at'] ?? '')) ?></td>
                            <td><span class="money-value"><?= number_format((float) ($trip['prix'] ?? 0), 2) ?> TND</span></td>
                            <td><?= (int) ($trip['places_total'] ?? 0) ?> total / <?= (int) ($trip['places_restantes'] ?? 0) ?> restantes</td>
                            <td><?= (int) ($trip['confirmed_count'] ?? 0) ?></td>
                            <td><?= (int) ($trip['pending_count'] ?? 0) ?></td>
                            <td><span class="money-value"><?= number_format((float) ($trip['declared_total'] ?? 0), 2) ?> TND</span></td>
                            <td><span class="money-value"><?= number_format((float) ($trip['estimated_confirmed_revenue'] ?? 0), 2) ?> TND</span></td>
                            <td>
                                <a href="<?= BASE_URL ?>/index.php?page=admin&action=tripDetails&id=<?= (int) $trip['id'] ?>" class="btn btn-outline btn-xs">
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

    <section class="detail-section">
        <h2 class="section-subtitle">Journal d'audit</h2>
        <div class="table-wrapper data-card">
            <table class="data-table audit-log-table">
                <thead>
                <tr>
                    <th>Date/heure</th>
                    <th>Acteur</th>
                    <th>Action</th>
                    <th>Entité</th>
                    <th>ID entité</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($auditRows)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">Aucune entrée d'audit.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($auditRows as $log): ?>
                        <?php
                        $actorName = trim((string) (($log['user_prenom'] ?? '') . ' ' . ($log['user_nom'] ?? '')));
                        $actorEmail = (string) ($log['user_email'] ?? '');
                        $decodedDetails = [];
                        if (!empty($log['details'])) {
                            $decoded = json_decode((string) $log['details'], true);
                            if (is_array($decoded)) {
                                $decodedDetails = $decoded;
                            }
                        }
                        $summaryChunks = [];
                        foreach ($decodedDetails as $k => $v) {
                            if (is_scalar($v) || $v === null) {
                                $summaryChunks[] = (string) $k . ': ' . (string) $v;
                            }
                            if (count($summaryChunks) >= 3) {
                                break;
                            }
                        }
                        $summaryText = $summaryChunks === [] ? '-' : implode(' | ', $summaryChunks);
                        ?>
                        <tr>
                            <td><?= admin_format_datetime((string) ($log['created_at'] ?? '')) ?></td>
                            <td>
                                <?php if ($actorName !== ''): ?>
                                    <strong><?= htmlspecialchars($actorName, ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <?php endif; ?>
                                <small><?= htmlspecialchars($actorEmail !== '' ? $actorEmail : 'Système', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><code><?= htmlspecialchars((string) ($log['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars((string) ($log['entity_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= isset($log['entity_id']) ? (int) $log['entity_id'] : '-' ?></td>
                            <td><?= htmlspecialchars($summaryText, ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>

