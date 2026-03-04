<?php
/**
 * views/layouts/header.php
 * En-tête commun — CHAYA3NI, Plateforme de Covoiturage en Tunisie
 */
$currentUser  = $_SESSION['user'] ?? null;
$role         = $currentUser['role'] ?? 'guest';
$flash        = $_SESSION['flash']   ?? null;
unset($_SESSION['flash']);

// Compte les messages non lus si connecté
$unreadCount = 0;
if ($currentUser) {
    $msgModel    = new Message();
    $unreadCount = $msgModel->countUnread($currentUser['id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CHAYA3NI — La plateforme de covoiturage en Tunisie. Trouvez ou proposez un trajet entre Tunis, Sfax, Sousse et partout en Tunisie.">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>CHAYA3NI | Covoiturage en Tunisie</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- ── Navbar ───────────────────────────────────────────────────────────── -->
<nav class="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">
            
            <span class="brand-name">CHAYA3NI</span>
                
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li><a href="<?= BASE_URL ?>/index.php?page=trajet" class="nav-link">🗺 Trajets</a></li>

            <?php if ($currentUser): ?>
                <?php if ($role === 'conducteur'): ?>
                    <li><a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="nav-link">📋 Mes trajets</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=trajet&action=create" class="nav-link nav-cta">+ Proposer</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=reservation&action=driverRequests" class="nav-link">📨 Demandes</a></li>
                <?php elseif ($role === 'passager'): ?>
                    <li><a href="<?= BASE_URL ?>/index.php?page=reservation&action=myReservations" class="nav-link">🎫 Réservations</a></li>
                <?php elseif ($role === 'admin'): ?>
                    <li><a href="<?= BASE_URL ?>/index.php?page=admin" class="nav-link">⚙️ Admin</a></li>
                <?php endif; ?>

                <li>
                    <a href="<?= BASE_URL ?>/index.php?page=message" class="nav-link nav-msg">
                        💬 Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-user-menu">
                    <button class="nav-avatar" id="userMenuBtn">
                        <?= strtoupper(substr(htmlspecialchars($currentUser['prenom']), 0, 1)) ?>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <p class="dropdown-name"><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></p>
                        <p class="dropdown-role badge-role badge-<?= $role ?>"><?= ucfirst($role) ?></p>
                        <a href="<?= BASE_URL ?>/index.php?page=auth&action=logout" class="dropdown-logout">🔓 Déconnexion</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/index.php?page=auth&action=login"    class="nav-link">Connexion</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?page=auth&action=register" class="nav-link nav-cta">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- ── Flash message ─────────────────────────────────────────────────────── -->
<?php if ($flash): ?>
<div class="flash flash-<?= htmlspecialchars($flash['type']) ?>" id="flashMsg">
    <?= htmlspecialchars($flash['msg']) ?>
    <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
</div>
<?php endif; ?>

<main class="main-content">
