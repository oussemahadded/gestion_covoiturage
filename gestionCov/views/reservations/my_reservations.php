<?php $pageTitle = 'Mes réservations'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <h1 class="page-title">
        <?= ui_icon('reservation', 'icon icon-md') ?>
        <span>Mes réservations</span>
    </h1>

    <?php if (empty($reservations)): ?>
        <div class="empty-state-box">
            <span class="empty-illustration"><?= ui_icon('reservation', 'icon icon-xl') ?></span>
            <p>Vous n'avez pas encore de réservation.</p>
            <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-primary">
                <?= ui_icon('search', 'icon icon-sm') ?>
                <span>Chercher un trajet</span>
            </a>
        </div>
    <?php else: ?>
        <div class="reservations-list">
            <?php foreach ($reservations as $r): ?>
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
                $reservationPrice = $r['reservation_price'] ?? $r['prix_snapshot'] ?? $r['prix'] ?? 0;
                $reservationDistance = $r['reservation_distance_km'] ?? null;
                $reservationType = (string) ($r['reservation_point_type'] ?? '');
                $pointLabel = $reservationType === 'prise_en_charge'
                    ? 'Point de prise en charge'
                    : ($reservationType === 'depose' ? 'Point de dépose' : '');
                $pointIcon = $reservationType === 'prise_en_charge' ? 'departure' : 'arrival';
                ?>
                <article class="reservation-card">
                    <div class="res-route">
                        <span class="city"><?= htmlspecialchars($r['ville_depart'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="arrow">→</span>
                        <span class="city"><?= htmlspecialchars($r['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="res-meta">
                        <span><?= ui_icon('calendar', 'icon icon-xs') ?> <?= date('d/m/Y', strtotime($r['date_depart'])) ?></span>
                        <span><?= ui_icon('clock', 'icon icon-xs') ?> <?= substr($r['heure_depart'], 0, 5) ?></span>
                        <span><?= ui_icon('price', 'icon icon-xs') ?> <?= number_format((float) $reservationPrice, 2) ?> TND</span>
                        <?php if ($reservationDistance !== null): ?>
                            <span><?= ui_icon('distance', 'icon icon-xs') ?> Distance facturée: <?= number_format((float) $reservationDistance, 2) ?> km</span>
                        <?php endif; ?>
                        <?php if ($pointLabel !== '' && isset($r['reservation_point_lat'], $r['reservation_point_lng'])): ?>
                            <span>
                                <?= ui_icon($pointIcon, 'icon icon-xs') ?>
                                <?= htmlspecialchars($pointLabel, ENT_QUOTES, 'UTF-8') ?>:
                                <?= number_format((float) $r['reservation_point_lat'], 5) ?>,
                                <?= number_format((float) $r['reservation_point_lng'], 5) ?>
                            </span>
                        <?php endif; ?>
                        <span><?= ui_icon('user', 'icon icon-xs') ?> <?= htmlspecialchars($r['conducteur_prenom'] . ' ' . $r['conducteur_nom'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if (!empty($r['conducteur_tel'])): ?>
                            <span><?= ui_icon('phone', 'icon icon-xs') ?> <?= htmlspecialchars($r['conducteur_tel'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="res-footer">
                        <span class="status-badge status-<?= htmlspecialchars($r['statut'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= ui_icon($statusIcon, 'icon icon-xs') ?>
                            <span><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                        <?php if (($r['statut'] ?? '') === 'confirmee' && ($r['payment_status'] ?? '') === 'declare_paye'): ?>
                            <?php
                            $paidAmount = $r['paid_amount'] ?? $r['montant_estime'] ?? $r['prix'] ?? 0;
                            $paidAt = !empty($r['paid_at']) ? date('d/m/Y H:i', strtotime((string) $r['paid_at'])) : '-';
                            ?>
                            <span class="payment-status-badge payment-status-declare_paye">
                                <?= ui_icon('price', 'icon icon-xs') ?>
                                <span>Paiement en espèces déclaré</span>
                            </span>
                            <span class="text-muted">Montant déclaré: <?= number_format((float) $paidAmount, 2) ?> TND · Déclaré le <?= htmlspecialchars($paidAt, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <?php if (in_array($r['statut'], ['en_attente', 'confirmee'], true)): ?>
                            <form action="<?= BASE_URL ?>/index.php?page=reservation&action=cancel"
                                  method="POST"
                                  class="inline-form"
                                  onsubmit="return confirm('Annuler cette réservation ?')">
                                <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <?= ui_icon('cancelled', 'icon icon-xs') ?>
                                    <span>Annuler</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
