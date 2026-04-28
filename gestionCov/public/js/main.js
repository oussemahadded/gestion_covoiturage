/**
 * public/js/main.js
 * Interactions côté client — CHAYA3NI
 */

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initUserDropdown();
    initFlashMessage();
    initRoleSelector();
    initStarPicker();
    initPasswordToggle();
    initChatTextarea();
    initConfirmForms();
    initDateFrInputs();
    initTripDirectionSelector();
});

function initMobileNav() {
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    if (!navToggle || !navLinks) return;

    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
    });

    document.addEventListener('click', (event) => {
        if (!navToggle.contains(event.target) && !navLinks.contains(event.target)) {
            navLinks.classList.remove('open');
        }
    });
}

function initUserDropdown() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (!userMenuBtn || !userDropdown) return;

    userMenuBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        userDropdown.classList.toggle('open');
    });

    document.addEventListener('click', () => {
        userDropdown.classList.remove('open');
    });
}

function initFlashMessage() {
    const flash = document.getElementById('flashMsg');
    if (!flash) return;

    const closeBtn = flash.querySelector('.flash-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            flash.remove();
        });
    }

    setTimeout(() => {
        if (!flash.isConnected) return;
        flash.style.transition = 'opacity .4s ease, height .4s ease';
        flash.style.opacity = '0';
        flash.style.overflow = 'hidden';
        flash.style.height = flash.offsetHeight + 'px';
        flash.offsetHeight;
        flash.style.height = '0';
        flash.style.padding = '0';
        setTimeout(() => flash.remove(), 400);
    }, 4500);

    flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function initRoleSelector() {
    const roleOptions = document.querySelectorAll('.role-option');
    if (!roleOptions.length) return;

    roleOptions.forEach((option) => {
        const radio = option.querySelector('input[type="radio"]');
        if (radio && radio.checked) option.classList.add('selected');

        option.addEventListener('click', () => {
            roleOptions.forEach((opt) => opt.classList.remove('selected'));
            option.classList.add('selected');
        });
    });
}

function initStarPicker() {
    const starPicker = document.getElementById('starPicker');
    if (!starPicker) return;

    const stars = [...starPicker.querySelectorAll('.star-label')];
    const inputs = [...starPicker.querySelectorAll('input[type="radio"]')];

    const paint = (index) => {
        stars.forEach((star, i) => {
            const icon = star.querySelector('.star-pick');
            if (!icon) return;
            icon.style.color = i <= index ? '#F59E0B' : '#D1D5DB';
        });
    };

    const reset = () => {
        const checked = starPicker.querySelector('input:checked');
        if (!checked) {
            paint(-1);
            return;
        }
        paint(inputs.indexOf(checked));
    };

    inputs.forEach((input, index) => {
        input.addEventListener('change', () => paint(index));
    });

    stars.forEach((star, index) => {
        star.addEventListener('mouseenter', () => paint(index));
        star.addEventListener('mouseleave', reset);
    });

    reset();
}

function initPasswordToggle() {
    const buttons = document.querySelectorAll('.toggle-pwd[data-toggle-target]');
    if (!buttons.length) return;

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-toggle-target');
            const field = targetId ? document.getElementById(targetId) : null;
            if (!field) return;

            const isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';
            button.classList.toggle('is-visible', isPassword);
        });
    });

    // Compatibilité avec les anciens templates
    window.togglePwd = (fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.type = field.type === 'password' ? 'text' : 'password';
    };
}

function initChatTextarea() {
    const chatInput = document.getElementById('chatInput');
    if (!chatInput) return;

    chatInput.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = `${Math.min(chatInput.scrollHeight, 120)}px`;
    });
}

function initConfirmForms() {
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('submit', (event) => {
            if (!confirm(el.dataset.confirm || 'Confirmer ?')) {
                event.preventDefault();
            }
        });
    });
}

function initDateFrInputs() {
    const dateGroups = document.querySelectorAll('.date-fr-group');
    if (!dateGroups.length) return;

    dateGroups.forEach((group) => {
        const displayInput = group.querySelector('.date-fr-input');
        const hiddenInput = group.querySelector('input[type="hidden"]');
        if (!displayInput || !hiddenInput) return;

        if (!displayInput.value && hiddenInput.value) {
            const fr = isoDateToFr(hiddenInput.value);
            if (fr) displayInput.value = fr;
        }

        displayInput.addEventListener('input', () => {
            displayInput.value = autoFormatDateFr(displayInput.value);
            displayInput.setCustomValidity('');
        });

        displayInput.addEventListener('blur', () => {
            const value = displayInput.value.trim();
            if (!value) {
                displayInput.setCustomValidity('');
                return;
            }

            const isoDate = frDateToIso(value);
            if (!isoDate) {
                displayInput.setCustomValidity('Veuillez saisir une date valide au format jj/mm/aaaa.');
            } else {
                displayInput.setCustomValidity('');
            }
        });
    });

    document.querySelectorAll('form.date-fr-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            let hasError = false;
            const groups = form.querySelectorAll('.date-fr-group');

            groups.forEach((group) => {
                const displayInput = group.querySelector('.date-fr-input');
                const hiddenInput = group.querySelector('input[type="hidden"]');
                if (!displayInput || !hiddenInput) return;

                const value = displayInput.value.trim();
                if (!value) {
                    hiddenInput.value = '';
                    displayInput.setCustomValidity('');
                    return;
                }

                const isoDate = frDateToIso(value);
                if (!isoDate) {
                    hasError = true;
                    displayInput.setCustomValidity('Veuillez saisir une date valide au format jj/mm/aaaa.');
                    displayInput.reportValidity();
                    return;
                }

                hiddenInput.value = isoDate;
                displayInput.setCustomValidity('');
            });

            if (hasError) {
                event.preventDefault();
            }
        });
    });
}

function autoFormatDateFr(rawValue) {
    const digits = rawValue.replace(/\D/g, '').slice(0, 8);
    if (digits.length <= 2) return digits;
    if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
}

function frDateToIso(frDate) {
    const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(frDate);
    if (!match) return '';

    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    const dateObj = new Date(year, month - 1, day);

    if (
        dateObj.getFullYear() !== year ||
        dateObj.getMonth() !== month - 1 ||
        dateObj.getDate() !== day
    ) {
        return '';
    }

    const monthPadded = String(month).padStart(2, '0');
    const dayPadded = String(day).padStart(2, '0');
    return `${year}-${monthPadded}-${dayPadded}`;
}

function isoDateToFr(isoDate) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(isoDate);
    if (!match) return '';
    return `${match[3]}/${match[2]}/${match[1]}`;
}

function initTripDirectionSelector() {
    const selectors = document.querySelectorAll('.trip-direction-selector');
    if (!selectors.length) return;

    const departInput = document.getElementById('ville_depart');
    const arriveeInput = document.getElementById('ville_arrivee');
    if (!departInput || !arriveeInput) return;

    const applyDirection = (direction, preserveOpposite = true) => {
        const departValue = departInput.value.trim().toLowerCase();
        const arriveeValue = arriveeInput.value.trim().toLowerCase();

        if (direction === 'depuis_sesame') {
            departInput.value = 'Sesame';
            departInput.readOnly = true;
            departInput.classList.add('input-locked');

            arriveeInput.readOnly = false;
            arriveeInput.classList.remove('input-locked');
            if (!preserveOpposite && arriveeValue === 'sesame') {
                arriveeInput.value = '';
            }
        } else {
            arriveeInput.value = 'Sesame';
            arriveeInput.readOnly = true;
            arriveeInput.classList.add('input-locked');

            departInput.readOnly = false;
            departInput.classList.remove('input-locked');
            if (!preserveOpposite && departValue === 'sesame') {
                departInput.value = '';
            }
        }
    };

    selectors.forEach((selector) => {
        const options = selector.querySelectorAll('.direction-option');
        const radios = selector.querySelectorAll('input[type="radio"][name="direction"]');
        if (!radios.length) return;

        const refreshSelectedStyle = () => {
            options.forEach((option) => {
                const radio = option.querySelector('input[type="radio"]');
                option.classList.toggle('selected', !!radio?.checked);
            });
        };

        const checkedRadio = selector.querySelector('input[type="radio"][name="direction"]:checked');
        const defaultDirection = checkedRadio?.value || selector.getAttribute('data-default-direction') || 'vers_sesame';
        applyDirection(defaultDirection, true);
        refreshSelectedStyle();

        radios.forEach((radio) => {
            radio.addEventListener('change', () => {
                if (!radio.checked) return;
                applyDirection(radio.value, false);
                refreshSelectedStyle();
            });
        });
    });
}
