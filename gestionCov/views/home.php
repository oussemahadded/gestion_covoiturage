<?php $pageTitle = 'Accueil'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<section class="hero page-hero">
    <div class="hero-content">
        <h1 class="hero-title">Voyagez <span class="gradient-text">ensemble</span><br>en Tunisie.</h1>
        <p class="hero-subtitle">
            CHAYA3NI connecte les étudiants et professeurs de Sesame avec des conducteurs pour partager les trajets.
            Étudiants et professeurs : adresse @sesame.com.tn. Conducteurs : adresse email valide.
        </p>

        <form action="<?= BASE_URL ?>/index.php" method="GET" class="hero-search filter-card date-fr-form">
            <input type="hidden" name="page" value="trajet">
            <input type="hidden" name="action" value="index">
            <div class="hero-search-fields">
                <div class="search-field">
                    <label for="home_depart">Départ</label>
                    <input type="text" id="home_depart" name="depart" placeholder="Ex: Tunis">
                </div>
                <div class="search-field">
                    <label for="home_arrivee">Arrivée</label>
                    <input type="text" id="home_arrivee" name="arrivee" placeholder="Ex: Sesame">
                </div>
                <div class="search-field date-fr-group">
                    <label for="home_date_display">Date</label>
                    <input type="text"
                           id="home_date_display"
                           name="date_display"
                           class="date-fr-input"
                           placeholder="jj/mm/aaaa"
                           inputmode="numeric"
                           maxlength="10"
                           autocomplete="off"
                           required>
                    <input type="hidden" id="home_date" name="date">
                </div>
                <button type="submit" class="btn btn-primary btn-search">
                    <?= ui_icon('search', 'icon icon-sm') ?>
                    <span>Rechercher</span>
                </button>
            </div>
        </form>
    </div>

    <div class="hero-visual" aria-hidden="true">
        <div class="hero-car-animation">
            <?= ui_icon('car', 'icon icon-xl') ?>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="stats-grid kpi-grid">
            <div class="stat-card kpi-card metric-card">
                <span class="stat-icon"><?= ui_icon('route', 'icon icon-lg') ?></span>
                <span class="stat-number">100+</span>
                <span class="stat-label">Trajets publiés</span>
            </div>
            <div class="stat-card kpi-card metric-card">
                <span class="stat-icon"><?= ui_icon('users', 'icon icon-lg') ?></span>
                <span class="stat-number">500+</span>
                <span class="stat-label">Utilisateurs actifs</span>
            </div>
            <div class="stat-card kpi-card metric-card">
                <span class="stat-icon"><?= ui_icon('car', 'icon icon-lg') ?></span>
                <span class="stat-number">24+</span>
                <span class="stat-label">Gouvernorats couverts</span>
            </div>
            <div class="stat-card kpi-card metric-card">
                <span class="stat-icon"><?= ui_icon('star', 'icon icon-lg') ?></span>
                <span class="stat-number">4.8/5</span>
                <span class="stat-label">Satisfaction moyenne</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Trajets récents</h2>
        <div class="trajets-grid">
            <?php if (empty($trajets)): ?>
                <p class="empty-state empty-state-polished">Aucun trajet disponible pour le moment.</p>
            <?php else: ?>
                <?php foreach ($trajets as $t): ?>
                    <article class="trajet-card route-card">
                        <div class="trajet-route route-timeline">
                            <span class="city city-from"><?= htmlspecialchars($t['ville_depart'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="route-arrow"><?= ui_icon('arrow-right', 'icon icon-sm') ?></span>
                            <span class="city city-to"><?= htmlspecialchars($t['ville_arrivee'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="trajet-meta meta-muted">
                            <span><?= ui_icon('calendar', 'icon icon-xs') ?> <?= date('d/m/Y', strtotime($t['date_depart'])) ?></span>
                            <span><?= ui_icon('clock', 'icon icon-xs') ?> <?= substr($t['heure_depart'], 0, 5) ?></span>
                            <span><?= ui_icon('seats', 'icon icon-xs') ?> <?= (int) $t['places_restantes'] ?> place(s)</span>
                        </div>
                        <div class="trajet-footer">
                            <span class="trajet-price"><?= number_format((float) $t['prix'], 2) ?> TND</span>
                            <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= (int) $t['id'] ?>" class="btn btn-outline btn-sm">
                                <?= ui_icon('view', 'icon icon-xs') ?>
                                <span>Voir</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center section-actions">
            <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-primary">
                <?= ui_icon('route', 'icon icon-sm') ?>
                <span>Voir tous les trajets</span>
            </a>
        </div>
    </div>
</section>

<section class="how-section">
    <div class="container">
        <h2 class="section-title">Comment ça marche ?</h2>
        <div class="steps-grid">
            <div class="step app-card">
                <div class="step-number">1</div>
                <h3>Créez votre compte</h3>
                <p>Inscrivez-vous en tant qu'étudiant, professeur ou conducteur.</p>
            </div>
            <div class="step app-card">
                <div class="step-number">2</div>
                <h3>Trouvez le bon trajet</h3>
                <p>Recherchez un départ ou une arrivée autour de Sesame.</p>
            </div>
            <div class="step app-card">
                <div class="step-number">3</div>
                <h3>Voyagez sereinement</h3>
                <p>Le conducteur valide les demandes puis vous prenez la route ensemble.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
