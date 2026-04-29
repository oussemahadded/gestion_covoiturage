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
            $errors = $this->validateTrajetInput($data, $_POST);

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
                        'distance_km' => $data['distance_km'],
                        'duree_minutes' => $data['duree_minutes'],
                        'prix_par_km' => $data['prix_par_km'],
                        'prix' => (float) $data['prix'],
                        'point_lat' => $data['point_lat'],
                        'point_lng' => $data['point_lng'],
                        'route_provider' => $data['route_provider'] ?? null,
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
            $errors = $this->validateTrajetInput($data, $_POST);

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
                            'distance_km' => $data['distance_km'],
                            'duree_minutes' => $data['duree_minutes'],
                            'prix_par_km' => $data['prix_par_km'],
                            'prix' => (float) $data['prix'],
                            'point_lat' => $data['point_lat'],
                            'point_lng' => $data['point_lng'],
                            'route_provider' => $data['route_provider'] ?? null,
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

    public function complete(): void
    {
        $this->requireConducteur();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
        }

        $trajetId = (int) ($_POST['trajet_id'] ?? 0);
        if ($trajetId <= 0) {
            $this->flash('error', 'Trajet invalide.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
        }

        $conducteurId = (int) $_SESSION['user']['id'];
        $result = $this->trajetModel->completeTrip($trajetId, $conducteurId);

        if (!empty($result['success'])) {
            $declaredTotal = (float) ($result['declared_total'] ?? 0);
            $completedReservations = (int) ($result['completed_reservations'] ?? 0);
            $completedAt = date('Y-m-d H:i:s');

            $this->audit('trajet_completed', 'trajet', $trajetId, [
                'conducteur_id' => $conducteurId,
                'completed_reservations' => $completedReservations,
                'declared_total' => $declaredTotal,
                'completed_at' => $completedAt,
                'payment_mode' => 'cash_declared',
            ]);
            $this->audit('cash_payment_declared', 'trajet', $trajetId, [
                'declared_total' => $declaredTotal,
                'reservation_count' => $completedReservations,
                'payment_mode' => 'cash_declared',
            ]);

            $this->flash(
                'success',
                'Trajet terminé. Total déclaré : ' . number_format($declaredTotal, 2) . ' TND.'
            );
        } else {
            $this->flash('error', (string) ($result['message'] ?? 'Impossible de terminer ce trajet.'));
        }

        $this->redirect(BASE_URL . '/index.php?page=trajet&action=myTrajets');
    }

    private function sanitizeTrajetInput(array $post): array
    {
        $villeDepart = trim(htmlspecialchars($post['ville_depart'] ?? '', ENT_QUOTES, 'UTF-8'));
        $villeArrivee = trim(htmlspecialchars($post['ville_arrivee'] ?? '', ENT_QUOTES, 'UTF-8'));

        $distanceRaw = trim((string) ($post['distance_km'] ?? ''));
        $durationRaw = trim((string) ($post['duree_minutes'] ?? ''));
        $prixParKmRaw = trim((string) ($post['prix_par_km'] ?? ''));
        $pointLatRaw = trim((string) ($post['point_lat'] ?? ''));
        $pointLngRaw = trim((string) ($post['point_lng'] ?? ''));
        $routeGeometryRaw = trim((string) ($post['route_geometry'] ?? ''));
        $routeProviderRaw = trim((string) ($post['route_provider'] ?? ''));

        $distanceKm = $distanceRaw === '' || !is_numeric($distanceRaw) ? null : (float) $distanceRaw;
        $dureeMinutes = $durationRaw === '' || !is_numeric($durationRaw) ? null : (int) $durationRaw;
        $prixParKm = $prixParKmRaw === '' || !is_numeric($prixParKmRaw)
            ? (float) DEFAULT_PRIX_PAR_KM
            : (float) $prixParKmRaw;
        $pointLat = $pointLatRaw === '' || !is_numeric($pointLatRaw) ? null : (float) $pointLatRaw;
        $pointLng = $pointLngRaw === '' || !is_numeric($pointLngRaw) ? null : (float) $pointLngRaw;
        $routeGeometry = $routeGeometryRaw === '' ? null : $routeGeometryRaw;
        $routeProvider = $routeProviderRaw !== '' ? $routeProviderRaw : ROUTING_PROVIDER;

        return [
            'ville_depart' => $this->normalizeSesameCity($villeDepart),
            'ville_arrivee' => $this->normalizeSesameCity($villeArrivee),
            'date_depart' => $this->normalizeDateInput(
                (string) ($post['date_depart'] ?? ''),
                (string) ($post['date_depart_display'] ?? '')
            ),
            'heure_depart' => trim((string) ($post['heure_depart'] ?? '')),
            'distance_km' => $distanceKm,
            'duree_minutes' => $dureeMinutes,
            'prix_par_km' => $prixParKm,
            'point_lat' => $pointLat,
            'point_lng' => $pointLng,
            'route_geometry' => $routeGeometry,
            'route_provider' => $routeProvider,
            'prix' => (float) ($post['prix'] ?? 0),
            'places_total' => (int) ($post['places_total'] ?? 0),
            'description' => trim(htmlspecialchars($post['description'] ?? '', ENT_QUOTES, 'UTF-8')),
        ];
    }

    private function validateTrajetInput(array &$data, array $rawPost): array
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

        $latRaw = trim((string) ($rawPost['point_lat'] ?? ''));
        $lngRaw = trim((string) ($rawPost['point_lng'] ?? ''));

        if ($latRaw === '' || $lngRaw === '') {
            $errors[] = 'Point du trajet sur la carte obligatoire.';
        }

        if ($latRaw !== '' && !is_numeric($latRaw)) {
            $errors[] = 'Latitude invalide.';
        }
        if ($lngRaw !== '' && !is_numeric($lngRaw)) {
            $errors[] = 'Longitude invalide.';
        }

        if ($data['point_lat'] !== null && ($data['point_lat'] < -90 || $data['point_lat'] > 90)) {
            $errors[] = 'Latitude hors limite.';
        }
        if ($data['point_lng'] !== null && ($data['point_lng'] < -180 || $data['point_lng'] > 180)) {
            $errors[] = 'Longitude hors limite.';
        }


        $durationRaw = trim((string) ($rawPost['duree_minutes'] ?? ''));
        if ($durationRaw !== '' && !is_numeric($durationRaw)) {
            $errors[] = 'Durée estimée invalide.';
        }
        if ($data['duree_minutes'] !== null && $data['duree_minutes'] < 0) {
            $errors[] = 'La durée ne peut pas être négative.';
        }

        $prixParKmRaw = trim((string) ($rawPost['prix_par_km'] ?? ''));
        if ($prixParKmRaw !== '' && !is_numeric($prixParKmRaw)) {
            $errors[] = 'Prix par km invalide.';
        }
        if ($data['prix_par_km'] < 0) {
            $errors[] = 'Le prix par km ne peut pas être négatif.';
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

        if (!empty($data['route_geometry'])) {
            json_decode((string) $data['route_geometry'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'Circuit du trajet invalide.';
                $data['route_geometry'] = null;
            }
        }

        $allowedProviders = ['osrm', 'haversine_fallback'];
        if (!in_array((string) $data['route_provider'], $allowedProviders, true)) {
            $data['route_provider'] = ROUTING_PROVIDER;
        }

        if ($data['point_lat'] !== null && $data['point_lng'] !== null) {
            // Server-side distance verification to avoid trusting hidden values.
            $haversine = $this->haversineKm(
                (float) SESAME_LAT,
                (float) SESAME_LNG,
                (float) $data['point_lat'],
                (float) $data['point_lng']
            );
            $tolerance = max(0.5, $haversine * 0.15);
            $distanceKm = $data['distance_km'];

            $distanceInvalid = $distanceKm === null || $distanceKm < 0;
            $distanceTooShort = $distanceKm !== null && ($distanceKm + $tolerance) < $haversine;
            $distanceTooLong = $distanceKm !== null && $distanceKm > ($haversine * 3);

            if ($distanceInvalid || $distanceTooShort || $distanceTooLong) {
                $data['distance_km'] = round($haversine, 2);
                $data['route_provider'] = 'haversine_fallback';
                $data['route_geometry'] = null;
            }
        }

        return $errors;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0;
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;
        $c = 2 * asin(min(1, sqrt($a)));

        return $earthRadius * $c;
    }
}
