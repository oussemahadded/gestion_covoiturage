<?php
$pageTitle = 'Administration - Détail réservation';
require_once ROOT_PATH . '/views/layouts/header.php';

if (!function_exists('admin_rd_datetime')) {
    function admin_rd_datetime(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y H:i', $ts) : '-';
    }
}

if (!function_exists('admin_rd_date')) {
    function admin_rd_date(?string $value): string
    {
        if (!$value) {
            return '-';
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y', $ts) : '-';
    }
}

if (!function_exists('admin_rd_status_label')) {
    function admin_rd_status_label(string $status): string
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

if (!function_exists('admin_rd_role_label')) {
    function admin_rd_role_label(string $role): string
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

if (!function_exists('admin_rd_payment_label')) {
    function admin_rd_payment_label(string $status): string
    {
        return $status === 'declare_paye' ? 'Paiement en espèces déclaré' : 'Non applicable';
    }
}

$status = (string) ($reservation['statut'] ?? 'en_attente');
$statusIcon = match ($status) {
    'confirmee' => 'success',
    'refusee' => 'refused',
    'annulee' => 'cancelled',
    default => 'pending',
};
$paymentStatus = (string) ($reservation['payment_status'] ?? 'non_applicable');
$reservationType = (string) ($reservation['reservation_point_type'] ?? '');
$pointLabel = $reservationType === 'prise_en_charge'
    ? 'Point de prise en charge'
    : ($reservationType === 'depose' ? 'Point de dépose' : '');
$reservationPrice = $reservation['reservation_price'] ?? $reservation['prix_snapshot'] ?? $reservation['trajet_prix'] ?? 0;
$reservationDistance = $reservation['reservation_distance_km'] ?? null;
$reservationDuration = $reservation['reservation_duree_minutes'] ?? null;
?>

<div class="container admin-page">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('reservation', 'icon icon-md') ?>
            <span>Détail réservation #<?= (int) ($reservation['id'] ?? 0) ?></span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin&action=traceability" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour traçabilité</span>
        </a>
    </div>

    <section class="admin-detail-card detail-card app-card">
        <h2>Statut</h2>
        <p>
            <span class="status-badge status-pill status-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                <?= ui_icon($statusIcon, 'icon icon-xs') ?>
                <span><?= htmlspecialchars(admin_rd_status_label($status), ENT_QUOTES, 'UTF-8') ?></span>
            </span>
        </p>
        <p><strong>Montant estimé:</strong> <span class="money-value"><?= number_format((float) ($reservation['montant_estime'] ?? 0), 2) ?> TND</span></p>
        <p><strong>Prix snapshot (réservation):</strong> <span class="money-value"><?= number_format((float) (($reservation['prix_snapshot'] ?? $reservation['trajet_prix'] ?? 0)), 2) ?> TND</span></p>
        <p>
            <strong>Statut paiement:</strong>
            <?php if ($paymentStatus === 'declare_paye'): ?>
                <span class="payment-status-badge payment-status-declare_paye">
                    <?= ui_icon('price', 'icon icon-xs') ?>
                    <span><?= htmlspecialchars(admin_rd_payment_label($paymentStatus), ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            <?php else: ?>
                <span class="text-muted"><?= htmlspecialchars(admin_rd_payment_label($paymentStatus), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </p>
        <p><strong>Code statut paiement:</strong> <?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($paymentStatus === 'declare_paye'): ?>
            <p><strong>Montant déclaré:</strong> <span class="money-value"><?= number_format((float) ($reservation['paid_amount'] ?? $reservation['montant_estime'] ?? 0), 2) ?> TND</span></p>
            <p><strong>Déclaré le:</strong> <?= admin_rd_datetime((string) ($reservation['paid_at'] ?? '')) ?></p>
        <?php endif; ?>
    </section>

    <section class="admin-detail-card detail-card app-card">
        <h2>Point de réservation</h2>
        <?php if ($pointLabel !== '' && isset($reservation['reservation_point_lat'], $reservation['reservation_point_lng'])): ?>
            <p><strong>Type de point:</strong> <?= htmlspecialchars($pointLabel, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Coordonnées:</strong> <?= number_format((float) $reservation['reservation_point_lat'], 5) ?>, <?= number_format((float) $reservation['reservation_point_lng'], 5) ?></p>
            <p><strong>Distance facturée:</strong> <?= $reservationDistance !== null ? number_format((float) $reservationDistance, 2) . ' km' : '-' ?></p>
            <p><strong>Durée estimée:</strong> <?= $reservationDuration !== null ? (int) $reservationDuration . ' min' : '-' ?></p>
            <p><strong>Prix réservation:</strong> <span class="money-value"><?= number_format((float) $reservationPrice, 2) ?> TND</span></p>
        <?php else: ?>
            <p class="text-muted">Aucune information de point sélectionné.</p>
        <?php endif; ?>
    </section>

    <div class="admin-detail-grid detail-grid">
        <section class="admin-detail-card detail-card app-card">
            <h2>Passager</h2>
            <p><strong>Nom:</strong> <?= htmlspecialchars(trim(($reservation['passager_prenom'] ?? '') . ' ' . ($reservation['passager_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($reservation['passager_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars((string) ($reservation['passager_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Rôle:</strong> <?= htmlspecialchars(admin_rd_role_label((string) ($reservation['passager_role'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Statut compte:</strong> <?= htmlspecialchars((string) ($reservation['passager_statut_compte'] ?? 'actif'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="admin-detail-card detail-card app-card">
            <h2>Conducteur</h2>
            <p><strong>Nom:</strong> <?= htmlspecialchars(trim(($reservation['conducteur_prenom'] ?? '') . ' ' . ($reservation['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($reservation['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars((string) ($reservation['conducteur_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Statut compte:</strong> <?= htmlspecialchars((string) ($reservation['conducteur_statut_compte'] ?? 'actif'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="admin-detail-card detail-card app-card">
            <h2>Trajet</h2>
            <p><strong>Route:</strong> <?= htmlspecialchars((string) ($reservation['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($reservation['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Date:</strong> <?= admin_rd_date((string) ($reservation['date_depart'] ?? '')) ?></p>
            <p><strong>Heure:</strong> <?= htmlspecialchars(substr((string) ($reservation['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Prix par passager (trajet):</strong> <span class="money-value"><?= number_format((float) ($reservation['trajet_prix'] ?? 0), 2) ?> TND</span></p>
            <p><strong>Statut trajet:</strong> <?= htmlspecialchars((string) ($reservation['statut_trajet'] ?? 'publie'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Terminé le:</strong> <?= admin_rd_datetime((string) ($reservation['completed_at'] ?? '')) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars((string) ($reservation['trajet_description'] ?? '-'), ENT_QUOTES, 'UTF-8')) ?></p>
        </section>
    </div>

    <section class="admin-detail-card detail-card app-card">
        <h2>Historique / timeline</h2>
        <ul class="timeline detail-kv-grid">
            <li>
                <strong>Demande créée:</strong>
                <span><?= admin_rd_datetime((string) ($reservation['created_at'] ?? '')) ?></span>
            </li>
            <li>
                <strong>Confirmée le:</strong>
                <span><?= admin_rd_datetime((string) ($reservation['confirmed_at'] ?? '')) ?></span>
            </li>
            <li>
                <strong>Refusée le:</strong>
                <span><?= admin_rd_datetime((string) ($reservation['refused_at'] ?? '')) ?></span>
            </li>
            <li>
                <strong>Annulée le:</strong>
                <span><?= admin_rd_datetime((string) ($reservation['cancelled_at'] ?? '')) ?></span>
            </li>
            <li>
                <strong>Déclaré payé le:</strong>
                <span><?= admin_rd_datetime((string) ($reservation['paid_at'] ?? '')) ?></span>
            </li>
            <li>
                <strong>Dernière mise à jour:</strong>
                <span><?= admin_rd_datetime((string) ($reservation['updated_at'] ?? '')) ?></span>
            </li>
        </ul>
    </section>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>

