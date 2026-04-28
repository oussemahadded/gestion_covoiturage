<?php
/**
 * controllers/TrajetController.php
 * CRUD trajets + recherche.
 */

class TrajetController
{
    private Trajet $trajetModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->trajetModel = new Trajet();
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

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->flash('error', 'Vous devez être connecté.');
            $this->redirect(BASE_URL . '/index.php?page=auth&action=login');
        }
    }

    private function requireConducteur(): void
    {
        $this->requireAuth();
        if (($_SESSION['user']['role'] ?? '') !== 'conducteur') {
            http_response_code(403);
            die('Accès interdit.');
        }
    }

    private function audit(string $action, string $entityType, ?int $entityId = null, array $details = []): void
    {
        $userId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;

        try {
            $ok = $this->auditLog->create($userId, $action, $entityType, $entityId, $details);
            if (!$ok) {
                error_log('[AUDIT] Failed to write log: ' . $action);
            }
        } catch (Throwable $e) {
            error_log('[AUDIT] ' . $e->getMessage());
        }
    }

    private function isValidIsoDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed instanceof DateTime && $parsed->format('Y-m-d') === $date;
    }

    private function parseFrenchDate(string $dateDisplay): string
    {
        $dateDisplay = trim($dateDisplay);
        if ($dateDisplay === '') {
            return '';
        }

        $parsed = DateTime::createFromFormat('d/m/Y', $dateDisplay);
        if (!$parsed || $parsed->format('d/m/Y') !== $dateDisplay) {
            return '';
        }

        return $parsed->format('Y-m-d');
    }

    private function normalizeDateInput(string $isoDate, string $dateDisplay): string
    {
        $isoDate = trim($isoDate);
        if ($this->isValidIsoDate($isoDate)) {
            return $isoDate;
        }

        return $this->parseFrenchDate($dateDisplay);
    }

    private function normalizeSesameCity(string $city): string
    {
        return strcasecmp($city, 'Sesame') === 0 ? 'Sesame' : $city;
    }

    public function index(): void
    {
        $trajets = [];
        $searchPerformed = false;

        $depart = trim($_GET['depart'] ?? '');
        $arrivee = trim($_GET['arrivee'] ?? '');
        $date = $this->normalizeDateInput(
            (string) ($_GET['date'] ?? ''),
            (string) ($_GET['date_display'] ?? '')
        );

        $rawDateProvided = !empty($_GET['date']) || !empty($_GET['date_display']);

        if ($depart !== '' || $arrivee !== '' || $rawDateProvided) {
            $searchPerformed = true;
            if ($rawDateProvided && $date === '') {
                $trajets = [];
            } else {
                $trajets = $this->trajetModel->search($depart, $arrivee, $date);
            }
        } else {
            $trajets = $this->trajetModel->getAll();
        }

        require_once ROOT_PATH . '/views/trajets/index.php';
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $trajet = $this->trajetModel->findById($id);

        if (!$trajet) {
            $this->flash('error', 'Trajet introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }

        $avisModel = new Avis();
        $avisList = $avisModel->getByTrajet($id);
        $avgRating = $avisModel->getAverageForConducteur((int) $trajet['conducteur_id']);

        $reservationModel = new Reservation();
        $currentReservation = false;
        $alreadyBooked = false;
        $canReview = false;

        if (isset($_SESSION['user'])) {
            $userId = (int) $_SESSION['user']['id'];
            $currentReservation = $reservationModel->findByTrajetAndPassager($id, $userId);
            $alreadyBooked = (bool) $currentReservation;
            $canReview = in_array($_SESSION['user']['role'], ['etudiant', 'professeur'], true)
                && $avisModel->canReview($id, $userId)
                && !$avisModel->existsForPassager($id, $userId);
        }

        require_once ROOT_PATH . '/views/trajets/show.php';
    }

    public function myTrajets(): void
    {
        $this->requireConducteur();
        $trajets = $this->trajetModel->getByConducteur((int) $_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/trajets/my_trajets.php';
    }

    public function create(): void
    {
        $this->requireConducteur();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeTrajetInput($_POST);
            $errors = $this->validateTrajetInput($data);

            if (empty($errors)) {
                $data['conducteur_id'] = (int) $_SESSION['user']['id'];
                $trajetId = $this->trajetModel->create($data);

                $this->audit(
                    'trajet_created',
                    'trajet',
                    $trajetId,
                    [
                        'conducteur_id' => (int) $_SESSION['user']['id'],
                        'ville_depart' => $data['ville_depart'],
                        'ville_arrivee' => $data['ville_arrivee'],
                        'date_depart' => $data['date_depart'],
                        'heure_depart' => $data['heure_depart'],
                        'prix' => (float) $data['prix'],
                    ]
                );

                $this->flash('success', 'Trajet créé avec succès.');
                $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
            }

            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
        }

        require_once ROOT_PATH . '/views/trajets/create.php';
    }

    public function edit(): void
    {
        $this->requireConducteur();
        $id = (int) ($_GET['id'] ?? 0);
        $trajet = $this->trajetModel->findById($id);

        if (!$trajet || (int) $trajet['conducteur_id'] !== (int) $_SESSION['user']['id']) {
            $this->flash('error', 'Trajet introuvable ou accès refusé.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeTrajetInput($_POST);
            $errors = $this->validateTrajetInput($data);

            if (empty($errors)) {
                $data['conducteur_id'] = (int) $_SESSION['user']['id'];
                $ok = $this->trajetModel->update($id, $data);
                if ($ok) {
                    $this->audit(
                        'trajet_updated',
                        'trajet',
                        $id,
                        [
                            'conducteur_id' => (int) $_SESSION['user']['id'],
                            'ville_depart' => $data['ville_depart'],
                            'ville_arrivee' => $data['ville_arrivee'],
                            'date_depart' => $data['date_depart'],
                            'heure_depart' => $data['heure_depart'],
                            'prix' => (float) $data['prix'],
                        ]
                    );
                }

                $this->flash('success', 'Trajet modifié avec succès.');
                $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
            }

            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
        }

        require_once ROOT_PATH . '/views/trajets/edit.php';
    }

    public function delete(): void
    {
        $this->requireConducteur();
        $id = (int) ($_POST['id'] ?? 0);
        $trajet = $this->trajetModel->findById($id);

        if (!$trajet || (int) $trajet['conducteur_id'] !== (int) $_SESSION['user']['id']) {
            $this->flash('error', 'Trajet introuvable ou accès refusé.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
        }

        $ok = $this->trajetModel->delete($id, (int) $_SESSION['user']['id']);
        if ($ok) {
            $this->audit(
                'trajet_deleted',
                'trajet',
                $id,
                [
                    'conducteur_id' => (int) $_SESSION['user']['id'],
                    'ville_depart' => $trajet['ville_depart'],
                    'ville_arrivee' => $trajet['ville_arrivee'],
                    'date_depart' => $trajet['date_depart'],
                    'heure_depart' => $trajet['heure_depart'],
                ]
            );
        }

        $this->flash('success', 'Trajet supprimé.');
        $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
    }

    private function sanitizeTrajetInput(array $post): array
    {
        $villeDepart = trim(htmlspecialchars($post['ville_depart'] ?? '', ENT_QUOTES, 'UTF-8'));
        $villeArrivee = trim(htmlspecialchars($post['ville_arrivee'] ?? '', ENT_QUOTES, 'UTF-8'));

        return [
            'ville_depart' => $this->normalizeSesameCity($villeDepart),
            'ville_arrivee' => $this->normalizeSesameCity($villeArrivee),
            'date_depart' => $this->normalizeDateInput(
                (string) ($post['date_depart'] ?? ''),
                (string) ($post['date_depart_display'] ?? '')
            ),
            'heure_depart' => trim((string) ($post['heure_depart'] ?? '')),
            'prix' => (float) ($post['prix'] ?? 0),
            'places_total' => (int) ($post['places_total'] ?? 0),
            'description' => trim(htmlspecialchars($post['description'] ?? '', ENT_QUOTES, 'UTF-8')),
        ];
    }

    private function validateTrajetInput(array $data): array
    {
        $errors = [];

        if ($data['ville_depart'] === '') {
            $errors[] = 'Ville de départ obligatoire.';
        }
        if ($data['ville_arrivee'] === '') {
            $errors[] = 'Ville d’arrivée obligatoire.';
        }

        $departIsSesame = strcasecmp($data['ville_depart'], 'Sesame') === 0;
        $arriveeIsSesame = strcasecmp($data['ville_arrivee'], 'Sesame') === 0;
        if (!$departIsSesame && !$arriveeIsSesame) {
            $errors[] = 'Le trajet doit partir de Sesame ou arriver à Sesame.';
        }
        if ($departIsSesame && $arriveeIsSesame) {
            $errors[] = 'Le départ et l’arrivée ne peuvent pas être tous les deux à Sesame.';
        }

        if ($data['date_depart'] === '') {
            $errors[] = 'Date de départ obligatoire.';
        } elseif (!$this->isValidIsoDate($data['date_depart'])) {
            $errors[] = 'Date de départ invalide.';
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_depart']);
            $today = new DateTime('today');
            if ($date < $today) {
                $errors[] = 'La date doit être aujourd’hui ou dans le futur.';
            }
        }

        if ($data['heure_depart'] === '') {
            $errors[] = 'Heure de départ obligatoire.';
        }
        if ($data['prix'] < 0) {
            $errors[] = 'Le prix ne peut pas être négatif.';
        }
        if ($data['places_total'] < 1) {
            $errors[] = 'Au moins 1 place est requise.';
        }

        return $errors;
    }
}

