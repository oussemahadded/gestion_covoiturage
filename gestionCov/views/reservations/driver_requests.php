<?php 
$pageTitle = 'Demandes de réservation'; 
$includeMap = true;
require_once ROOT_PATH . '/views/layouts/header.php'; 
?>

<div class="container">
    <h1 class="page-title">
        <?= ui_icon('reservation', 'icon icon-md') ?>
        <span>Demandes de réservation</span>
    </h1>

    <?php if (empty($requests)): ?>
        <div class="empty-state-box">
            <span class="empty-illustration"><?= ui_icon('messages', 'icon icon-xl') ?></span>
            <p>Aucune demande de réservation pour vos trajets.</p>
        </div>
    <?php else: ?>
        <div class="reservation-request-list">
            <?php foreach ($requests as $r): ?>
                <?php
                $statusLabel = match ($r['statut']) {
                    'en_attente' => 'En attente',
                    'confirmee' => 'Confirmée',
                    'annulee' => 'Annulée',
                    'refusee' => 'Refusée',
                    default => (string) $r['statut'],
                };
                $statusIcon = match ($r['statut']) {
                    'en_attente' => 'pending',
                    'confirmee' => 'success',
                    'annulee' => 'cancelled',
                    'refusee' => 'refused',
                    default => 'warning',
                };
                $reservationDistance = $r['reservation_distance_km'] ?? null;
                $reservationType = (string) ($r['reservation_point_type'] ?? '');
                $pointLabel = $reservationType === 'prise_en_charge'
                    ? 'Point de prise en charge'
                    : ($reservationType === 'depose' ? 'Point de dépose' : '');
                $pointIcon = $reservationType === 'prise_en_charge' ? 'departure' : 'arrival';
                // Points conducteur estimés pour cette réservation
                $prixParKm = (float) ($r['prix_par_km'] ?? 250);
                if ($prixParKm <= 10) $prixParKm = 250; // migration auto
                $pointsEstimes = $reservationDistance !== null
                    ? max(1, (int) round((float) $reservationDistance * $prixParKm))
                    : (int) round((float) ($r['distance_km'] ?? 0) * $prixParKm);
                ?>
                <article class="reservation-request-card">
                    <div class="request-main-grid">
                        <div class="request-meta">
                            <div class="request-route">
                                <strong><?= htmlspecialchars($r['ville_depart'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="arrow-small">→</span>
                                <strong><?= htmlspecialchars($r['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="request-datetime">
                                <?= date('d/m/Y', strtotime($r['date_depart'])) ?> à <?= substr($r['heure_depart'], 0, 5) ?>
                            </div>
                        </div>
                        
                        <div class="request-passenger">
                            <strong><?= htmlspecialchars($r['passager_prenom'] . ' ' . $r['passager_nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($r['passager_tel'])): ?>
                                <div class="mt-1">
                                    <a href="tel:<?= htmlspecialchars($r['passager_tel'], ENT_QUOTES, 'UTF-8') ?>" class="contact-inline-link">
                                        <?= ui_icon('phone', 'icon icon-xs') ?>
                                        <span><?= htmlspecialchars($r['passager_tel'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="mt-2">
                                <a href="<?= BASE_URL ?>/index.php?page=message&action=conversation&contact=<?= (int) $r['passager_id'] ?>" class="btn btn-outline btn-xs">
                                    <?= ui_icon('messages', 'icon icon-xs') ?>
                                    <span>Message</span>
                                </a>
                            </div>
                        </div>
                        
                        <div class="request-status-block">
                            <span class="status-badge status-<?= htmlspecialchars($r['statut'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= ui_icon($statusIcon, 'icon icon-xs') ?>
                                <span><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                            
                            <?php if ($pointLabel !== '' && isset($r['reservation_point_lat'], $r['reservation_point_lng'])): ?>
                                <div class="request-point-info mt-2">
                                    <div class="font-medium text-sm">
                                        <?= ui_icon($pointIcon, 'icon icon-xs') ?> <?= htmlspecialchars($pointLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="request-coordinates">
                                        <?= number_format((float) $r['reservation_point_lat'], 5) ?>,
                                        <?= number_format((float) $r['reservation_point_lng'], 5) ?>
                                    </div>
                                </div>
                                <?php if ($reservationDistance !== null): ?>
                                    <div class="request-financial mt-1">Distance: <?= number_format((float) $reservationDistance, 2) ?> km</div>
                                <?php endif; ?>
                                <div class="request-reward mt-1">
                                    <?= ui_icon('price', 'icon icon-xs') ?>
                                    <span><strong><?= $pointsEstimes ?> pts</strong> estimés si terminé</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (($r['statut'] ?? '') === 'confirmee' && !empty($r['points_earned'])): ?>
                                <div class="payment-status-block mt-2">
                                    <span class="payment-status-badge payment-status-declare_paye compact-badge">
                                        <?= ui_icon('reward', 'icon icon-xs') ?>
                                        <span><?= (int) $r['points_earned'] ?> points gagnés</span>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="request-map-actions">
                            <?php if ($pointLabel !== '' && isset($r['reservation_point_lat'], $r['reservation_point_lng']) && !empty($r['route_geometry'])): ?>
                                <div class="request-point-map-card">
                                    <div class="driver-request-map"
                                         data-route-geometry="<?= htmlspecialchars((string) $r['route_geometry'], ENT_QUOTES, 'UTF-8') ?>"
                                         data-point-lat="<?= htmlspecialchars((string) $r['reservation_point_lat'], ENT_QUOTES, 'UTF-8') ?>"
                                         data-point-lng="<?= htmlspecialchars((string) $r['reservation_point_lng'], ENT_QUOTES, 'UTF-8') ?>"
                                         data-point-type="<?= htmlspecialchars((string) $reservationType, ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="request-actions mt-2">
                                <?php if ($r['statut'] === 'en_attente'): ?>
                                    <form action="<?= BASE_URL ?>/index.php?page=reservation&action=updateStatus" method="POST" class="inline-form d-block">
                                        <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                                        <input type="hidden" name="statut" value="confirmee">
                                        <button type="submit" class="btn btn-success btn-sm btn-block">
                                            <?= ui_icon('success', 'icon icon-xs') ?>
                                            <span>Confirmer</span>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/index.php?page=reservation&action=updateStatus" method="POST" class="inline-form d-block mt-1">
                                        <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                                        <input type="hidden" name="statut" value="refusee">
                                        <button type="submit" class="btn btn-danger btn-sm btn-block" onclick="return confirm('Refuser cette réservation ?')">
                                            <?= ui_icon('refused', 'icon icon-xs') ?>
                                            <span>Refuser</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="text-center text-muted mt-2">—</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
