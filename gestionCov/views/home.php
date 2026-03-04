<?php $pageTitle = 'Accueil'; require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<!-- ── Hero ─────────────────────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Voyagez <span class="gradient-text">ensemble</span><br>à travers la Tunisie.</h1>
        <p class="hero-subtitle">
            CHAYA3NI vous connecte avec des conducteurs et passagers partout en Tunisie.
            Économique, écologique et convivial.
        </p>

        <!-- Mini formulaire de recherche rapide -->
        <form action="<?= BASE_URL ?>/index.php" method="GET" class="hero-search">
            <input type="hidden" name="page"   value="trajet">
            <input type="hidden" name="action" value="index">
            <div class="hero-search-fields">
                <div class="search-field">
                    <label>Départ</label>
                    <input type="text" name="depart" placeholder="Ex: Tunis">
                </div>
                <div class="search-field">
                    <label>Arrivée</label>
                    <input type="text" name="arrivee" placeholder="Ex: Sfax">
                </div>
                <div class="search-field">
                    <label>Date</label>
                    <input type="date" name="date" min="<?= date('Y-m-d') ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-search">🔍 Rechercher</button>
            </div>
        </form>
    </div>
    <div class="hero-visual">
        <div class="hero-car-animation">🚗</div>
    </div>
</section>

<!-- ── Statistiques ──────────────────────────────────────────────────────── -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">🚗</span>
                <span class="stat-number">100+</span>
                <span class="stat-label">Trajets disponibles</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">👥</span>
                <span class="stat-number">500+</span>
                <span class="stat-label">Utilisateurs actifs</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🇹🇳</span>
                <span class="stat-number">24+</span>
                <span class="stat-label">Gouvernorats couverts</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⭐</span>
                <span class="stat-number">4.8/5</span>
                <span class="stat-label">Note moyenne</span>
            </div>
        </div>
    </div>
</section>

<!-- ── Derniers trajets ──────────────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Trajets récents</h2>
        <div class="trajets-grid">
            <?php if (empty($trajets)): ?>
                <p class="empty-state">Aucun trajet disponible pour le moment.</p>
            <?php else: ?>
                <?php foreach ($trajets as $t): ?>
                <div class="trajet-card">
                    <div class="trajet-route">
                        <span class="city city-from"><?= htmlspecialchars($t['ville_depart']) ?></span>
                        <span class="route-arrow">→</span>
                        <span class="city city-to"><?= htmlspecialchars($t['ville_arrivee']) ?></span>
                    </div>
                    <div class="trajet-meta">
                        <span>📅 <?= date('d/m/Y', strtotime($t['date_depart'])) ?></span>
                        <span>🕐 <?= substr($t['heure_depart'], 0, 5) ?></span>
                        <span>💺 <?= $t['places_restantes'] ?> place(s)</span>
                    </div>
                    <div class="trajet-footer">
                        <span class="trajet-price"><?= number_format($t['prix'], 2) ?> TND</span>
                        <a href="<?= BASE_URL ?>/index.php?page=trajet&action=show&id=<?= $t['id'] ?>"
                           class="btn btn-outline btn-sm">Voir →</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center" style="margin-top:2rem;">
            <a href="<?= BASE_URL ?>/index.php?page=trajet" class="btn btn-primary">Voir tous les trajets</a>
        </div>
    </div>
</section>

<!-- ── Comment ça marche ────────────────────────────────────────────────── -->
<section class="how-section">
    <div class="container">
        <h2 class="section-title">Comment ça marche ?</h2>
        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Inscrivez-vous</h3>
                <p>Créez votre compte gratuitement en tant que passager ou conducteur tunisien.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Trouvez un trajet</h3>
                <p>Recherchez entre Tunis, Sfax, Sousse, Monastir et bien d'autres villes.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Voyagez ensemble</h3>
                <p>Le conducteur confirme — retrouvez-vous au point de départ et partez !</p>
            </div>
        </div>
    </div>
</section>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
