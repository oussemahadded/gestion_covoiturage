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
    initTripMap();
    initReservationPointPicker();
    initTripPreviewMaps();
    initDriverRequestMaps();
    initSortableTables();
});

function initMobileNav() {
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    if (!navToggle || !navLinks) return;

    const syncMenuState = () => {
        navToggle.setAttribute('aria-expanded', navLinks.classList.contains('open') ? 'true' : 'false');
    };

    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
        syncMenuState();
    });

    document.addEventListener('click', (event) => {
        if (!navToggle.contains(event.target) && !navLinks.contains(event.target)) {
            navLinks.classList.remove('open');
            syncMenuState();
        }
    });

    navLinks.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('open');
            syncMenuState();
        });
    });

    syncMenuState();
}

function initUserDropdown() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (!userMenuBtn || !userDropdown) return;

    const syncDropdownState = () => {
        userMenuBtn.setAttribute('aria-expanded', userDropdown.classList.contains('open') ? 'true' : 'false');
    };

    userMenuBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        userDropdown.classList.toggle('open');
        syncDropdownState();
    });

    document.addEventListener('click', () => {
        userDropdown.classList.remove('open');
        syncDropdownState();
    });

    syncDropdownState();
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

function initTripMap() {
    const mapEl = document.getElementById('tripMap');
    if (!mapEl || typeof window.L === 'undefined') {
        initTripPreviewMaps();
        return;
    }

    const sesameLat = parseFloat(mapEl.dataset.sesameLat || '0');
    const sesameLng = parseFloat(mapEl.dataset.sesameLng || '0');
    const osrmUrl = mapEl.dataset.osrmUrl || '';

    const pointLatInput = document.getElementById('point_lat');
    const pointLngInput = document.getElementById('point_lng');
    const distanceInput = document.getElementById('distance_km');
    const durationInput = document.getElementById('duree_minutes');
    const routeGeometryInput = document.getElementById('route_geometry');
    const routeProviderInput = document.getElementById('route_provider');
    const distanceValue = document.getElementById('distanceValue');
    const durationValue = document.getElementById('durationValue');
    const routeWarning = document.getElementById('routeWarning');
    const prixParKmInput = document.getElementById('prix_par_km');
    const prixInput = document.getElementById('prix');
    const suggestedPriceValue = document.getElementById('suggestedPriceValue');
    // applySuggestedPrice button removed — price is always auto-computed.

    const map = L.map(mapEl, { zoomControl: true }).setView([sesameLat, sesameLng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const sesameMarker = L.marker([sesameLat, sesameLng], { title: 'Sesame' }).addTo(map);

    let selectedLat = pointLatInput?.value ? parseFloat(pointLatInput.value) : null;
    let selectedLng = pointLngInput?.value ? parseFloat(pointLngInput.value) : null;
    let hasPoint = Number.isFinite(selectedLat) && Number.isFinite(selectedLng);

    let pointMarker = L.marker(
        [hasPoint ? selectedLat : sesameLat, hasPoint ? selectedLng : sesameLng],
        { draggable: true }
    ).addTo(map);

    let routeLayer = null;

    const getDirection = () => {
        const checked = document.querySelector('input[type="radio"][name="direction"]:checked');
        return checked ? checked.value : 'vers_sesame';
    };

    const updateHiddenPoint = (lat, lng) => {
        if (pointLatInput) pointLatInput.value = String(lat);
        if (pointLngInput) pointLngInput.value = String(lng);
    };

    const updateHiddenRoute = (distanceKm, durationMinutes, provider, geometry) => {
        if (distanceInput) distanceInput.value = distanceKm !== null ? distanceKm.toFixed(2) : '';
        if (durationInput) durationInput.value = durationMinutes !== null ? String(durationMinutes) : '';
        if (routeProviderInput) routeProviderInput.value = provider || '';
        if (routeGeometryInput) routeGeometryInput.value = geometry ? serializeRouteGeometry(geometry) : '';
    };

    // Always force-apply the computed price (priceManuallyEdited is always false).
    const applyComputedPrice = () => {
        updateSuggestedPrice(distanceInput, prixParKmInput, prixInput, suggestedPriceValue, false, null);
    };

    const updateRoute = async () => {
        if (!hasPoint || !Number.isFinite(selectedLat) || !Number.isFinite(selectedLng)) {
            updateRouteSummary(null, null, null, distanceValue, durationValue, routeWarning);
            applyComputedPrice();
            return;
        }

        const direction = getDirection();
        const startLat = direction === 'vers_sesame' ? selectedLat : sesameLat;
        const startLng = direction === 'vers_sesame' ? selectedLng : sesameLng;
        const endLat = direction === 'vers_sesame' ? sesameLat : selectedLat;
        const endLng = direction === 'vers_sesame' ? sesameLng : selectedLng;

        let routeData = null;
        if (osrmUrl) {
            try {
                routeData = await calculateRouteWithOSRM(startLat, startLng, endLat, endLng, osrmUrl);
            } catch (error) {
                routeData = null;
            }
        }

        if (routeData) {
            drawRouteLine(routeData.geometry, map, (layer) => { routeLayer = layer; });
            updateRouteSummary(routeData.distanceKm, routeData.durationMinutes, 'osrm', distanceValue, durationValue, routeWarning);
            updateHiddenRoute(routeData.distanceKm, routeData.durationMinutes, 'osrm', routeData.geometry);
        } else {
            const haversineKm = calculateHaversineDistanceKm(startLat, startLng, endLat, endLng);
            const fallbackGeometry = {
                type: 'LineString',
                coordinates: [
                    [startLng, startLat],
                    [endLng, endLat],
                ],
            };
            drawRouteLine(fallbackGeometry, map, (layer) => { routeLayer = layer; });
            updateRouteSummary(haversineKm, null, 'haversine_fallback', distanceValue, durationValue, routeWarning);
            updateHiddenRoute(haversineKm, null, 'haversine_fallback', fallbackGeometry);
        }

        applyComputedPrice();
    };

    const existingGeometryRaw = routeGeometryInput?.value || '';
    if (existingGeometryRaw) {
        try {
            const geometry = JSON.parse(existingGeometryRaw);
            drawRouteLine(geometry, map, (layer) => { routeLayer = layer; });
            const distanceKm = distanceInput?.value ? parseFloat(distanceInput.value) : null;
            const durationMinutes = durationInput?.value ? parseInt(durationInput.value, 10) : null;
            const provider = routeProviderInput?.value || 'osrm';
            updateRouteSummary(distanceKm, Number.isFinite(durationMinutes) ? durationMinutes : null, provider, distanceValue, durationValue, routeWarning);
        } catch (error) {
            // Ignore invalid geometry on load.
        }
    }

    if (hasPoint) {
        map.fitBounds(L.latLngBounds([[sesameLat, sesameLng], [selectedLat, selectedLng]]), { padding: [20, 20] });
    }

    pointMarker.on('dragend', (event) => {
        const { lat, lng } = event.target.getLatLng();
        selectedLat = lat;
        selectedLng = lng;
        hasPoint = true;
        updateHiddenPoint(lat, lng);
        updateRoute();
    });

    map.on('click', (event) => {
        const { lat, lng } = event.latlng;
        selectedLat = lat;
        selectedLng = lng;
        hasPoint = true;
        pointMarker.setLatLng([lat, lng]);
        updateHiddenPoint(lat, lng);
        updateRoute();
    });

    document.querySelectorAll('input[type="radio"][name="direction"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            if (!radio.checked) return;
            updateRoute();
        });
    });

    // Prix input is readonly — no manual edit listener needed.
    // Prix par km is hidden/readonly — no manual edit listener needed.

    if (hasPoint && !existingGeometryRaw) {
        updateRoute();
    } else {
        applyComputedPrice();
    }

    initTripPreviewMaps();
}

function initReservationPointPicker() {
    const mapEl = document.getElementById('reservationMap');
    if (!mapEl || typeof window.L === 'undefined') return;

    const routeRaw = mapEl.dataset.routeGeometry || '';
    if (!routeRaw) return;

    let geometry = null;
    try {
        geometry = JSON.parse(routeRaw);
    } catch (error) {
        return;
    }

    const routeCoords = getRouteCoordinatesFromGeometry(geometry);
    if (routeCoords.length < 2) return;

    const direction = mapEl.dataset.direction || 'vers_sesame';
    const prixParKm = parseFloat(mapEl.dataset.prixParKm || '0');
    const totalDuration = parseInt(mapEl.dataset.totalDuration || '', 10);

    const warningEl = document.getElementById('reservationPointWarning');
    const pointValueEl = document.getElementById('reservationPointValue');
    const distanceValueEl = document.getElementById('reservationDistanceValue');
    const durationValueEl = document.getElementById('reservationDurationValue');
    const priceValueEl = document.getElementById('reservationPriceValue');
    const submitBtn = document.getElementById('reservationSubmitBtn');

    const pointLatInput = document.getElementById('reservation_point_lat');
    const pointLngInput = document.getElementById('reservation_point_lng');
    const distanceInput = document.getElementById('reservation_distance_km');
    const durationInput = document.getElementById('reservation_duree_minutes');
    const priceInput = document.getElementById('reservation_price');
    const pointTypeInput = document.getElementById('reservation_point_type');

    const map = L.map(mapEl, { zoomControl: true }).setView([routeCoords[0].lat, routeCoords[0].lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const polyline = L.polyline(routeCoords, {
        color: '#C0392B',
        weight: 4,
        opacity: 0.9,
    }).addTo(map);
    map.fitBounds(polyline.getBounds(), { padding: [20, 20] });

    const startMarker = L.circleMarker(routeCoords[0], {
        radius: 5,
        color: '#C0392B',
        weight: 2,
        fillColor: '#C0392B',
        fillOpacity: 0.9,
    }).addTo(map);

    const endMarker = L.circleMarker(routeCoords[routeCoords.length - 1], {
        radius: 5,
        color: '#E67E22',
        weight: 2,
        fillColor: '#E67E22',
        fillOpacity: 0.9,
    }).addTo(map);

    let selectionMarker = null;
    const totalDistanceMeters = computeRouteDistanceAlongPolyline(routeCoords, routeCoords.length - 2, 1);

    enableBookingIfValid(submitBtn, false);

    map.on('click', (event) => {
        const nearest = findNearestPointOnRoute(event.latlng, routeCoords);
        if (!nearest) return;

        if (nearest.distanceMeters > 500) {
            if (warningEl) {
                warningEl.textContent = 'Veuillez choisir un point situé sur le circuit proposé.';
                warningEl.style.display = 'block';
            }
            updateReservationPointSummary(pointValueEl, distanceValueEl, durationValueEl, priceValueEl, null);
            enableBookingIfValid(submitBtn, false);
            return;
        }

        if (warningEl) {
            warningEl.textContent = '';
            warningEl.style.display = 'none';
        }

        const distanceToPointMeters = computeRouteDistanceAlongPolyline(routeCoords, nearest.segmentIndex, nearest.t);
        const chargedMeters = direction === 'vers_sesame'
            ? Math.max(0, totalDistanceMeters - distanceToPointMeters)
            : Math.max(0, distanceToPointMeters);

        const chargedKm = chargedMeters / 1000;
        const durationMinutes = computeRouteDurationProportionally(totalDuration, chargedMeters, totalDistanceMeters);
        const priceEstimate = Math.round(chargedKm * prixParKm * 100) / 100;

        if (!selectionMarker) {
            selectionMarker = L.circleMarker([nearest.lat, nearest.lng], {
                radius: 6,
                color: '#1D4ED8',
                weight: 2,
                fillColor: '#3B82F6',
                fillOpacity: 0.9,
            }).addTo(map);
        } else {
            selectionMarker.setLatLng([nearest.lat, nearest.lng]);
        }

        if (pointLatInput) pointLatInput.value = nearest.lat.toFixed(7);
        if (pointLngInput) pointLngInput.value = nearest.lng.toFixed(7);
        if (distanceInput) distanceInput.value = chargedKm.toFixed(2);
        if (durationInput) durationInput.value = durationMinutes !== null ? String(durationMinutes) : '';
        if (priceInput) priceInput.value = priceEstimate.toFixed(2);
        if (pointTypeInput && !pointTypeInput.value) {
            pointTypeInput.value = direction === 'vers_sesame' ? 'prise_en_charge' : 'depose';
        }

        updateReservationPointSummary(
            pointValueEl,
            distanceValueEl,
            durationValueEl,
            priceValueEl,
            {
                lat: nearest.lat,
                lng: nearest.lng,
                distanceKm: chargedKm,
                durationMinutes,
                price: priceEstimate,
            }
        );

        enableBookingIfValid(submitBtn, true);
    });
}

function getRouteCoordinatesFromGeometry(geometry) {
    if (!geometry || geometry.type !== 'LineString' || !Array.isArray(geometry.coordinates)) {
        return [];
    }

    return geometry.coordinates
        .filter((coord) => Array.isArray(coord) && coord.length >= 2)
        .map((coord) => ({ lat: coord[1], lng: coord[0] }));
}

function findNearestPointOnRoute(clickedLatLng, routeCoordinates) {
    let minDistance = Number.POSITIVE_INFINITY;
    let closest = null;

    for (let i = 0; i < routeCoordinates.length - 1; i++) {
        const segment = distancePointToSegmentMeters(clickedLatLng, routeCoordinates[i], routeCoordinates[i + 1]);
        if (segment.distanceMeters < minDistance) {
            minDistance = segment.distanceMeters;
            closest = {
                lat: segment.lat,
                lng: segment.lng,
                distanceMeters: segment.distanceMeters,
                segmentIndex: i,
                t: segment.t,
            };
        }
    }

    return closest;
}

function distancePointToSegmentMeters(point, a, b) {
    const r = 6371000;
    const toRad = (value) => (value * Math.PI) / 180;

    const lat1 = toRad(a.lat);
    const lng1 = toRad(a.lng);
    const lat2 = toRad(b.lat);
    const lng2 = toRad(b.lng);
    const latP = toRad(point.lat);
    const lngP = toRad(point.lng);

    const lat0 = (lat1 + lat2) / 2;
    const ax = lng1 * Math.cos(lat0) * r;
    const ay = lat1 * r;
    const bx = lng2 * Math.cos(lat0) * r;
    const by = lat2 * r;
    const px = lngP * Math.cos(lat0) * r;
    const py = latP * r;

    const dx = bx - ax;
    const dy = by - ay;
    const lenSq = (dx * dx) + (dy * dy);
    let t = lenSq > 0 ? ((px - ax) * dx + (py - ay) * dy) / lenSq : 0;
    t = Math.max(0, Math.min(1, t));

    const closestX = ax + dx * t;
    const closestY = ay + dy * t;
    const distanceMeters = Math.sqrt((px - closestX) ** 2 + (py - closestY) ** 2);

    const closestLat = (closestY / r) * (180 / Math.PI);
    const closestLng = (closestX / (r * Math.cos(lat0))) * (180 / Math.PI);

    return {
        distanceMeters,
        t,
        lat: closestLat,
        lng: closestLng,
    };
}

function distanceBetweenLatLngMeters(a, b) {
    const r = 6371000;
    const toRad = (value) => (value * Math.PI) / 180;
    const lat1 = toRad(a.lat);
    const lng1 = toRad(a.lng);
    const lat2 = toRad(b.lat);
    const lng2 = toRad(b.lng);
    const dLat = lat2 - lat1;
    const dLng = lng2 - lng1;

    const h = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
    return r * c;
}

function computeRouteDistanceAlongPolyline(routeCoordinates, segmentIndex, t) {
    if (!routeCoordinates.length) return 0;

    const safeIndex = Math.max(0, Math.min(segmentIndex, routeCoordinates.length - 2));
    const safeT = Math.max(0, Math.min(1, t));
    let distance = 0;

    for (let i = 0; i < safeIndex; i++) {
        distance += distanceBetweenLatLngMeters(routeCoordinates[i], routeCoordinates[i + 1]);
    }

    const segmentDistance = distanceBetweenLatLngMeters(routeCoordinates[safeIndex], routeCoordinates[safeIndex + 1]);
    distance += segmentDistance * safeT;

    return distance;
}

function computeRouteDurationProportionally(totalMinutes, segmentMeters, totalMeters) {
    if (!Number.isFinite(totalMinutes) || totalMinutes <= 0 || totalMeters <= 0) {
        return null;
    }

    return Math.round(totalMinutes * (segmentMeters / totalMeters));
}

function updateReservationPointSummary(pointEl, distanceEl, durationEl, priceEl, data) {
    if (!data) {
        if (pointEl) pointEl.textContent = '-';
        if (distanceEl) distanceEl.textContent = '-';
        if (durationEl) durationEl.textContent = '-';
        if (priceEl) priceEl.textContent = '-';
        return;
    }

    if (pointEl) {
        pointEl.textContent = `${data.lat.toFixed(5)}, ${data.lng.toFixed(5)}`;
    }
    if (distanceEl) {
        distanceEl.textContent = data.distanceKm.toFixed(2);
    }
    if (durationEl) {
        durationEl.textContent = data.durationMinutes !== null ? String(data.durationMinutes) : '-';
    }
    if (priceEl) {
        priceEl.textContent = data.price.toFixed(2);
    }
}

function enableBookingIfValid(button, isValid) {
    if (!button) return;
    button.disabled = !isValid;
    button.classList.toggle('is-disabled', !isValid);
}

async function calculateRouteWithOSRM(startLat, startLng, endLat, endLng, osrmUrl) {
    const url = `${osrmUrl}/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson&steps=false&alternatives=false`;
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error('OSRM request failed');
    }
    const data = await response.json();
    if (!data.routes || !data.routes.length) {
        throw new Error('OSRM route missing');
    }
    const route = data.routes[0];
    return {
        distanceKm: route.distance / 1000,
        durationMinutes: Math.round(route.duration / 60),
        geometry: route.geometry,
    };
}

function calculateHaversineDistanceKm(lat1, lng1, lat2, lng2) {
    const toRad = (value) => (value * Math.PI) / 180;
    const earthRadius = 6371;
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);
    const a = Math.sin(dLat / 2) ** 2
        + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return earthRadius * c;
}

function drawRouteLine(routeGeometry, map, setLayer) {
    if (!map || !routeGeometry) return;
    if (map._routeLayer) {
        map.removeLayer(map._routeLayer);
    }
    const layer = L.geoJSON(routeGeometry, {
        style: {
            color: '#C0392B',
            weight: 4,
            opacity: 0.9,
        },
    }).addTo(map);
    map._routeLayer = layer;
    if (typeof setLayer === 'function') {
        setLayer(layer);
    }
    if (layer.getBounds && layer.getBounds().isValid()) {
        map.fitBounds(layer.getBounds(), { padding: [20, 20] });
    }
}

function updateRouteSummary(distanceKm, durationMinutes, provider, distanceValue, durationValue, routeWarning) {
    if (distanceValue) {
        distanceValue.textContent = Number.isFinite(distanceKm) ? distanceKm.toFixed(2) : '-';
    }
    if (durationValue) {
        durationValue.textContent = Number.isFinite(durationMinutes) ? String(durationMinutes) : '-';
    }
    if (routeWarning) {
        if (provider === 'haversine_fallback') {
            routeWarning.textContent = "Circuit routier indisponible. Distance estimee a vol d'oiseau.";
            routeWarning.style.display = 'block';
        } else {
            routeWarning.textContent = '';
            routeWarning.style.display = 'none';
        }
    }
}

function updateSuggestedPrice(distanceInput, prixParKmInput, prixInput, suggestedPriceValue, priceManuallyEdited, onUpdated) {
    if (!distanceInput || !prixParKmInput || !suggestedPriceValue) return;
    const distanceKm = parseFloat(distanceInput.value || '');
    const prixParKm = parseFloat(prixParKmInput.value || '');

    if (!Number.isFinite(distanceKm) || !Number.isFinite(prixParKm)) {
        suggestedPriceValue.textContent = '-';
        if (typeof onUpdated === 'function') onUpdated();
        return;
    }

    const suggested = Math.round(distanceKm * prixParKm * 100) / 100;
    suggestedPriceValue.textContent = suggested.toFixed(2);

    if (prixInput) {
        prixInput.value = suggested.toFixed(2);
    }

    if (typeof onUpdated === 'function') onUpdated();
}

function serializeRouteGeometry(geometry) {
    try {
        return JSON.stringify(geometry);
    } catch (error) {
        return '';
    }
}

function initTripPreviewMaps() {
    const previewMaps = document.querySelectorAll('.circuit-preview-map');
    if (!previewMaps.length || typeof window.L === 'undefined') return;

    previewMaps.forEach((mapEl) => {
        const raw = mapEl.dataset.routeGeometry || '';
        if (!raw) return;

        let geometry = null;
        try {
            geometry = JSON.parse(raw);
        } catch (error) {
            return;
        }

        const sesameLat = parseFloat(mapEl.dataset.sesameLat || '0');
        const sesameLng = parseFloat(mapEl.dataset.sesameLng || '0');

        const map = L.map(mapEl, {
            zoomControl: false,
            attributionControl: false,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            keyboard: false,
            tap: false,
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        if (Number.isFinite(sesameLat) && Number.isFinite(sesameLng)) {
            L.circleMarker([sesameLat, sesameLng], {
                radius: 5,
                color: '#C0392B',
                weight: 2,
                fillColor: '#C0392B',
                fillOpacity: 0.9,
            }).addTo(map);
        }

        drawRouteLine(geometry, map);
    });
}

function initDriverRequestMaps() {
    const requestMaps = document.querySelectorAll('.driver-request-map');
    if (!requestMaps.length || typeof window.L === 'undefined') return;

    requestMaps.forEach((mapEl) => {
        const rawGeometry = mapEl.dataset.routeGeometry || '';
        const pointLat = parseFloat(mapEl.dataset.pointLat || '');
        const pointLng = parseFloat(mapEl.dataset.pointLng || '');
        const pointType = mapEl.dataset.pointType || 'prise_en_charge';

        if (!rawGeometry || !Number.isFinite(pointLat) || !Number.isFinite(pointLng)) return;

        let geometry = null;
        try {
            geometry = JSON.parse(rawGeometry);
        } catch (error) {
            return;
        }

        const map = L.map(mapEl, {
            zoomControl: true,
            attributionControl: false,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: true,
            boxZoom: false,
            keyboard: false,
            tap: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        const routeLine = drawRouteLine(geometry, map);

        const markerColor = pointType === 'prise_en_charge' ? '#2980B9' : '#8E44AD';
        const pointMarker = L.circleMarker([pointLat, pointLng], {
            radius: 6,
            color: '#FFFFFF',
            weight: 2,
            fillColor: markerColor,
            fillOpacity: 1,
        }).addTo(map);

        if (routeLine) {
            const bounds = routeLine.getBounds();
            bounds.extend(pointMarker.getLatLng());
            map.fitBounds(bounds, { padding: [10, 10] });
        } else {
            map.setView([pointLat, pointLng], 14);
        }
    });
}

function initSortableTables() {
    const tables = document.querySelectorAll('.data-sortable-table');
    if (!tables.length) return;

    tables.forEach((table) => {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const headers = table.querySelectorAll('thead th.data-sortable-column, thead th[data-sort]');
        if (!headers.length) return;

        headers.forEach((header) => {
            if (!header.classList.contains('data-sortable-column')) {
                header.classList.add('data-sortable-column');
            }
            header.classList.add('sortable-header');
            header.setAttribute('tabindex', '0');
            header.setAttribute('role', 'button');

            let indicator = header.querySelector('.sort-indicator');
            if (!indicator) {
                indicator = document.createElement('span');
                indicator.className = 'sort-indicator';
                indicator.textContent = '↕';
                indicator.setAttribute('aria-hidden', 'true');
                header.appendChild(indicator);
            }

            const sortHandler = () => {
                const headerCells = Array.from(header.parentElement.children);
                const columnIndex = headerCells.indexOf(header);
                const sortType = header.dataset.sortType || header.dataset.sort || 'text';
                const dataRows = Array.from(tbody.querySelectorAll('tr'))
                    .filter((row) => !row.querySelector('.empty-state'));

                if (dataRows.length < 2) return;

                const shouldSortAsc = !header.classList.contains('sort-asc');

                table.querySelectorAll('thead th.sortable-header').forEach((th) => {
                    th.classList.remove('sort-asc', 'sort-desc');
                    const thIndicator = th.querySelector('.sort-indicator');
                    if (thIndicator) thIndicator.textContent = '↕';
                });

                header.classList.add(shouldSortAsc ? 'sort-asc' : 'sort-desc');
                if (indicator) {
                    indicator.textContent = shouldSortAsc ? '↑' : '↓';
                }

                dataRows.sort((rowA, rowB) => {
                    const cellA = rowA.children[columnIndex] || null;
                    const cellB = rowB.children[columnIndex] || null;
                    const valueA = getSortableValue(cellA, sortType);
                    const valueB = getSortableValue(cellB, sortType);

                    if (valueA === null && valueB === null) return 0;
                    if (valueA === null) return 1;
                    if (valueB === null) return -1;

                    let comparison = 0;
                    if (typeof valueA === 'string' && typeof valueB === 'string') {
                        comparison = valueA.localeCompare(valueB, 'fr', { sensitivity: 'base' });
                    } else {
                        comparison = valueA < valueB ? -1 : (valueA > valueB ? 1 : 0);
                    }

                    return shouldSortAsc ? comparison : -comparison;
                });

                dataRows.forEach((row) => {
                    tbody.appendChild(row);
                });
            };

            header.addEventListener('click', sortHandler);
            header.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sortHandler();
                }
            });
        });
    });
}

function getSortableValue(cell, sortType) {
    if (!cell) return null;

    const raw = cell.getAttribute('data-sort-value');
    const text = (raw !== null ? raw : cell.textContent || '')
        .replace(/\s+/g, ' ')
        .trim();

    if (text === '') {
        return null;
    }

    if (sortType === 'number' || sortType === 'money') {
        return parseSortableNumber(text);
    }

    if (sortType === 'date') {
        return parseSortableDate(text);
    }

    return text.toLocaleLowerCase('fr');
}

function parseSortableNumber(text) {
    const cleaned = text
        .replace(/\s+/g, '')
        .replace(/[^\d,.-]/g, '');

    if (cleaned === '' || cleaned === '-') {
        return null;
    }

    let normalized = cleaned;
    const hasComma = normalized.includes(',');
    const hasDot = normalized.includes('.');

    if (hasComma && hasDot) {
        normalized = normalized.replace(/,/g, '');
    } else if (hasComma && !hasDot) {
        normalized = normalized.replace(/,/g, '.');
    }

    const value = parseFloat(normalized);
    return Number.isFinite(value) ? value : null;
}

function parseSortableDate(text) {
    const dateMatch = text.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/);
    if (!dateMatch) {
        return null;
    }

    const day = Number(dateMatch[1]);
    const month = Number(dateMatch[2]);
    const year = Number(dateMatch[3]);
    const hour = dateMatch[4] ? Number(dateMatch[4]) : 0;
    const minute = dateMatch[5] ? Number(dateMatch[5]) : 0;
    const parsed = new Date(year, month - 1, day, hour, minute);

    if (
        parsed.getFullYear() !== year ||
        parsed.getMonth() !== month - 1 ||
        parsed.getDate() !== day ||
        parsed.getHours() !== hour ||
        parsed.getMinutes() !== minute
    ) {
        return null;
    }

    return parsed.getTime();
}
