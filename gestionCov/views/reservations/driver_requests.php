<?php $pageTitle = 'Demandes de réservation'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <h1 class="page-title">📨 Demandes de réservation</h1>

    <?php if (empty($requests)): ?>
    <div class="empty-state-box">
        <span style="font-size:3rem;">📬</span>
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
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($r['ville_depart']) ?></strong>
                        <span class="arrow-small">→</span>
                        <strong><?= htmlspecialchars($r['ville_arrivee']) ?></strong>
                    </td>
                    <td><?= date('d/m/Y', strtotime($r['date_depart'])) ?><br>
                        <small><?= substr($r['heure_depart'], 0, 5) ?></small></td>
                    <td><?= htmlspecialchars($r['passager_prenom'] . ' ' . $r['passager_nom']) ?></td>
                    <td>
                        <?php if ($r['passager_tel']): ?>
                            <a href="tel:<?= htmlspecialchars($r['passager_tel']) ?>">
                                📞 <?= htmlspecialchars($r['passager_tel']) ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/index.php?page=message&action=conversation&contact=<?= $r['passager_id'] ?>"
                           class="btn btn-outline btn-xs" style="margin-top:.25rem;">💬</a>
                    </td>
                    <td>
                        <span class="status-badge status-<?= $r['statut'] ?>">
                            <?php match($r['statut']) {
                                'en_attente' => print('⏳ En attente'),
                                'confirmee'  => print('✅ Confirmée'),
                                'annulee'    => print('❌ Annulée'),
                                'refusee'    => print('🚫 Refusée'),
                                default      => print(htmlspecialchars($r['statut']))
                            }; ?>
                        </span>
                    </td>
                    <td class="table-actions">
                        <?php if ($r['statut'] === 'en_attente'): ?>
                        <form action="<?= BASE_URL ?>/index.php?page=reservation&action=updateStatus" method="POST" class="inline-form">
                            <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="statut" value="confirmee">
                            <button type="submit" class="btn btn-success btn-xs">✅ Confirmer</button>
                        </form>
                        <form action="<?= BASE_URL ?>/index.php?page=reservation&action=updateStatus" method="POST" class="inline-form">
                            <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="statut" value="refusee">
                            <button type="submit" class="btn btn-danger btn-xs"
                                    onclick="return confirm('Refuser cette réservation ?')">🚫 Refuser</button>
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
