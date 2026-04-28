<?php
$pageTitle = 'Administration - Détail trajet';
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
?>

<div class="container">
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

    <div class="admin-detail-grid">
        <section class="admin-detail-card">
            <h2>Informations trajet</h2>
            <p><strong>Trajet:</strong> <?= htmlspecialchars((string) ($trip['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($trip['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Date:</strong> <?= admin_detail_date((string) ($trip['date_depart'] ?? '')) ?></p>
            <p><strong>Heure:</strong> <?= htmlspecialchars(substr((string) ($trip['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Prix par passager:</strong> <span class="money-value"><?= number_format((float) ($trip['prix'] ?? 0), 2) ?> TND</span></p>
            <p><strong>Places:</strong> <?= (int) ($trip['places_total'] ?? 0) ?> total / <?= (int) ($trip['places_restantes'] ?? 0) ?> restantes</p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars((string) ($trip['description'] ?? '-'), ENT_QUOTES, 'UTF-8')) ?></p>
            <p><strong>Créé le:</strong> <?= admin_detail_datetime((string) ($trip['created_at'] ?? '')) ?></p>
            <p><strong>Mis à jour le:</strong> <?= admin_detail_datetime((string) ($trip['updated_at'] ?? '')) ?></p>
        </section>

        <section class="admin-detail-card">
            <h2>Conducteur</h2>
            <p><strong>Nom:</strong> <?= htmlspecialchars(trim(($trip['conducteur_prenom'] ?? '') . ' ' . ($trip['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($trip['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars((string) ($trip['conducteur_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Rôle:</strong> <?= htmlspecialchars(admin_role_label_detail((string) ($trip['conducteur_role'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Statut compte:</strong> <?= htmlspecialchars((string) ($trip['conducteur_statut_compte'] ?? 'actif'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="admin-detail-card">
            <h2>Résumé financier estimé</h2>
            <p><strong>Total réservations:</strong> <?= (int) ($tripSummary['total_reservations'] ?? 0) ?></p>
            <p><strong>Confirmées:</strong> <?= (int) ($tripSummary['confirmed_count'] ?? 0) ?></p>
            <p><strong>En attente:</strong> <?= (int) ($tripSummary['pending_count'] ?? 0) ?></p>
            <p><strong>Refusées:</strong> <?= (int) ($tripSummary['refused_count'] ?? 0) ?></p>
            <p><strong>Annulées:</strong> <?= (int) ($tripSummary['cancelled_count'] ?? 0) ?></p>
            <p><strong>Total confirmé estimé:</strong> <span class="money-value"><?= number_format((float) ($tripSummary['estimated_confirmed_revenue'] ?? 0), 2) ?> TND</span></p>
        </section>
    </div>

    <section class="detail-section">
        <h2 class="section-subtitle">Réservations liées à ce trajet</h2>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Passager</th>
                    <th>Email / téléphone</th>
                    <th>Statut</th>
                    <th>Réservé le</th>
                    <th>Horodatage statut</th>
                    <th>Prix snapshot (réservation)</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tripReservationRows)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">Aucune réservation pour ce trajet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tripReservationRows as $row): ?>
                        <?php
                        $status = (string) ($row['statut'] ?? 'en_attente');
                        $statusTimestamp = match ($status) {
                            'confirmee' => $row['confirmed_at'] ?? null,
                            'refusee' => $row['refused_at'] ?? null,
                            'annulee' => $row['cancelled_at'] ?? null,
                            default => null,
                        };
                        $priceSnapshot = $row['prix_snapshot'] ?? $row['trajet_prix'] ?? 0;
                        ?>
                        <tr>
                            <td>#<?= (int) ($row['reservation_id'] ?? 0) ?></td>
                            <td>
                                <strong><?= htmlspecialchars(trim(($row['passager_prenom'] ?? '') . ' ' . ($row['passager_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small><?= htmlspecialchars(admin_role_label_detail((string) ($row['passager_role'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars((string) ($row['passager_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?><br>
                                <small><?= htmlspecialchars((string) ($row['passager_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <span class="status-badge status-<?= htmlspecialchars(admin_res_status_class($status), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ui_icon(admin_res_status_icon($status), 'icon icon-xs') ?>
                                    <span><?= htmlspecialchars(admin_res_status_label($status), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </td>
                            <td><?= admin_detail_datetime((string) ($row['reservation_created_at'] ?? '')) ?></td>
                            <td><?= admin_detail_datetime(is_string($statusTimestamp) ? $statusTimestamp : null) ?></td>
                            <td><span class="money-value"><?= number_format((float) $priceSnapshot, 2) ?> TND</span></td>
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
