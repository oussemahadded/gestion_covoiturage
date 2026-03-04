<?php
/**
 * controllers/TrajetController.php
 * CRUD des trajets + recherche
 */

class TrajetController
{
    private Trajet $trajetModel;

    public function __construct()
    {
        $this->trajetModel = new Trajet();
    }

    private function redirect(string $url): void { header("Location: $url"); exit; }
    private function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

    /** Vérifie qu'un utilisateur est connecté */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->flash('error', 'Vous devez être connecté.');
            $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
        }
    }

    /** Vérifie qu'un utilisateur a le rôle requis */
    private function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            http_response_code(403);
            die('Accès interdit.');
        }
    }

    // ── Recherche & Liste ─────────────────────────────────────────────────────

    public function index(): void
    {
        $trajets        = [];
        $searchPerformed = false;

        if (!empty($_GET['depart']) || !empty($_GET['arrivee']) || !empty($_GET['date'])) {
            $depart  = trim($_GET['depart']  ?? '');
            $arrivee = trim($_GET['arrivee'] ?? '');
            $date    = $_GET['date'] ?? '';
            $trajets = $this->trajetModel->search($depart, $arrivee, $date);
            $searchPerformed = true;
        } else {
            $trajets = $this->trajetModel->getAll();
        }

        require_once ROOT_PATH . '/views/trajets/index.php';
    }

    // ── Détail d'un trajet ────────────────────────────────────────────────────

    public function show(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $trajet = $this->trajetModel->findById($id);

        if (!$trajet) {
            $this->flash('error', 'Trajet introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }

        $avisModel       = new Avis();
        $avisList        = $avisModel->getByTrajet($id);
        $avgRating       = $avisModel->getAverageForConducteur($trajet['conducteur_id']);

        $reservationModel = new Reservation();
        $alreadyBooked    = false;
        $canReview        = false;

        if (isset($_SESSION['user'])) {
            $userId       = $_SESSION['user']['id'];
            $alreadyBooked = $reservationModel->exists($id, $userId);
            $canReview     = $avisModel->canReview($id, $userId) && !$avisModel->existsForPassager($id, $userId);
        }

        require_once ROOT_PATH . '/views/trajets/show.php';
    }

    // ── Mes trajets (conducteur) ──────────────────────────────────────────────

    public function myTrajets(): void
    {
        $this->requireRole('conducteur');
        $trajets = $this->trajetModel->getByConducteur($_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/trajets/my_trajets.php';
    }

    // ── Créer trajet ──────────────────────────────────────────────────────────

    public function create(): void
    {
        $this->requireRole('conducteur');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data   = $this->sanitizeTrajetInput($_POST);
            $errors = $this->validateTrajetInput($data);

            if (empty($errors)) {
                $data['conducteur_id'] = $_SESSION['user']['id'];
                $this->trajetModel->create($data);
                $this->flash('success', 'Trajet créé avec succès !');
                $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
            }
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data']   = $data;
        }

        require_once ROOT_PATH . '/views/trajets/create.php';
    }

    // ── Modifier trajet ───────────────────────────────────────────────────────

    public function edit(): void
    {
        $this->requireRole('conducteur');
        $id     = (int) ($_GET['id'] ?? 0);
        $trajet = $this->trajetModel->findById($id);

        if (!$trajet || $trajet['conducteur_id'] != $_SESSION['user']['id']) {
            $this->flash('error', 'Trajet introuvable ou accès refusé.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data   = $this->sanitizeTrajetInput($_POST);
            $errors = $this->validateTrajetInput($data);

            if (empty($errors)) {
                $data['conducteur_id'] = $_SESSION['user']['id'];
                $this->trajetModel->update($id, $data);
                $this->flash('success', 'Trajet modifié avec succès !');
                $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
            }
            $_SESSION['form_errors'] = $errors;
        }

        require_once ROOT_PATH . '/views/trajets/edit.php';
    }

    // ── Supprimer trajet ──────────────────────────────────────────────────────

    public function delete(): void
    {
        $this->requireRole('conducteur');
        $id = (int) ($_POST['id'] ?? 0);
        $this->trajetModel->delete($id, $_SESSION['user']['id']);
        $this->flash('success', 'Trajet supprimé.');
        $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
    }

    // ── Validation & Sanitisation ─────────────────────────────────────────────

    private function sanitizeTrajetInput(array $post): array
    {
        return [
            'ville_depart'   => trim(htmlspecialchars($post['ville_depart']   ?? '', ENT_QUOTES, 'UTF-8')),
            'ville_arrivee'  => trim(htmlspecialchars($post['ville_arrivee']  ?? '', ENT_QUOTES, 'UTF-8')),
            'date_depart'    => $post['date_depart']   ?? '',
            'heure_depart'   => $post['heure_depart']  ?? '',
            'prix'           => (float) ($post['prix']           ?? 0),
            'places_total'   => (int)   ($post['places_total']   ?? 0),
            'description'    => trim(htmlspecialchars($post['description'] ?? '', ENT_QUOTES, 'UTF-8')),
        ];
    }

    private function validateTrajetInput(array $data): array
    {
        $errors = [];
        if (empty($data['ville_depart']))   $errors[] = 'Ville de départ obligatoire.';
        if (empty($data['ville_arrivee']))  $errors[] = 'Ville d\'arrivée obligatoire.';
        if (empty($data['date_depart']))    $errors[] = 'Date de départ obligatoire.';
        if (strtotime($data['date_depart']) < strtotime('today')) $errors[] = 'La date doit être dans le futur.';
        if (empty($data['heure_depart']))   $errors[] = 'Heure de départ obligatoire.';
        if ($data['prix'] < 0)              $errors[] = 'Le prix ne peut pas être négatif.';
        if ($data['places_total'] < 1)      $errors[] = 'Au moins 1 place est requise.';
        return $errors;
    }
}
