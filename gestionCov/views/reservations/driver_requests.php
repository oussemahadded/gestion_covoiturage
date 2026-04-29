<?php $pageTitle = 'Demandes de réservation'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

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
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Trajet</th>
                    <th>Date</th>
                    <th>Passager</th>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
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
                    $reservationPrice = $r['reservation_price'] ?? $r['prix_snapshot'] ?? $r['prix'] ?? 0;
                    $reservationDistance = $r['reservation_distance_km'] ?? null;
                    $reservationType = (string) ($r['reservation_point_type'] ?? '');
                    $pointLabel = $reservationType === 'prise_en_charge'
                        ? 'Point de prise en charge'
                        : ($reservationType === 'depose' ? 'Point de dépose' : '');
                    $pointIcon = $reservationType === 'prise_en_charge' ? 'departure' : 'arrival';
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($r['ville_depart'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="arrow-small">→</span>
                            <strong><?= htmlspecialchars($r['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($r['date_depart'])) ?><br>
                            <small><?= substr($r['heure_depart'], 0, 5) ?></small>
                        </td>
                        <td><?= htmlspecialchars($r['passager_prenom'] . ' ' . $r['passager_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if (!empty($r['passager_tel'])): ?>
                                <a href="tel:<?= htmlspecialchars($r['passager_tel'], ENT_QUOTES, 'UTF-8') ?>" class="contact-inline-link">
                                    <?= ui_icon('phone', 'icon icon-xs') ?>
                                    <span><?= htmlspecialchars($r['passager_tel'], ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/index.php?page=message&action=conversation&contact=<?= (int) $r['passager_id'] ?>" class="btn btn-outline btn-xs" style="margin-top:.25rem;">
                                <?= ui_icon('messages', 'icon icon-xs') ?>
                                <span>Message</span>
                            </a>
                        </td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($r['statut'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= ui_icon($statusIcon, 'icon icon-xs') ?>
                                <span><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                            <?php if ($pointLabel !== '' && isset($r['reservation_point_lat'], $r['reservation_point_lng'])): ?>
                                <br>
                                <small>
                                    <?= ui_icon($pointIcon, 'icon icon-xs') ?>
                                    <?= htmlspecialchars($pointLabel, ENT_QUOTES, 'UTF-8') ?>:
                                    <?= number_format((float) $r['reservation_point_lat'], 5) ?>,
                                    <?= number_format((float) $r['reservation_point_lng'], 5) ?>
                                </small>
                                <?php if ($reservationDistance !== null): ?>
                                    <br>
                                    <small>Distance facturée: <?= number_format((float) $reservationDistance, 2) ?> km</small>
                                <?php endif; ?>
                                <br>
                                <small>Prix estimé: <?= number_format((float) $reservationPrice, 2) ?> TND</small>
                            <?php endif; ?>
                            <?php if (($r['statut'] ?? '') === 'confirmee' && ($r['payment_status'] ?? '') === 'declare_paye'): ?>
                                <?php
                                $paidAmount = $r['paid_amount'] ?? $r['montant_estime'] ?? $r['prix'] ?? 0;
                                $paidAt = !empty($r['paid_at']) ? date('d/m/Y H:i', strtotime((string) $r['paid_at'])) : '-';
                                ?>
                                <br>
                                <span class="payment-status-badge payment-status-declare_paye">
                                    <?= ui_icon('price', 'icon icon-xs') ?>
                                    <span>Paiement en espèces déclaré</span>
                                </span>
                                <br>
                                <small>Montant déclaré: <?= number_format((float) $paidAmount, 2) ?> TND · Déclaré le <?= htmlspecialchars($paidAt, ENT_QUOTES, 'UTF-8') ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <?php if ($r['statut'] === 'en_attente'): ?>
                                <form action="<?= BASE_URL ?>/index.php?page=reservation&action=updateStatus" method="POST" class="inline-form">
                                    <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                                    <input type="hidden" name="statut" value="confirmee">
                                    <button type="submit" class="btn btn-success btn-xs">
                                        <?= ui_icon('success', 'icon icon-xs') ?>
                                        <span>Confirmer</span>
                                    </button>
                                </form>
                                <form action="<?= BASE_URL ?>/index.php?page=reservation&action=updateStatus" method="POST" class="inline-form">
                                    <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                                    <input type="hidden" name="statut" value="refusee">
                                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Refuser cette réservation ?')">
                                        <?= ui_icon('refused', 'icon icon-xs') ?>
                                        <span>Refuser</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
