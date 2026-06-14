/**
 * public/js/rewards.js
 * Reward system animations & interactions — CHAYA3NI
 */

(function () {
    'use strict';

    /* ── Utility: count-up animation ──────────────────────── */
    function animateCountUp(el, target, duration) {
        if (!el) return;
        const start     = performance.now();
        const formatted = (n) => Math.round(n).toLocaleString('fr-FR');

        function step(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatted(eased * target);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    /* ── Utility: animate progress bar width ─────────────── */
    function animateProgressBar(el, targetPct, delay) {
        if (!el) return;
        setTimeout(() => {
            el.style.width = targetPct + '%';
        }, delay || 0);
    }

    /* ── Utility: IntersectionObserver trigger ───────────── */
    function onVisible(el, cb) {
        if (!el) return;
        if ('IntersectionObserver' in window) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        cb();
                        obs.unobserve(el);
                    }
                });
            }, { threshold: 0.15 });
            obs.observe(el);
        } else {
            cb();
        }
    }

    /* ── Conducteur Dashboard: main progress bar & counter ─ */
    const mainBar     = document.getElementById('mainProgressBar');
    const ptsCounter  = document.getElementById('conducteurPoints');

    if (mainBar) {
        const pct = parseFloat(mainBar.dataset.pct || '0');
        onVisible(mainBar, () => {
            animateProgressBar(mainBar, pct, 300);
        });
    }

    if (ptsCounter) {
        const target = parseInt(ptsCounter.dataset.target || '0', 10);
        onVisible(ptsCounter, () => {
            animateCountUp(ptsCounter, target, 1400);
        });
    }

    /* ── Admin Table: animate mini progress bars on scroll ─ */
    document.querySelectorAll('.rw-mini-bar-fill').forEach(bar => {
        const pct = parseFloat(bar.dataset.pct || '0');
        onVisible(bar, () => {
            animateProgressBar(bar, pct, 200);
        });
    });

    /* ── Admin: live search debounce ───────────────────────── */
    const searchInput = document.getElementById('rewardSearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const form = document.getElementById('rewardFilterForm');
                if (form) form.submit();
            }, 500);
        });
    }

    /* ── Admin Table: row hover glow ──────────────────────── */
    document.querySelectorAll('.rw-table-row').forEach(row => {
        row.addEventListener('mouseenter', () => {
            row.style.transition = 'background .15s';
        });
    });

    /* ── Flash auto-dismiss (if not already handled) ──────── */
    const flash = document.getElementById('flashMsg');
    if (flash && !flash.dataset.rwHandled) {
        flash.dataset.rwHandled = '1';
        setTimeout(() => {
            flash.style.transition = 'opacity .5s, transform .5s';
            flash.style.opacity    = '0';
            flash.style.transform  = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }, 4000);
    }

    /* ── Roadmap items: staggered entrance animation ──────── */
    const roadmapItems = document.querySelectorAll('.rw-roadmap-item');
    roadmapItems.forEach((item, i) => {
        item.style.opacity   = '0';
        item.style.transform = 'translateX(20px)';
        item.style.transition = 'opacity .4s ease, transform .4s ease';
        onVisible(item, () => {
            setTimeout(() => {
                item.style.opacity   = '1';
                item.style.transform = 'translateX(0)';
            }, i * 80);
        });
    });

    /* ── Stat cards: staggered entrance ──────────────────── */
    document.querySelectorAll('.rw-stat-card').forEach((card, i) => {
        card.style.opacity   = '0';
        card.style.transform = 'translateY(16px)';
        card.style.transition = 'opacity .4s ease, transform .4s ease';
        onVisible(card, () => {
            setTimeout(() => {
                card.style.opacity   = '1';
                card.style.transform = 'translateY(0)';
            }, i * 70);
        });
    });

}());
