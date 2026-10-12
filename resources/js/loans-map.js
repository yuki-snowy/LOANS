/**
 * LOAN'S — Leaflet + OpenStreetMap helpers (no paid map APIs).
 * Expects global `L` from the Leaflet CDN.
 */

const OSM_TILE_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const OSM_ATTRIBUTION =
    'Map data © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors (Cost: ¥0)';

/** Tokyo Station — fallback center when coordinates are missing. */
export const DEFAULT_CENTER = {
    latitude: 35.681236,
    longitude: 139.767125,
    zoom: 13,
};

function requireLeaflet() {
    if (typeof window.L === 'undefined') {
        throw new Error('Leaflet (L) is not loaded. Ensure the CDN script is included.');
    }

    return window.L;
}

function createTileLayer(L) {
    return L.tileLayer(OSM_TILE_URL, {
        maxZoom: 19,
        attribution: OSM_ATTRIBUTION,
    });
}

function popupHtml({ title, price, url }) {
    const name = title ? escapeHtml(title) : '物件';
    const priceLine =
        price !== null && price !== undefined && price !== ''
            ? `<div class="text-sm">${escapeHtml(String(price))}万円</div>`
            : '';
    const link = url
        ? `<div class="mt-1"><a href="${escapeHtml(url)}" class="underline">詳細を見る</a></div>`
        : '';

    return `<strong>${name}</strong>${priceLine}${link}`;
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function parseMarkers(raw) {
    if (!raw) {
        return [];
    }

    try {
        const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

/**
 * Initialize a map from a root element with data-* attributes.
 */
export function initMapElement(el) {
    const L = requireLeaflet();
    const markers = parseMarkers(el.dataset.markers);
    const interactive = el.dataset.interactive === '1';
    const latInputId = el.dataset.latInput || '';
    const lngInputId = el.dataset.lngInput || '';

    let latitude = el.dataset.latitude !== '' ? Number(el.dataset.latitude) : null;
    let longitude = el.dataset.longitude !== '' ? Number(el.dataset.longitude) : null;
    const zoom = Number(el.dataset.zoom || DEFAULT_CENTER.zoom);
    const title = el.dataset.title || '';
    const price = el.dataset.price || '';

    if ((latitude === null || Number.isNaN(latitude) || longitude === null || Number.isNaN(longitude)) && markers.length === 0) {
        latitude = DEFAULT_CENTER.latitude;
        longitude = DEFAULT_CENTER.longitude;
    }

    if (markers.length > 0 && (latitude === null || longitude === null || Number.isNaN(latitude))) {
        latitude = Number(markers[0].latitude);
        longitude = Number(markers[0].longitude);
    }

    const map = L.map(el, {
        scrollWheelZoom: true,
    }).setView([latitude, longitude], zoom);

    createTileLayer(L).addTo(map);

    const leafletMarkers = [];

    if (markers.length > 0) {
        const bounds = [];
        markers.forEach((marker) => {
            if (marker.latitude === null || marker.longitude === null) {
                return;
            }
            const lat = Number(marker.latitude);
            const lng = Number(marker.longitude);
            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }
            const m = L.marker([lat, lng]).addTo(map);
            m.bindPopup(
                popupHtml({
                    title: marker.title,
                    price: marker.price,
                    url: marker.url,
                }),
            );
            leafletMarkers.push(m);
            bounds.push([lat, lng]);
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [32, 32], maxZoom: 15 });
        } else if (bounds.length === 1) {
            map.setView(bounds[0], zoom);
            leafletMarkers[0]?.openPopup();
        }
    } else if (el.dataset.latitude !== '' && el.dataset.longitude !== '') {
        const m = L.marker([latitude, longitude]).addTo(map);
        m.bindPopup(popupHtml({ title, price })).openPopup();
        leafletMarkers.push(m);
    } else if (interactive) {
        const m = L.marker([latitude, longitude], { draggable: true }).addTo(map);
        m.bindPopup('地図をクリック、またはピンをドラッグして座標を設定').openPopup();
        leafletMarkers.push(m);
    }

    if (interactive) {
        const latInput = latInputId ? document.getElementById(latInputId) : null;
        const lngInput = lngInputId ? document.getElementById(lngInputId) : null;
        let pickerMarker = leafletMarkers[0] || null;

        const syncInputs = (lat, lng) => {
            if (latInput) {
                latInput.value = lat.toFixed(7);
            }
            if (lngInput) {
                lngInput.value = lng.toFixed(7);
            }
        };

        if (pickerMarker) {
            pickerMarker.on('dragend', () => {
                const pos = pickerMarker.getLatLng();
                syncInputs(pos.lat, pos.lng);
            });
        }

        map.on('click', (event) => {
            const { lat, lng } = event.latlng;
            if (!pickerMarker) {
                pickerMarker = L.marker([lat, lng], { draggable: true }).addTo(map);
                pickerMarker.on('dragend', () => {
                    const pos = pickerMarker.getLatLng();
                    syncInputs(pos.lat, pos.lng);
                });
            } else {
                pickerMarker.setLatLng([lat, lng]);
            }
            syncInputs(lat, lng);
            pickerMarker.bindPopup(`緯度 ${lat.toFixed(7)}<br>経度 ${lng.toFixed(7)}`).openPopup();
        });

        // Prefill inputs when empty and using Tokyo Station fallback.
        if (latInput && !latInput.value) {
            syncInputs(latitude, longitude);
        }
    }

    // Invalidate size after layout settles (grid/flex).
    requestAnimationFrame(() => map.invalidateSize());
    setTimeout(() => map.invalidateSize(), 150);

    return map;
}

export function initAllMaps(root = document) {
    root.querySelectorAll('[data-loans-map]').forEach((el) => {
        if (el.dataset.mapReady === '1') {
            return;
        }
        initMapElement(el);
        el.dataset.mapReady = '1';
    });
}

function boot() {
    const run = () => {
        try {
            initAllMaps();
        } catch (error) {
            // Leaflet CDN may still be loading; retry briefly.
            if (typeof window.L === 'undefined') {
                setTimeout(run, 50);
                return;
            }
            console.error(error);
        }
    };

    run();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
