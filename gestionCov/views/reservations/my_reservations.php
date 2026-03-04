<?php $pageTitle = 'Mes réservations'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <h1 class="page-title">🎫 Mes réservations</h1>

    <?php if (empty($reservations)): ?>
    <div class="empty-state-box">
        <span style="font-size:3rem;">🎫</span>
        <p>Vous n'avez pas encore de réservation.</p>
        <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-primary">Chercher un trajet</a>
    </div>
    <?php else: ?>
    <div class="reservations-list">
        <?php foreach ($reservations as $r): ?>
        <div class="reservation-card">
            <div class="res-route">
                <span class="city"><?= htmlspecialchars($r['ville_depart']) ?></span>
                <span class="arrow">→</span>
                <span class="city"><?= htmlspecialchars($r['ville_arrivee']) ?></span>
            </div>
            <div class="res-meta">
                <span>📅 <?= date('d/m/Y', strtotime($r['date_depart'])) ?></span>
                <span>🕐 <?= substr($r['heure_depart'], 0, 5) ?></span>
                <span>💰 <?= number_format($r['prix'], 2) ?> TND</span>
                <span>🚗 <?= htmlspecialchars($r['conducteur_prenom'] . ' ' . $r['conducteur_nom']) ?></span>
                <?php if ($r['conducteur_tel']): ?>
                    <span>📞 <?= htmlspecialchars($r['conducteur_tel']) ?></span>
                <?php endif; ?>
            </div>
            <div class="res-footer">
                <span class="status-badge status-<?= $r['statut'] ?>">
                    <?php match($r['statut']) {
                        'en_attente' => print('⏳ En attente'),
                        'confirmee'  => print('✅ Confirmée'),
                        'annulee'    => print('❌ Annulée'),
                        'refusee'    => print('🚫 Refusée'),
                        default      => print(htmlspecialchars($r['statut']))
                    }; ?>
                </span>
                <?php if (in_array($r['statut'], ['en_attente', 'confirmee'])): ?>
                <form action="<?= BASE_URL ?>/index.php?page=reservation&action=cancel" method="POST"
                      class="inline-form"
                      onsubmit="return confirm('Annuler cette réservation ?')">
                    <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs">✕ Annuler</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
