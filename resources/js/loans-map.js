/**
 * LOAN'S — Leaflet + 国土地理院タイル（APIキー不要）。
 */

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

const GSI_TILE_URL = 'https://cyberjapandata.gsi.go.jp/xyz/std/{z}/{x}/{y}.png';
const GSI_ATTRIBUTION =
    '<a href="https://maps.gsi.go.jp/development/ichiran.html" target="_blank" rel="noopener">国土地理院</a>';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

export const DEFAULT_CENTER = {
    latitude: 35.681236,
    longitude: 139.767125,
    zoom: 13,
};

/** @type {Map<string, { map: L.Map, pickerMarker: L.Marker|null, syncInputs: Function }>} */
const mapRegistry = new Map();

function createTileLayer() {
    return L.tileLayer(GSI_TILE_URL, {
        maxZoom: 18,
        attribution: GSI_ATTRIBUTION,
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
    if (! raw) {
        return [];
    }

    try {
        const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

export function initMapElement(el) {
    if (el.dataset.mapReady === '1' && mapRegistry.has(el.id)) {
        return mapRegistry.get(el.id).map;
    }

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
        preferCanvas: true,
    }).setView([latitude, longitude], zoom);

    createTileLayer().addTo(map);

    const leafletMarkers = [];
    let pickerMarker = null;
    let syncInputs = () => {};

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
        pickerMarker = interactive ? m : null;
    } else if (interactive) {
        pickerMarker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
        pickerMarker.bindPopup('地図をクリック、またはピンをドラッグして座標を設定').openPopup();
        leafletMarkers.push(pickerMarker);
    }

    if (interactive) {
        const latInput = latInputId ? document.getElementById(latInputId) : null;
        const lngInput = lngInputId ? document.getElementById(lngInputId) : null;
        pickerMarker = pickerMarker || leafletMarkers[0] || null;

        if (pickerMarker && ! pickerMarker.dragging?.enabled()) {
            pickerMarker.dragging?.enable?.();
        }

        syncInputs = (lat, lng) => {
            if (latInput) {
                latInput.value = lat.toFixed(7);
            }
            if (lngInput) {
                lngInput.value = lng.toFixed(7);
            }
        };

        const placePicker = (lat, lng) => {
            if (! pickerMarker) {
                pickerMarker = L.marker([lat, lng], { draggable: true }).addTo(map);
            } else {
                pickerMarker.setLatLng([lat, lng]);
            }
            syncInputs(lat, lng);
            pickerMarker.bindPopup(`緯度 ${lat.toFixed(7)}<br>経度 ${lng.toFixed(7)}`).openPopup();
            mapRegistry.set(el.id || el.dataset.mapKey || String(el), {
                map,
                pickerMarker,
                syncInputs,
            });
        };

        pickerMarker?.on('dragend', () => {
            const pos = pickerMarker.getLatLng();
            syncInputs(pos.lat, pos.lng);
        });

        map.on('click', (event) => {
            const { lat, lng } = event.latlng;
            placePicker(lat, lng);
            if (pickerMarker && ! pickerMarker.listens?.('dragend')) {
                pickerMarker.on('dragend', () => {
                    const pos = pickerMarker.getLatLng();
                    syncInputs(pos.lat, pos.lng);
                });
            }
        });

        if (latInput && ! latInput.value) {
            syncInputs(latitude, longitude);
        }
    }

    const key = el.id || `map-${mapRegistry.size}`;
    if (! el.id) {
        el.id = key;
    }

    mapRegistry.set(key, { map, pickerMarker, syncInputs });
    el.dataset.mapReady = '1';

    requestAnimationFrame(() => map.invalidateSize());
    setTimeout(() => map.invalidateSize(), 120);

    return map;
}

/**
 * 検索結果などで地図中心・ピンを移動する。
 */
export function focusMap(elOrId, latitude, longitude, zoom = 16) {
    const el = typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;
    if (! el) {
        return;
    }

    if (el.dataset.mapReady !== '1') {
        initMapElement(el);
    }

    const entry = mapRegistry.get(el.id);
    if (! entry) {
        return;
    }

    const { map, pickerMarker, syncInputs } = entry;
    map.setView([latitude, longitude], zoom);

    if (pickerMarker) {
        pickerMarker.setLatLng([latitude, longitude]);
        pickerMarker.bindPopup(`緯度 ${latitude.toFixed(7)}<br>経度 ${longitude.toFixed(7)}`).openPopup();
    } else if (el.dataset.interactive === '1') {
        const marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            syncInputs(pos.lat, pos.lng);
        });
        mapRegistry.set(el.id, { map, pickerMarker: marker, syncInputs });
    }

    syncInputs(latitude, longitude);
    map.invalidateSize();
}

export function initAllMaps(root = document) {
    root.querySelectorAll('[data-loans-map]').forEach((el) => {
        if (el.dataset.deferMap === '1') {
            return;
        }
        if (el.dataset.mapReady === '1') {
            return;
        }
        initMapElement(el);
    });
}

export function bootMaps() {
    initAllMaps();

    document.querySelectorAll('[data-load-map]').forEach((button) => {
        if (button.dataset.loadMapReady === '1') {
            return;
        }
        button.addEventListener('click', () => {
            const section = button.closest('section');
            const targetId = button.dataset.loadMap;
            const el = targetId
                ? document.getElementById(targetId)
                : section?.querySelector('[data-loans-map]');
            if (! el) {
                return;
            }

            button.disabled = true;
            button.textContent = '読み込み中…';
            section?.querySelector('[data-map-placeholder]')?.classList.add('hidden');
            section?.querySelector('[data-map-shell]')?.classList.remove('hidden');

            el.dataset.deferMap = '0';
            initMapElement(el);
        });
        button.dataset.loadMapReady = '1';
    });
}
