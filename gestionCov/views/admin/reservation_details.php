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

$status = (string) ($reservation['statut'] ?? 'en_attente');
$statusIcon = match ($status) {
    'confirmee' => 'success',
    'refusee' => 'refused',
    'annulee' => 'cancelled',
    default => 'pending',
};
?>

<div class="container">
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

    <section class="admin-detail-card">
        <h2>Statut</h2>
        <p>
            <span class="status-badge status-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                <?= ui_icon($statusIcon, 'icon icon-xs') ?>
                <span><?= htmlspecialchars(admin_rd_status_label($status), ENT_QUOTES, 'UTF-8') ?></span>
            </span>
        </p>
        <p><strong>Montant estimé:</strong> <span class="money-value"><?= number_format((float) ($reservation['montant_estime'] ?? 0), 2) ?> TND</span></p>
        <p><strong>Prix snapshot (réservation):</strong> <span class="money-value"><?= number_format((float) (($reservation['prix_snapshot'] ?? $reservation['trajet_prix'] ?? 0)), 2) ?> TND</span></p>
    </section>

    <div class="admin-detail-grid">
        <section class="admin-detail-card">
            <h2>Passager</h2>
            <p><strong>Nom:</strong> <?= htmlspecialchars(trim(($reservation['passager_prenom'] ?? '') . ' ' . ($reservation['passager_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($reservation['passager_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars((string) ($reservation['passager_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Rôle:</strong> <?= htmlspecialchars(admin_rd_role_label((string) ($reservation['passager_role'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Statut compte:</strong> <?= htmlspecialchars((string) ($reservation['passager_statut_compte'] ?? 'actif'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="admin-detail-card">
            <h2>Conducteur</h2>
            <p><strong>Nom:</strong> <?= htmlspecialchars(trim(($reservation['conducteur_prenom'] ?? '') . ' ' . ($reservation['conducteur_nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($reservation['conducteur_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars((string) ($reservation['conducteur_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Statut compte:</strong> <?= htmlspecialchars((string) ($reservation['conducteur_statut_compte'] ?? 'actif'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="admin-detail-card">
            <h2>Trajet</h2>
            <p><strong>Route:</strong> <?= htmlspecialchars((string) ($reservation['ville_depart'] ?? ''), ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) ($reservation['ville_arrivee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Date:</strong> <?= admin_rd_date((string) ($reservation['date_depart'] ?? '')) ?></p>
            <p><strong>Heure:</strong> <?= htmlspecialchars(substr((string) ($reservation['heure_depart'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Prix par passager (trajet):</strong> <span class="money-value"><?= number_format((float) ($reservation['trajet_prix'] ?? 0), 2) ?> TND</span></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars((string) ($reservation['trajet_description'] ?? '-'), ENT_QUOTES, 'UTF-8')) ?></p>
        </section>
    </div>

    <section class="admin-detail-card">
        <h2>Historique / timeline</h2>
        <ul class="timeline">
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
                <strong>Dernière mise à jour:</strong>
                <span><?= admin_rd_datetime((string) ($reservation['updated_at'] ?? '')) ?></span>
            </li>
        </ul>
    </section>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
