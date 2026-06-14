</main>

<?php if (isset($_SESSION['user']) && $_SESSION['user']): ?>
    </div> <!-- Closes .app-wrapper -->
</div> <!-- Closes .app-layout -->
<?php else: ?>
    <footer class="footer footer-minimal">
        <p>&copy; <?= date('Y') ?> SesameRide - Tous droits r&eacute;serv&eacute;s</p>
    </footer>
<?php endif; ?>

<script src="<?= BASE_URL ?>/public/js/main.js"></script>
<script>
// Mobile Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openSidebarBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const sidebar = document.getElementById('appSidebar');

    if (openBtn && closeBtn && sidebar) {
        openBtn.addEventListener('click', () => sidebar.classList.add('is-open'));
        closeBtn.addEventListener('click', () => sidebar.classList.remove('is-open'));
    }
});
</script>
</body>
</html>
