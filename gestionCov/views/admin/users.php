<?php
$pageTitle = 'Administration - Utilisateurs';

if (!function_exists('admin_role_label')) {
    function admin_role_label(string $role): string
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

if (!function_exists('admin_account_status_label')) {
    function admin_account_status_label(string $status): string
    {
        return match ($status) {
            'actif' => 'Actif',
            'en_attente' => 'En attente',
            'refuse' => 'Refusé',
            'desactive' => 'Désactivé',
            default => 'Actif',
        };
    }
}

if (!function_exists('admin_role_badge_class')) {
    function admin_role_badge_class(string $role): string
    {
        return in_array($role, ['admin', 'conducteur', 'etudiant', 'professeur'], true)
            ? $role
            : 'etudiant';
    }
}

if (!function_exists('admin_normalize_account_status')) {
    function admin_normalize_account_status(mixed $status): string
    {
        $value = is_string($status) ? trim($status) : '';
        return in_array($value, ['actif', 'en_attente', 'refuse', 'desactive'], true) ? $value : 'actif';
    }
}

require_once ROOT_PATH . '/views/layouts/header.php';
?>

<div class="container">
    <div class="page-header-row">
        <h1 class="page-title">
            <?= ui_icon('users', 'icon icon-md') ?>
            <span>Gestion des utilisateurs</span>
        </h1>
        <a href="<?= BASE_URL ?>/index.php?page=admin" class="btn btn-outline">
            <?= ui_icon('arrow-left', 'icon icon-sm') ?>
            <span>Retour au dashboard</span>
        </a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Rôle</th>
                <th>Statut compte</th>
                <th>Inscrit le</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <?php
                $userId = (int) ($u['id'] ?? 0);
                $userRole = (string) ($u['role'] ?? '');
                $status = admin_normalize_account_status($u['statut_compte'] ?? null);
                $isSelf = $userId === (int) ($_SESSION['user']['id'] ?? 0);

                $statusIcon = match ($status) {
                    'actif' => 'success',
                    'en_attente' => 'pending',
                    'refuse' => 'refused',
                    'desactive' => 'cancelled',
                    default => 'warning',
                };
                ?>
                <tr>
                    <td><?= $userId ?></td>
                    <td><?= htmlspecialchars((string) (($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($u['telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="badge-role badge-<?= htmlspecialchars(admin_role_badge_class($userRole), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(admin_role_label($userRole), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <span class="account-status account-status-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                            <?= ui_icon($statusIcon, 'icon icon-xs') ?>
                            <span><?= htmlspecialchars(admin_account_status_label($status), ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    </td>
                    <td><?= !empty($u['created_at']) ? date('d/m/Y', strtotime((string) $u['created_at'])) : '-' ?></td>
                    <td class="table-actions">
                        <?php if ($isSelf): ?>
                            <span class="text-muted">Vous</span>
                        <?php else: ?>
                            <?php if ($userRole === 'conducteur' && $status === 'en_attente'): ?>
                                <form action="<?= BASE_URL ?>/index.php?page=admin&action=activateUser" method="POST" class="inline-form">
                                    <input type="hidden" name="id" value="<?= $userId ?>">
                                    <button type="submit" class="btn btn-success btn-xs">
                                        <?= ui_icon('check', 'icon icon-xs') ?>
                                        <span>Activer</span>
                                    </button>
                                </form>
                                <form action="<?= BASE_URL ?>/index.php?page=admin&action=refuseUser" method="POST" class="inline-form">
                                    <input type="hidden" name="id" value="<?= $userId ?>">
                                    <button type="submit" class="btn btn-warning btn-xs" onclick="return confirm('Refuser ce compte conducteur ?')">
                                        <?= ui_icon('refused', 'icon icon-xs') ?>
                                        <span>Refuser</span>
                                    </button>
                                </form>
                            <?php elseif ($status === 'actif'): ?>
                                <form action="<?= BASE_URL ?>/index.php?page=admin&action=deactivateUser" method="POST" class="inline-form">
                                    <input type="hidden" name="id" value="<?= $userId ?>">
                                    <button type="submit" class="btn btn-warning btn-xs" onclick="return confirm('Désactiver ce compte ?')">
                                        <?= ui_icon('cancelled', 'icon icon-xs') ?>
                                        <span>Désactiver</span>
                                    </button>
                                </form>
                            <?php elseif ($status === 'desactive' || $status === 'refuse'): ?>
                                <form action="<?= BASE_URL ?>/index.php?page=admin&action=activateUser" method="POST" class="inline-form">
                                    <input type="hidden" name="id" value="<?= $userId ?>">
                                    <button type="submit" class="btn btn-success btn-xs">
                                        <?= ui_icon('check', 'icon icon-xs') ?>
                                        <span><?= $status === 'refuse' ? 'Réactiver' : 'Activer' ?></span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
