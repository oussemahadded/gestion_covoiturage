<?php
/**
 * views/layouts/header.php
 * En-tête commun - CHAYA3NI
 */

if (!function_exists('ui_icon')) {
    function ui_icon(string $name, string $class = 'icon icon-md', string $title = ''): string
    {
        $icons = [
            'car' => '<path d="M3 13l2-6a2 2 0 012-1h10a2 2 0 012 1l2 6"/><path d="M5 13h14v5a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H8v1a1 1 0 01-1 1H6a1 1 0 01-1-1v-5z"/><circle cx="7.5" cy="14.5" r="1"/><circle cx="16.5" cy="14.5" r="1"/>',
            'route' => '<path d="M5 19a2 2 0 100-4 2 2 0 000 4z"/><path d="M19 9a2 2 0 100-4 2 2 0 000 4z"/><path d="M7 17h2a4 4 0 004-4v-2a4 4 0 014-4h0"/>',
            'distance' => '<path d="M4 12h16"/><path d="M8 6l-4 6 4 6"/><path d="M16 6l4 6-4 6"/>',
            'departure' => '<path d="M12 3v18"/><path d="M7 8l5-5 5 5"/>',
            'arrival' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/>',
            'seats' => '<path d="M7 10h10v7H7z"/><path d="M5 17h14M9 10V7a3 3 0 016 0v3"/>',
            'price' => '<circle cx="12" cy="12" r="9"/><path d="M9 9.5c0-1.2 1.1-2.1 2.5-2.1s2.5.9 2.5 2.1c0 2.6-5 1.7-5 4.1 0 1.2 1.1 2.1 2.5 2.1s2.5-.9 2.5-2.1M12 5v14"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0116 0"/>',
            'messages' => '<path d="M4 5h16a1 1 0 011 1v9a1 1 0 01-1 1H8l-4 3v-3H4a1 1 0 01-1-1V6a1 1 0 011-1z"/>',
            'reservation' => '<path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 010 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 010-4V8z"/><path d="M9 8v10M15 8v10"/>',
            'admin' => '<path d="M12 3l7 3v6c0 4.4-3 7.8-7 9-4-1.2-7-4.6-7-9V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
            'success' => '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
            'pending' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'warning' => '<path d="M12 3l9 16H3l9-16z"/><path d="M12 9v4M12 16h.01"/>',
            'refused' => '<circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>',
            'cancelled' => '<circle cx="12" cy="12" r="9"/><path d="M8 12h8"/>',
            'edit' => '<path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/>',
            'delete' => '<path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 13a1 1 0 001 1h8a1 1 0 001-1l1-13"/><path d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>',
            'view' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'send' => '<path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4 20-7z"/>',
            'phone' => '<path d="M5 4h3l2 5-2 2a15 15 0 006 6l2-2 5 2v3a2 2 0 01-2 2 17 17 0 01-17-17 2 2 0 012-2z"/>',
            'star' => '<path d="M12 3l2.8 5.7 6.2.9-4.5 4.3 1.1 6.1L12 17l-5.6 3 1.1-6.1L3 9.6l6.2-.9L12 3z"/>',
            'login' => '<path d="M10 17l-5-5 5-5"/><path d="M5 12h10"/><path d="M14 4h5v16h-5"/>',
            'register' => '<path d="M12 5v14M5 12h14"/><circle cx="19" cy="5" r="2"/>',
            'dashboard' => '<rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="10" width="8" height="11" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/>',
            'traceability' => '<path d="M9 3h6"/><path d="M10 3a2 2 0 00-2 2v1H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V8a2 2 0 00-2-2h-1V5a2 2 0 00-2-2"/><path d="M9 13l2 2 4-4"/><path d="M9 9h6"/>',
            'users' => '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20a6 6 0 0112 0"/><path d="M14 20a4.5 4.5 0 018.5-2"/>',
            'student' => '<path d="M3 9l9-5 9 5-9 5-9-5z"/><path d="M7 11v4c0 2.2 2.2 4 5 4s5-1.8 5-4v-4"/><path d="M21 9v5"/>',
            'teacher' => '<path d="M4 6h11a2 2 0 012 2v10H6a2 2 0 01-2-2V6z"/><path d="M17 10h3v8h-3"/><path d="M8 10h6M8 13h6"/>',
            'arrow-right' => '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
            'arrow-left' => '<path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/>',
            'logout' => '<path d="M10 17l5-5-5-5"/><path d="M15 12H5"/><path d="M14 4h5v16h-5"/>',
            'check' => '<path d="M5 13l4 4L19 7"/>',
            'x' => '<path d="M6 6l12 12M18 6L6 18"/>',
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 118 0v3"/>',
            'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
        ];

        if (!isset($icons[$name])) {
            return '';
        }

        $safeClass = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        $titleMarkup = $title !== '' ? '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>' : '';

        return '<svg class="' . $safeClass . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $titleMarkup . $icons[$name] . '</svg>';
    }
}

$currentUser = $_SESSION['user'] ?? null;
$role = $currentUser['role'] ?? 'guest';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$unreadCount = 0;
if ($currentUser) {
    $msgModel = new Message();
    $unreadCount = $msgModel->countUnread((int) $currentUser['id']);
}

$pendingDemandCount = 0;
if ($currentUser && $role === 'conducteur') {
    $reservationModel = new Reservation();
    $pendingDemandCount = $reservationModel->countPendingForConducteur((int) $currentUser['id']);
}

$isPassenger = in_array($role, ['etudiant', 'professeur'], true);
$isConducteur = $role === 'conducteur';
$currentPage = (string) ($_GET['page'] ?? 'home');
$currentAction = (string) ($_GET['action'] ?? 'index');

$isTrajetPage = $currentPage === 'trajet';
$isMessagePage = $currentPage === 'message';
$isReservationPage = $currentPage === 'reservation';
$isAdminPage = $currentPage === 'admin';

$navClass = static function (bool $active = false, bool $cta = false): string {
    $className = 'nav-link';
    if ($cta) {
        $className .= ' nav-cta';
    }
    if ($active) {
        $className .= ' is-active';
    }
    return $className;
};

$roleLabel = match ($role) {
    'admin' => 'Administration',
    'conducteur' => 'Conducteur',
    'etudiant' => 'Étudiant',
    'professeur' => 'Professeur',
    default => ucfirst($role),
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CHAYA3NI - Plateforme de covoiturage pour la communauté Sesame.">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' - ' : '' ?>CHAYA3NI | Covoiturage</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <?php if (!empty($includeMap)): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">
            <?= ui_icon('car', 'icon icon-md brand-mark') ?>
            <span class="brand-name">CHAYA3NI</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false" aria-controls="navLinks">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li class="nav-item">
                <a href="<?= BASE_URL ?>/index.php?page=trajet" class="<?= $navClass($isTrajetPage && in_array($currentAction, ['index', 'show'], true)) ?>">
                    <?= ui_icon('route', 'icon icon-sm nav-link-icon') ?>
                    <span>Trajets</span>
                </a>
            </li>

            <?php if ($currentUser): ?>
                <?php if ($isConducteur): ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="<?= $navClass($isTrajetPage && $currentAction === 'myTrajets') ?>">
                            <?= ui_icon('car', 'icon icon-sm nav-link-icon') ?>
                            <span>Mes trajets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=create" class="<?= $navClass($isTrajetPage && in_array($currentAction, ['create', 'edit'], true), true) ?>">
                            <?= ui_icon('plus', 'icon icon-sm nav-link-icon') ?>
                            <span>Proposer</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php?page=reservation&action=driverRequests" class="<?= $navClass($isReservationPage && $currentAction === 'driverRequests') ?>">
                            <?= ui_icon('reservation', 'icon icon-sm nav-link-icon') ?>
                            <span>Demandes</span>
                            <?php if ($pendingDemandCount > 0): ?>
                                <span class="badge count-badge"><?= (int) $pendingDemandCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php elseif ($isPassenger): ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php?page=reservation&action=myReservations" class="<?= $navClass($isReservationPage && $currentAction === 'myReservations') ?>">
                            <?= ui_icon('reservation', 'icon icon-sm nav-link-icon') ?>
                            <span>Mes réservations</span>
                        </a>
                    </li>
                <?php elseif ($role === 'admin'): ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php?page=admin" class="<?= $navClass($isAdminPage && $currentAction === 'index') ?>">
                            <?= ui_icon('admin', 'icon icon-sm nav-link-icon') ?>
                            <span>Admin</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php?page=admin&action=traceability" class="<?= $navClass($isAdminPage && in_array($currentAction, ['traceability', 'tripDetails', 'reservationDetails'], true)) ?>">
                            <?= ui_icon('traceability', 'icon icon-sm nav-link-icon') ?>
                            <span>Traçabilité</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php?page=admin&action=finances" class="<?= $navClass($isAdminPage && $currentAction === 'finances') ?>">
                            <?= ui_icon('price', 'icon icon-sm nav-link-icon') ?>
                            <span>Finances</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($role !== 'admin'): ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/index.php?page=message" class="<?= $navClass($isMessagePage) ?> nav-msg">
                        <?= ui_icon('messages', 'icon icon-sm nav-link-icon') ?>
                        <span>Messages</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge count-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-user-menu">
                    <button class="nav-avatar" id="userMenuBtn" type="button" aria-label="Menu utilisateur" aria-expanded="false" aria-controls="userDropdown">
                        <?= strtoupper(substr(htmlspecialchars($currentUser['prenom'], ENT_QUOTES, 'UTF-8'), 0, 1)) ?>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <p class="dropdown-name"><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="dropdown-role badge-role badge-<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <a href="<?= BASE_URL ?>/index.php?page=auth&action=logout" class="dropdown-logout">
                            <?= ui_icon('logout', 'icon icon-sm') ?>
                            <span>Déconnexion</span>
                        </a>
                    </div>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/index.php?page=auth&action=login" class="<?= $navClass($currentPage === 'auth' && $currentAction === 'login') ?>">
                        <?= ui_icon('login', 'icon icon-sm nav-link-icon') ?>
                        <span>Connexion</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/index.php?page=auth&action=register" class="<?= $navClass($currentPage === 'auth' && $currentAction === 'register', true) ?>">
                        <?= ui_icon('register', 'icon icon-sm nav-link-icon') ?>
                        <span>S'inscrire</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<?php if ($flash): ?>
<div class="flash flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>" id="flashMsg">
    <div class="flash-content">
        <?php
        $flashIcon = match ($flash['type']) {
            'success' => 'success',
            'error' => 'warning',
            default => 'messages',
        };
        ?>
        <?= ui_icon($flashIcon, 'icon icon-sm') ?>
        <span><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <button class="flash-close" type="button" aria-label="Fermer">
        <?= ui_icon('x', 'icon icon-sm') ?>
    </button>
</div>
<?php endif; ?>

<main class="main-content page-shell">
