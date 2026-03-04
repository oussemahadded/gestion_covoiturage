<?php
$pageTitle = htmlspecialchars($trajet['ville_depart'] . ' → ' . $trajet['ville_arrivee']);
require_once ROOT_PATH . '/views/layouts/header.php';
?>

<div class="container">
    <a href="<?= BASE_URL ?>/index.php?page=trajet" class="back-link">← Retour aux trajets</a>

    <div class="show-grid">
        <!-- Détail du trajet -->
        <div class="trajet-detail-card">
            <div class="detail-route">
                <div class="detail-city">
                    <span class="detail-dot from"></span>
                    <div>
                        <small>Départ</small>
                        <h2><?= htmlspecialchars($trajet['ville_depart']) ?></h2>
                    </div>
                </div>
                <div class="route-line-vertical"></div>
                <div class="detail-city">
                    <span class="detail-dot to"></span>
                    <div>
                        <small>Arrivée</small>
                        <h2><?= htmlspecialchars($trajet['ville_arrivee']) ?></h2>
                    </div>
                </div>
            </div>

            <div class="detail-meta-grid">
                <div class="meta-item">
                    <span class="meta-icon">📅</span>
                    <div>
                        <small>Date</small>
                        <strong><?= date('d/m/Y', strtotime($trajet['date_depart'])) ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">🕐</span>
                    <div>
                        <small>Heure</small>
                        <strong><?= substr($trajet['heure_depart'], 0, 5) ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">💺</span>
                    <div>
                        <small>Places restantes</small>
                        <strong><?= $trajet['places_restantes'] ?> / <?= $trajet['places_total'] ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">💰</span>
                    <div>
                        <small>Prix par personne</small>
                        <strong class="price-big"><?= number_format($trajet['prix'], 2) ?> TND</strong>
                    </div>
                </div>
            </div>

            <?php if (!empty($trajet['description'])): ?>
            <div class="detail-desc">
                <h3>Description</h3>
                <p><?= nl2br(htmlspecialchars($trajet['description'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar conducteur + réservation -->
        <div class="detail-sidebar">
            <!-- Conducteur -->
            <div class="driver-card">
                <div class="driver-avatar">
                    <?= strtoupper(substr(htmlspecialchars($trajet['prenom']), 0, 1)) ?>
                </div>
                <div class="driver-info">
                    <h3><?= htmlspecialchars($trajet['prenom'] . ' ' . $trajet['nom']) ?></h3>
                    <p>Conducteur</p>
                    <?php if ($avgRating > 0): ?>
                    <div class="star-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= round($avgRating) ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                        <span class="rating-num"><?= $avgRating ?>/5</span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] !== $trajet['conducteur_id']): ?>
                <a href="<?= BASE_URL ?>/index.php?page=message&action=conversation&contact=<?= $trajet['conducteur_id'] ?>"
                   class="btn btn-outline btn-sm btn-full" style="margin-top:.75rem;">💬 Contacter</a>
                <?php endif; ?>
            </div>

            <!-- Réservation -->
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'passager'): ?>
            <div class="booking-card">
                <?php if ($alreadyBooked): ?>
                    <div class="booking-done">✅ Vous avez déjà réservé ce trajet</div>
                <?php elseif ($trajet['places_restantes'] <= 0): ?>
                    <div class="booking-full">🚫 Complet</div>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>/index.php?page=reservation&action=book" method="POST">
                        <input type="hidden" name="trajet_id" value="<?= $trajet['id'] ?>">
                        <p class="booking-price"><?= number_format($trajet['prix'], 2) ?> TND <small>/ personne</small></p>
                        <button type="submit" class="btn btn-primary btn-full">🎫 Réserver ce trajet</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php elseif (!isset($_SESSION['user'])): ?>
            <div class="booking-card">
                <p>Connectez-vous pour réserver.</p>
                <a href="<?= BASE_URL ?>/index.php?page=auth&action=login" class="btn btn-primary btn-full">Se connecter</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Avis -->
    <section class="reviews-section">
        <h2>⭐ Avis des passagers (<?= count($avisList) ?>)</h2>

        <?php if (empty($avisList)): ?>
            <p class="empty-state">Aucun avis pour ce trajet.</p>
        <?php else: ?>
        <div class="reviews-list">
            <?php foreach ($avisList as $avis): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-avatar">
                        <?= strtoupper(substr(htmlspecialchars($avis['prenom']), 0, 1)) ?>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($avis['prenom'] . ' ' . $avis['nom']) ?></strong>
                        <div class="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $avis['note'] ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <small class="review-date"><?= date('d/m/Y', strtotime($avis['created_at'])) ?></small>
                </div>
                <?php if (!empty($avis['commentaire'])): ?>
                <p class="review-comment"><?= htmlspecialchars($avis['commentaire']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Formulaire avis -->
        <?php if ($canReview): ?>
        <div class="add-review-box">
            <h3>Laissez votre avis</h3>
            <a href="<?= BASE_URL ?>/index.php?page=avis&action=create&trajet_id=<?= $trajet['id'] ?>"
               class="btn btn-primary">✍ Écrire un avis</a>
        </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
