<?php
$pageTitle = htmlspecialchars($trajet['ville_depart'] . ' → ' . $trajet['ville_arrivee'], ENT_QUOTES, 'UTF-8');
require_once ROOT_PATH . '/views/layouts/header.php';
?>

<div class="container">
    <a href="<?= BASE_URL ?>/index.php?page=trajet" class="back-link">
        <?= ui_icon('arrow-left', 'icon icon-sm') ?>
        <span>Retour aux trajets</span>
    </a>

    <div class="show-grid">
        <article class="trajet-detail-card">
            <div class="detail-route">
                <div class="detail-city">
                    <span class="detail-dot from"></span>
                    <div>
                        <small>Départ</small>
                        <h2><?= htmlspecialchars($trajet['ville_depart'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                </div>
                <div class="route-line-vertical"></div>
                <div class="detail-city">
                    <span class="detail-dot to"></span>
                    <div>
                        <small>Arrivée</small>
                        <h2><?= htmlspecialchars($trajet['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                </div>
            </div>

            <div class="detail-meta-grid">
                <div class="meta-item">
                    <span class="meta-icon"><?= ui_icon('calendar', 'icon icon-sm') ?></span>
                    <div>
                        <small>Date</small>
                        <strong><?= date('d/m/Y', strtotime($trajet['date_depart'])) ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <span class="meta-icon"><?= ui_icon('clock', 'icon icon-sm') ?></span>
                    <div>
                        <small>Heure</small>
                        <strong><?= substr($trajet['heure_depart'], 0, 5) ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <span class="meta-icon"><?= ui_icon('seats', 'icon icon-sm') ?></span>
                    <div>
                        <small>Places restantes</small>
                        <strong><?= (int) $trajet['places_restantes'] ?> / <?= (int) $trajet['places_total'] ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <span class="meta-icon"><?= ui_icon('price', 'icon icon-sm') ?></span>
                    <div>
                        <small>Prix par personne</small>
                        <strong class="price-big"><?= number_format((float) $trajet['prix'], 2) ?> TND</strong>
                    </div>
                </div>
            </div>

            <?php if (!empty($trajet['description'])): ?>
                <div class="detail-desc">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($trajet['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
            <?php endif; ?>
        </article>

        <aside class="detail-sidebar">
            <div class="driver-card">
                <div class="driver-avatar">
                    <?= strtoupper(substr(htmlspecialchars($trajet['prenom'], ENT_QUOTES, 'UTF-8'), 0, 1)) ?>
                </div>
                <div class="driver-info">
                    <h3><?= htmlspecialchars($trajet['prenom'] . ' ' . $trajet['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p>Conducteur</p>
                    <?php if ($avgRating > 0): ?>
                        <div class="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= round($avgRating) ? 'filled' : '' ?>">
                                    <?= ui_icon('star', 'icon icon-xs') ?>
                                </span>
                            <?php endfor; ?>
                            <span class="rating-num"><?= htmlspecialchars((string) $avgRating, ENT_QUOTES, 'UTF-8') ?>/5</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user']) && (int) $_SESSION['user']['id'] !== (int) $trajet['conducteur_id']): ?>
                    <a href="<?= BASE_URL ?>/index.php?page=message&action=conversation&contact=<?= (int) $trajet['conducteur_id'] ?>" class="btn btn-outline btn-sm btn-full" style="margin-top:.75rem;">
                        <?= ui_icon('messages', 'icon icon-sm') ?>
                        <span>Contacter</span>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (
                isset($_SESSION['user'])
                && in_array($_SESSION['user']['role'], ['etudiant', 'professeur'], true)
                && (int) $_SESSION['user']['id'] !== (int) $trajet['conducteur_id']
            ): ?>
                <div class="booking-card">
                    <?php if ($currentReservation): ?>
                        <?php
                        $status = $currentReservation['statut'];
                        $reservationState = match ($status) {
                            'confirmee' => [
                                'class' => 'reservation-state-confirmee',
                                'icon' => 'success',
                                'title' => 'Réservation confirmée',
                                'text' => 'Votre place a été validée par le conducteur.',
                            ],
                            'refusee' => [
                                'class' => 'reservation-state-refusee',
                                'icon' => 'refused',
                                'title' => 'Réservation refusée',
                                'text' => 'Le conducteur a refusé votre demande.',
                            ],
                            'annulee' => [
                                'class' => 'reservation-state-annulee',
                                'icon' => 'cancelled',
                                'title' => 'Réservation annulée',
                                'text' => 'Vous avez annulé cette réservation.',
                            ],
                            default => [
                                'class' => 'reservation-state-attente',
                                'icon' => 'pending',
                                'title' => 'Demande de réservation envoyée',
                                'text' => 'En attente de confirmation par le conducteur.',
                            ],
                        };
                        ?>
                        <div class="reservation-state-card <?= $reservationState['class'] ?>">
                            <div class="reservation-state-title">
                                <?= ui_icon($reservationState['icon'], 'icon icon-sm') ?>
                                <strong><?= htmlspecialchars($reservationState['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <p><?= htmlspecialchars($reservationState['text'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php elseif ((int) $trajet['places_restantes'] <= 0): ?>
                        <div class="reservation-state-card reservation-state-refusee">
                            <div class="reservation-state-title">
                                <?= ui_icon('warning', 'icon icon-sm') ?>
                                <strong>Trajet complet</strong>
                            </div>
                            <p>Aucune place n'est disponible pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>/index.php?page=reservation&action=book" method="POST">
                            <input type="hidden" name="trajet_id" value="<?= (int) $trajet['id'] ?>">
                            <p class="booking-price"><?= number_format((float) $trajet['prix'], 2) ?> TND <small>/ personne</small></p>
                            <button type="submit" class="btn btn-primary btn-full">
                                <?= ui_icon('reservation', 'icon icon-sm') ?>
                                <span>Réserver ce trajet</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php elseif (isset($_SESSION['user']) && (int) $_SESSION['user']['id'] === (int) $trajet['conducteur_id']): ?>
                <div class="booking-card">
                    <p>Vous avez proposé ce trajet.</p>
                    <a href="<?= BASE_URL ?>/index.php?page=trajet&action=myTrajets" class="btn btn-outline btn-full">
                        <?= ui_icon('car', 'icon icon-sm') ?>
                        <span>Mes trajets</span>
                    </a>
                </div>
            <?php elseif (!isset($_SESSION['user'])): ?>
                <div class="booking-card">
                    <p>Connectez-vous pour réserver.</p>
                    <a href="<?= BASE_URL ?>/index.php?page=auth&action=login" class="btn btn-primary btn-full">
                        <?= ui_icon('login', 'icon icon-sm') ?>
                        <span>Se connecter</span>
                    </a>
                </div>
            <?php endif; ?>
        </aside>
    </div>

    <section class="reviews-section">
        <h2>
            <?= ui_icon('star', 'icon icon-sm') ?>
            <span>Avis des passagers (<?= count($avisList) ?>)</span>
        </h2>

        <?php if (empty($avisList)): ?>
            <p class="empty-state">Aucun avis pour ce trajet.</p>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($avisList as $avis): ?>
                    <article class="review-card">
                        <div class="review-header">
                            <div class="review-avatar">
                                <?= strtoupper(substr(htmlspecialchars($avis['prenom'], ENT_QUOTES, 'UTF-8'), 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($avis['prenom'] . ' ' . $avis['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= (int) $avis['note'] ? 'filled' : '' ?>">
                                            <?= ui_icon('star', 'icon icon-xs') ?>
                                        </span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <small class="review-date"><?= date('d/m/Y', strtotime($avis['created_at'])) ?></small>
                        </div>
                        <?php if (!empty($avis['commentaire'])): ?>
                            <p class="review-comment"><?= htmlspecialchars($avis['commentaire'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($canReview): ?>
            <div class="add-review-box">
                <h3>Laissez votre avis</h3>
                <a href="<?= BASE_URL ?>/index.php?page=avis&action=create&trajet_id=<?= (int) $trajet['id'] ?>" class="btn btn-primary">
                    <?= ui_icon('edit', 'icon icon-sm') ?>
                    <span>Écrire un avis</span>
                </a>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
