<?php
/**
 * controllers/ReservationController.php
 * Reservation workflow for passengers and drivers.
 */

class ReservationController
{
    private Reservation $resModel;
    private Trajet $trajetModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->resModel = new Reservation();
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

    private function requirePassenger(): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'], ['etudiant', 'professeur'], true)) {
            http_response_code(403);
            die('Accès interdit.');
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

    public function book(): void
    {
        $this->requirePassenger();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }

        $trajetId = (int) ($_POST['trajet_id'] ?? 0);
        $passagerId = (int) ($_SESSION['user']['id'] ?? 0);
        $trajet = $this->trajetModel->findById($trajetId);

        if (!$trajet) {
            $this->flash('error', 'Trajet introuvable.');
            $this->redirect(BASE_URL . '/index.php?page=trajet');
        }

        $tripStatus = (string) ($trajet['statut_trajet'] ?? 'publie');
        $tripTimestamp = strtotime(trim((string) ($trajet['date_depart'] ?? '')) . ' ' . trim((string) ($trajet['heure_depart'] ?? '')));

        if ($tripStatus === 'termine') {
            $this->flash('error', 'Trajet terminé. Ce trajet a été marqué comme terminé par le conducteur.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ($tripStatus === 'annule') {
            $this->flash('error', "Trajet annulé. Ce trajet n'est plus disponible à la réservation.");
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ($tripTimestamp !== false && $tripTimestamp <= time()) {
            $this->flash('error', 'Ce trajet est déjà passé et ne peut plus être réservé.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ((int) ($trajet['places_restantes'] ?? 0) <= 0) {
            $this->flash('error', 'Trajet complet. Aucune place disponible.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        if ((int) $trajet['conducteur_id'] === $passagerId) {
            $this->flash('error', 'Vous ne pouvez pas réserver votre propre trajet.');
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        $pointData = $this->buildReservationPointData($_POST, $trajet);
        if (!empty($pointData['error'])) {
            $this->flash('error', (string) $pointData['error']);
            $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
        }

        $result = $this->resModel->createSafe($trajetId, $passagerId, $pointData);
        if (!empty($result['success'])) {
            $this->audit(
                'reservation_created',
                'reservation',
                isset($result['reservation_id']) ? (int) $result['reservation_id'] : null,
                [
                    'trajet_id' => $trajetId,
                    'passager_id' => $passagerId,
                    'conducteur_id' => (int) $trajet['conducteur_id'],
                    'statut' => 'en_attente',
                    'prix_par_passager' => (float) ($pointData['reservation_price'] ?? $trajet['prix']),
                    'reservation_point_type' => $pointData['reservation_point_type'] ?? null,
                    'reservation_distance_km' => $pointData['reservation_distance_km'] ?? null,
                ]
            );
        }

        $this->flash(!empty($result['success']) ? 'success' : 'error', (string) ($result['message'] ?? 'Erreur.'));
        $this->redirect(BASE_URL . '/index.php?page=trajet&action=show&id=' . $trajetId);
    }

    public function myReservations(): void
    {
        $this->requirePassenger();
        $reservations = $this->resModel->getByPassager((int) $_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/reservations/my_reservations.php';
    }

    public function driverRequests(): void
    {
        $this->requireConducteur();
        $requests = $this->resModel->getByTrajetConducteur((int) $_SESSION['user']['id']);
        require_once ROOT_PATH . '/views/reservations/driver_requests.php';
    }

    public function updateStatus(): void
    {
        $this->requireConducteur();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
        }

        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';

        if (!in_array($statut, ['confirmee', 'refusee'], true)) {
            $this->flash('error', 'Statut invalide.');
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
        }

        $existingReservation = $this->resModel->findById($reservationId);
        $ok = $this->resModel->updateStatus($reservationId, $statut, (int) $_SESSION['user']['id']);
        if ($ok) {
            $this->audit(
                $statut === 'confirmee' ? 'reservation_confirmed' : 'reservation_refused',
                'reservation',
                $reservationId,
                [
                    'reservation_id' => $reservationId,
                    'trajet_id' => isset($existingReservation['trajet_id']) ? (int) $existingReservation['trajet_id'] : null,
                    'passager_id' => isset($existingReservation['passager_id']) ? (int) $existingReservation['passager_id'] : null,
                    'previous_status' => (string) ($existingReservation['statut'] ?? ''),
                    'new_status' => $statut,
                    'conducteur_id' => (int) $_SESSION['user']['id'],
                ]
            );
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Réservation mise à jour.' : 'Erreur lors de la mise à jour.');
        $this->redirect(BASE_URL . '/index.php?page=reservation&action=driverRequests');
    }

    public function cancel(): void
    {
        $this->requirePassenger();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?page=reservation&action=myReservations');
        }

        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $passagerId = (int) $_SESSION['user']['id'];
        $existingReservation = $this->resModel->findById($reservationId);
        $ok = $this->resModel->cancelByPassager($reservationId, $passagerId);

        if ($ok) {
            $this->audit(
                'reservation_cancelled',
                'reservation',
                $reservationId,
                [
                    'reservation_id' => $reservationId,
                    'trajet_id' => isset($existingReservation['trajet_id']) ? (int) $existingReservation['trajet_id'] : null,
                    'passager_id' => isset($existingReservation['passager_id']) ? (int) $existingReservation['passager_id'] : $passagerId,
                    'previous_status' => (string) ($existingReservation['statut'] ?? ''),
                    'new_status' => 'annulee',
                ]
            );
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Réservation annulée.' : "Impossible d'annuler.");
        $this->redirect(BASE_URL . '/index.php?page=reservation&action=myReservations');
    }

    private function buildReservationPointData(array $post, array $trajet): array
    {
        if (empty($trajet['route_geometry'])) {
            return ['error' => 'Le circuit de ce trajet est indisponible.'];
        }

        $latRaw = trim((string) ($post['reservation_point_lat'] ?? ''));
        $lngRaw = trim((string) ($post['reservation_point_lng'] ?? ''));
        if ($latRaw === '' || $lngRaw === '') {
            return ['error' => 'Veuillez choisir un point situé sur le circuit proposé.'];
        }
        if (!is_numeric($latRaw) || !is_numeric($lngRaw)) {
            return ['error' => 'Veuillez choisir un point situé sur le circuit proposé.'];
        }

        $geometry = $this->decodeRouteGeometry((string) $trajet['route_geometry']);
        if (!$geometry) {
            return ['error' => 'Le circuit de ce trajet est indisponible.'];
        }

        $routeCoords = $this->getRouteCoordinatesFromGeometry($geometry);
        if (count($routeCoords) < 2) {
            return ['error' => 'Le circuit de ce trajet est indisponible.'];
        }

        $nearest = $this->findNearestPointOnRoute((float) $latRaw, (float) $lngRaw, $routeCoords);
        if (!$nearest || $nearest['distance_meters'] > 500) {
            return ['error' => 'Veuillez choisir un point situé sur le circuit proposé.'];
        }

        $totalDistanceMeters = $this->computeRouteDistanceAlongPolyline(
            $routeCoords,
            count($routeCoords) - 2,
            1.0
        );
        if ($totalDistanceMeters <= 0) {
            return ['error' => 'Le circuit de ce trajet est indisponible.'];
        }

        $distanceToPointMeters = $this->computeRouteDistanceAlongPolyline(
            $routeCoords,
            $nearest['segment_index'],
            $nearest['t']
        );

        $direction = $this->getTripDirection($trajet);
        $pointType = $direction === 'vers_sesame' ? 'prise_en_charge' : 'depose';
        $chargedMeters = $direction === 'vers_sesame'
            ? max(0, $totalDistanceMeters - $distanceToPointMeters)
            : max(0, $distanceToPointMeters);

        $chargedKm = round($chargedMeters / 1000, 2);
        $totalDuration = isset($trajet['duree_minutes']) && is_numeric($trajet['duree_minutes'])
            ? (int) $trajet['duree_minutes']
            : null;
        $chargedDuration = $this->computeRouteDurationProportionally($totalDuration, $chargedMeters, $totalDistanceMeters);

        $prixParKm = isset($trajet['prix_par_km']) ? (float) $trajet['prix_par_km'] : 0.0;
        $reservationPrice = round($chargedKm * $prixParKm, 2);

        return [
            'reservation_point_lat' => $nearest['lat'],
            'reservation_point_lng' => $nearest['lng'],
            'reservation_point_type' => $pointType,
            'reservation_distance_km' => $chargedKm,
            'reservation_duree_minutes' => $chargedDuration,
            'reservation_price' => $reservationPrice,
        ];
    }

    private function getTripDirection(array $trajet): string
    {
        $arrivee = (string) ($trajet['ville_arrivee'] ?? '');
        return strcasecmp($arrivee, 'Sesame') === 0 ? 'vers_sesame' : 'depuis_sesame';
    }

    private function decodeRouteGeometry(string $raw): array|false
    {
        if ($raw === '') {
            return false;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || ($decoded['type'] ?? '') !== 'LineString') {
            return false;
        }
        if (!isset($decoded['coordinates']) || !is_array($decoded['coordinates'])) {
            return false;
        }
        if (count($decoded['coordinates']) < 2) {
            return false;
        }
        return $decoded;
    }

    private function getRouteCoordinatesFromGeometry(array $geometry): array
    {
        $coords = [];
        foreach ($geometry['coordinates'] as $coord) {
            if (!is_array($coord) || count($coord) < 2) {
                continue;
            }
            $lng = (float) $coord[0];
            $lat = (float) $coord[1];
            $coords[] = ['lat' => $lat, 'lng' => $lng];
        }
        return $coords;
    }

    private function findNearestPointOnRoute(float $lat, float $lng, array $routeCoords): array|false
    {
        $closest = null;
        $minDistance = INF;

        for ($i = 0; $i < count($routeCoords) - 1; $i++) {
            $segment = $this->distancePointToSegmentMeters($lat, $lng, $routeCoords[$i], $routeCoords[$i + 1]);
            if ($segment['distance_meters'] < $minDistance) {
                $minDistance = $segment['distance_meters'];
                $closest = [
                    'lat' => $segment['lat'],
                    'lng' => $segment['lng'],
                    'distance_meters' => $segment['distance_meters'],
                    'segment_index' => $i,
                    't' => $segment['t'],
                ];
            }
        }

        return $closest;
    }

    private function distancePointToSegmentMeters(float $lat, float $lng, array $a, array $b): array
    {
        $r = 6371000;
        $lat1 = deg2rad($a['lat']);
        $lng1 = deg2rad($a['lng']);
        $lat2 = deg2rad($b['lat']);
        $lng2 = deg2rad($b['lng']);
        $latP = deg2rad($lat);
        $lngP = deg2rad($lng);

        $lat0 = ($lat1 + $lat2) / 2;
        $ax = $lng1 * cos($lat0) * $r;
        $ay = $lat1 * $r;
        $bx = $lng2 * cos($lat0) * $r;
        $by = $lat2 * $r;
        $px = $lngP * cos($lat0) * $r;
        $py = $latP * $r;

        $dx = $bx - $ax;
        $dy = $by - $ay;
        $lenSq = ($dx * $dx) + ($dy * $dy);
        $t = $lenSq > 0 ? (($px - $ax) * $dx + ($py - $ay) * $dy) / $lenSq : 0.0;
        $t = max(0.0, min(1.0, $t));

        $closestX = $ax + ($dx * $t);
        $closestY = $ay + ($dy * $t);
        $distanceMeters = sqrt(($px - $closestX) ** 2 + ($py - $closestY) ** 2);

        $closestLat = rad2deg($closestY / $r);
        $closestLng = rad2deg($closestX / ($r * cos($lat0)));

        return [
            'distance_meters' => $distanceMeters,
            't' => $t,
            'lat' => $closestLat,
            'lng' => $closestLng,
        ];
    }

    private function distanceBetweenLatLngMeters(array $a, array $b): float
    {
        $earthRadius = 6371000;
        $lat1 = deg2rad($a['lat']);
        $lng1 = deg2rad($a['lng']);
        $lat2 = deg2rad($b['lat']);
        $lng2 = deg2rad($b['lng']);
        $dLat = $lat2 - $lat1;
        $dLng = $lng2 - $lng1;

        $h = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($h), sqrt(1 - $h));

        return $earthRadius * $c;
    }

    private function computeRouteDistanceAlongPolyline(array $routeCoords, int $segmentIndex, float $t): float
    {
        $distance = 0.0;
        $segmentIndex = max(0, min($segmentIndex, count($routeCoords) - 2));
        $t = max(0.0, min(1.0, $t));

        for ($i = 0; $i < $segmentIndex; $i++) {
            $distance += $this->distanceBetweenLatLngMeters($routeCoords[$i], $routeCoords[$i + 1]);
        }

        $segmentDistance = $this->distanceBetweenLatLngMeters($routeCoords[$segmentIndex], $routeCoords[$segmentIndex + 1]);
        $distance += $segmentDistance * $t;

        return $distance;
    }

    private function computeRouteDurationProportionally(?int $totalMinutes, float $segmentMeters, float $totalMeters): ?int
    {
        if ($totalMinutes === null || $totalMinutes <= 0 || $totalMeters <= 0) {
            return null;
        }

        $ratio = $segmentMeters / $totalMeters;
        return (int) round($totalMinutes * $ratio);
    }
}
