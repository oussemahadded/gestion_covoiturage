<?php
/**
 * controllers/AuthController.php
 * Authentification : register, login, logout
 */

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Redirige vers une URL */
    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /** Ajoute un message flash en session */
    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    /** Redirige si déjà connecté */
    private function requireGuest(): void
    {
        if (isset($_SESSION['user'])) {
            $this->redirect(BASE_URL . '/index.php?page=home');
        }
    }

    // ── Inscription ───────────────────────────────────────────────────────────

    public function register(): void
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom       = trim(htmlspecialchars($_POST['nom']       ?? '', ENT_QUOTES, 'UTF-8'));
            $prenom    = trim(htmlspecialchars($_POST['prenom']    ?? '', ENT_QUOTES, 'UTF-8'));
            $email     = trim($_POST['email']     ?? '');
            $mdp       = $_POST['mot_de_passe']   ?? '';
            $mdpConf   = $_POST['mdp_confirm']    ?? '';
            $telephone = trim($_POST['telephone'] ?? '');
            $role      = $_POST['role']            ?? 'passager';

            $errors = [];

            if (empty($nom))    $errors[] = 'Le nom est obligatoire.';
            if (empty($prenom)) $errors[] = 'Le prénom est obligatoire.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
            if (strlen($mdp) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            if ($mdp !== $mdpConf)  $errors[] = 'Les mots de passe ne correspondent pas.';
            if (!in_array($role, ['conducteur', 'passager'], true)) $role = 'passager';

            // Validation du numéro tunisien : exactement 8 chiffres, sans indicatif
            if (!empty($telephone) && !preg_match('/^[0-9]{8}$/', $telephone)) {
                $errors[] = 'Numéro invalide. Entrez 8 chiffres.';
            }

            if (empty($errors) && $this->userModel->emailExists($email)) {
                $errors[] = 'Cet email est déjà utilisé.';
            }

            if (empty($errors)) {
                $this->userModel->create($nom, $prenom, $email, $mdp, $telephone, $role);
                $this->flash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
                $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
            }

            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data']   = compact('nom', 'prenom', 'email', 'telephone', 'role');
        }

        require_once ROOT_PATH . '/views/auth/register.php';
    }

    // ── Connexion ─────────────────────────────────────────────────────────────

    public function login(): void
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']       ?? '');
            $mdp   = $_POST['mot_de_passe']     ?? '';

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                // Régénérer l'ID de session pour prévenir la fixation de session
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'     => $user['id'],
                    'nom'    => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                ];

                $this->flash('success', 'Bienvenue, ' . htmlspecialchars($user['prenom'], ENT_QUOTES, 'UTF-8') . ' !');

                // Redirection selon le rôle
                match ($user['role']) {
                    'admin'      => $this->redirect(BASE_URL . '/index.php?page=admin'),
                    'conducteur' => $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets'),
                    default      => $this->redirect(BASE_URL . '/index.php?page=trajet'),
                };
            }

            $this->flash('error', 'Email ou mot de passe incorrect.');
        }

        require_once ROOT_PATH . '/views/auth/login.php';
    }

    // ── Déconnexion ───────────────────────────────────────────────────────────

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
    }
}
