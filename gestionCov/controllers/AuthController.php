<?php
/**
 * controllers/AuthController.php
 * Authentification : register, login, logout
 */

class AuthController
{
    private User $userModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->userModel = new User();
        $this->auditLog = new AuditLog();
    }

    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    private function requireGuest(): void
    {
        if (isset($_SESSION['user'])) {
            $this->redirect(BASE_URL . '/index.php?page=home');
        }
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isSesameEmail(string $email): bool
    {
        $normalizedEmail = strtolower(trim($email));
        return $this->isValidEmail($normalizedEmail)
            && str_ends_with($normalizedEmail, '@sesame.com.tn');
    }

    private function validateEmailForRole(string $email, string $role): bool
    {
        if ($role === 'conducteur') {
            return $this->isValidEmail($email);
        }

        return $this->isSesameEmail($email);
    }

    private function getUserAccountStatus(array $user): string
    {
        $status = (string) ($user['statut_compte'] ?? 'actif');
        return in_array($status, ['actif', 'en_attente', 'refuse', 'desactive'], true) ? $status : 'actif';
    }

    private function audit(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $details = []
    ): void {
        try {
            $ok = $this->auditLog->create($userId, $action, $entityType, $entityId, $details);
            if (!$ok) {
                error_log('[AUDIT] Failed to write log: ' . $action);
            }
        } catch (Throwable $e) {
            error_log('[AUDIT] ' . $e->getMessage());
        }
    }

    public function register(): void
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim(htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8'));
            $prenom = trim(htmlspecialchars($_POST['prenom'] ?? '', ENT_QUOTES, 'UTF-8'));
            $email = strtolower(trim($_POST['email'] ?? ''));
            $mdp = $_POST['mot_de_passe'] ?? '';
            $mdpConf = $_POST['mdp_confirm'] ?? '';
            $telephone = trim($_POST['telephone'] ?? '');
            $role = $_POST['role'] ?? 'etudiant';

            $errors = [];

            if ($nom === '') {
                $errors[] = 'Le nom est obligatoire.';
            }
            if ($prenom === '') {
                $errors[] = 'Le prénom est obligatoire.';
            }
            if (strlen($mdp) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }
            if ($mdp !== $mdpConf) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (!in_array($role, ['conducteur', 'etudiant', 'professeur'], true)) {
                $role = 'etudiant';
            }

            if (!$this->validateEmailForRole($email, $role)) {
                if ($role === 'conducteur') {
                    $errors[] = 'Veuillez entrer une adresse email valide.';
                } else {
                    $errors[] = 'Les étudiants et professeurs doivent utiliser une adresse @sesame.com.tn valide.';
                }
            }

            if ($telephone !== '' && !preg_match('/^[0-9]{8}$/', $telephone)) {
                $errors[] = 'Numéro invalide. Entrez 8 chiffres.';
            }

            if (empty($errors) && $this->userModel->emailExists($email)) {
                $errors[] = 'Cet email est déjà utilisé.';
            }

            if (empty($errors)) {
                $statutCompte = $role === 'conducteur' ? 'en_attente' : 'actif';
                $newUserId = $this->userModel->create($nom, $prenom, $email, $mdp, $telephone, $role, $statutCompte);

                if ($newUserId !== false) {
                    $this->audit(
                        (int) $newUserId,
                        'user_register',
                        'utilisateur',
                        (int) $newUserId,
                        [
                            'role' => $role,
                            'email' => $email,
                            'statut_compte' => $statutCompte,
                        ]
                    );
                }

                if ($role === 'conducteur') {
                    $this->flash(
                        'success',
                        "Compte conducteur créé. Votre compte doit être validé par l'administration avant connexion."
                    );
                } else {
                    $this->flash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
                }

                $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
            }

            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = compact('nom', 'prenom', 'email', 'telephone', 'role');
        }

        require_once ROOT_PATH . '/views/auth/register.php';
    }

    public function login(): void
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = strtolower(trim($_POST['email'] ?? ''));
            $mdp = $_POST['mot_de_passe'] ?? '';

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                $status = $this->getUserAccountStatus($user);

                if ($status !== 'actif') {
                    if ($status === 'en_attente' && $user['role'] === 'conducteur') {
                        $this->flash('error', "Votre compte conducteur est en attente de validation par l'administration.");
                    } elseif ($status === 'en_attente') {
                        $this->flash('error', "Votre compte est en attente de validation par l'administration.");
                    } elseif ($status === 'refuse') {
                        $this->flash('error', "Votre compte a été refusé par l'administration.");
                    } elseif ($status === 'desactive') {
                        $this->flash('error', "Votre compte est désactivé. Contactez l'administration.");
                    } else {
                        $this->flash('error', "Votre compte n'est pas autorisé à se connecter.");
                    }
                    $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
                }

                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'statut_compte' => $status,
                ];

                $jwtPayload = ['user' => $_SESSION['user']];
                $jwtToken = JWT::encode($jwtPayload);
                setcookie('jwt_token', $jwtToken, time() + (24 * 60 * 60), '/', '', false, true);

                $this->audit(
                    (int) $user['id'],
                    'user_login',
                    'utilisateur',
                    (int) $user['id'],
                    [
                        'role' => $user['role'],
                        'email' => $user['email'],
                    ]
                );

                $this->flash('success', 'Bienvenue, ' . htmlspecialchars($user['prenom'], ENT_QUOTES, 'UTF-8') . ' !');

                match ($user['role']) {
                    'admin' => $this->redirect(BASE_URL . '/index.php?page=admin'),
                    'conducteur' => $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets'),
                    default => $this->redirect(BASE_URL . '/index.php?page=trajet'),
                };
            }

            $this->flash('error', 'Email ou mot de passe incorrect.');
        }

        require_once ROOT_PATH . '/views/auth/login.php';
    }

    public function logout(): void
    {
        $currentUser = $_SESSION['user'] ?? null;
        if ($currentUser) {
            $this->audit(
                (int) ($currentUser['id'] ?? 0),
                'user_logout',
                'utilisateur',
                (int) ($currentUser['id'] ?? 0),
                [
                    'role' => $currentUser['role'] ?? null,
                    'email' => $currentUser['email'] ?? null,
                ]
            );
        }

        $_SESSION = [];
        session_destroy();
        setcookie('jwt_token', '', time() - 3600, '/');
        $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
    }
}
