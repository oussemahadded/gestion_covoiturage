<?php
/**
 * index.php — Point d'entrée unique (Front Controller)
 * Gestion de Covoiturage
 */

// ── 1. Session sécurisée ─────────────────────────────────────────────────────
session_start([
    'cookie_httponly' => true,   // Inaccessible au JS → protection XSS
    'cookie_secure'   => false,  // Mettre true en HTTPS
    'use_strict_mode' => true,
]);

// ── 2. Constantes globales ───────────────────────────────────────────────────
define('ROOT_PATH', __DIR__);
define('BASE_URL',  'http://localhost/pfa/gestionCov');

// ── 3. Auto-chargement simplifié ─────────────────────────────────────────────
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/JWT.php';

// ── 3.5. Middleware JWT ──────────────────────────────────────────────────────
if (isset($_COOKIE['jwt_token'])) {
    $decoded = JWT::decode($_COOKIE['jwt_token']);
    if ($decoded && isset($decoded['user'])) {
        $_SESSION['user'] = $decoded['user'];
    } else {
        // Token invalide ou expiré
        setcookie('jwt_token', '', time() - 3600, '/');
        unset($_SESSION['user']);
    }
} else {
    unset($_SESSION['user']);
}

// Chargement automatique des modèles
foreach (glob(ROOT_PATH . '/models/*.php') as $model) {
    require_once $model;
}

// ── 4. Routage ───────────────────────────────────────────────────────────────
$router = new Router();
$router->dispatch();
