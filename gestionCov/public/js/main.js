/**
 * public/js/main.js
 * Interactions côté client — CHAYA3NI
 * Plateforme de covoiturage en Tunisie
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Navigation mobile ────────────────────────────────────────
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
        // Fermer sur clic extérieur
        document.addEventListener('click', e => {
            if (!navToggle.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('open');
            }
        });
    }

    // ── Menu utilisateur (dropdown) ──────────────────────────────
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', e => {
            e.stopPropagation();
            userDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => {
            userDropdown.classList.remove('open');
        });
    }

    // ── Auto-dismiss flash message ────────────────────────────────
    const flash = document.getElementById('flashMsg');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity .4s ease, height .4s ease';
            flash.style.opacity = '0';
            flash.style.overflow = 'hidden';
            flash.style.height = flash.offsetHeight + 'px';
            // Force reflow
            flash.offsetHeight;
            flash.style.height = '0';
            flash.style.padding = '0';
            setTimeout(() => flash.remove(), 400);
        }, 4500);
    }

    // ── Sélecteur de rôle (inscription) ─────────────────────────
    const roleOptions = document.querySelectorAll('.role-option');
    roleOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        if (radio && radio.checked) option.classList.add('selected');

        option.addEventListener('click', () => {
            roleOptions.forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');
        });
    });

    // ── Sélecteur d'étoiles (avis) ──────────────────────────────
    const starPicker = document.getElementById('starPicker');
    if (starPicker) {
        const stars = starPicker.querySelectorAll('.star-label');
        const inputs = starPicker.querySelectorAll('input[type="radio"]');

        inputs.forEach((input, i) => {
            input.addEventListener('change', () => highlightStars(i));
        });

        // Highlight on hover
        stars.forEach((star, i) => {
            star.addEventListener('mouseenter', () => highlightStars(i, true));
            star.addEventListener('mouseleave', () => {
                const checked = starPicker.querySelector('input:checked');
                if (checked) {
                    const idx = [...inputs].indexOf(checked);
                    highlightStars(idx);
                } else {
                    clearStars();
                }
            });
        });

        function highlightStars(index, hover = false) {
            stars.forEach((s, i) => {
                s.querySelector('.star-pick').style.color =
                    i <= index ? '#F59E0B' : '#D1D5DB';
            });
        }
        function clearStars() {
            stars.forEach(s => s.querySelector('.star-pick').style.color = '#D1D5DB');
        }
    }

    // ── Toggle visibilité mot de passe ───────────────────────────
    window.togglePwd = function (fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.type = field.type === 'password' ? 'text' : 'password';
    };

    // ── Auto-resize textarea (chat) ──────────────────────────────
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('input', () => {
            chatInput.style.height = 'auto';
            chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
        });
    }

    // ── Confirmation suppression ─────────────────────────────────
    // (aussi géré inline avec onsubmit pour compatibilité basique)
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('submit', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // ── Date minimum = aujourd'hui pour tous les champs date ──────
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(inp => {
        if (!inp.min) inp.min = today;
    });

    // ── Smooth scroll vers flashmsg si présent ────────────────────
    if (flash) {
        flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

});
