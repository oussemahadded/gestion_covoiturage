</main>

<!-- ── Footer ──────────────────────────────────────────────────────────── -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <span class="brand-icon">🇹🇳</span>
            <span class="brand-name">CHAYA3NI</span>
            <p>Le covoiturage en Tunisie, simple et économique.</p>
        </div>
        <div class="footer-links">
            <h4>Navigation</h4>
            <a href="<?= BASE_URL ?>/index.php?page=trajet">Rechercher un trajet</a>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'conducteur'): ?>
                <a href="<?= BASE_URL ?>/index.php?page=trajet&action=create">Proposer un trajet</a>
            <?php endif; ?>
        </div>
        <div class="footer-info">
            <h4>Projet PFA — Tunisie</h4>
            <p>Cycle Préparatoire Intégré — 2ème année</p>
            <p>Gestion de Covoiturage · Tunisie 🇹🇳</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> CHAYA3NI — Plateforme de covoiturage en Tunisie — Tous droits réservés</p>
    </div>
</footer>

<script src="<?= BASE_URL ?>/public/js/main.js"></script>
</body>
</html>
