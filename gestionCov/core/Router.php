<?php
/**
 * core/Router.php
 * Routeur frontal simplifié — MVC
 *
 * Principe : index.php?page=trajet&action=create
 *   - page   → correspond au contrôleur  (TrajetController)
 *   - action → correspond à la méthode   (create)
 */

class Router
{
    /** Map page → classe contrôleur */
    private array $routes = [
        'home'        => 'HomeController',
        'auth'        => 'AuthController',
        'trajet'      => 'TrajetController',
        'reservation' => 'ReservationController',
        'admin'       => 'AdminController',
        'avis'        => 'AvisController',
        'message'     => 'MessageController',
    ];

    public function dispatch(): void
    {
        $page   = $_GET['page']   ?? 'home';
        $action = $_GET['action'] ?? 'index';

        // Sécurité : accepter uniquement des caractères alphanumériques
        $page   = preg_replace('/[^a-zA-Z0-9_]/', '', $page);
        $action = preg_replace('/[^a-zA-Z0-9_]/', '', $action);

        if (!array_key_exists($page, $this->routes)) {
            $this->notFound();
            return;
        }

        $controllerClass = $this->routes[$page];
        $controllerFile  = __DIR__ . '/../controllers/' . $controllerClass . '.php';

        if (!file_exists($controllerFile)) {
            $this->notFound();
            return;
        }

        require_once $controllerFile;

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        $controller->$action();
    }

    private function notFound(): void
    {
        http_response_code(404);
        require_once __DIR__ . '/../views/layouts/404.php';
    }
}
