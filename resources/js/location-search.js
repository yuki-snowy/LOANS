/**
 * 郵便番号 / 住所検索 → 国土地理院座標で地図表示。
 */

import { focusMap } from './loans-map.js';

function digitsOnly(value) {
    return String(value || '').replace(/\D/g, '');
}

function formatPostal(digits) {
    if (digits.length !== 7) {
        return digits;
    }

    return `${digits.slice(0, 3)}-${digits.slice(3)}`;
}

async function searchLocation(query) {
    const params = new URLSearchParams({ q: query });
    const response = await fetch(`/geocode/search?${params.toString()}`, {
        headers: { Accept: 'application/json' },
    });
    const payload = await response.json();
    if (! response.ok || ! payload?.ok) {
        throw new Error(payload?.message || '検索に失敗しました');
    }

    return payload;
}

function applyToForm(form, result) {
    const postalInput = form.querySelector('[data-postal-code]');
    const prefectureInput = form.querySelector('[data-prefecture]');
    const addressInput = form.querySelector('[data-address]');
    const latInput = form.querySelector('[data-latitude]');
    const lngInput = form.querySelector('[data-longitude]');

    if (result.postal_code && postalInput) {
        postalInput.value = formatPostal(digitsOnly(result.postal_code));
    }
    if (result.prefecture && prefectureInput) {
        prefectureInput.value = result.prefecture;
    }
    if (result.address && addressInput) {
        addressInput.value = result.address;
        addressInput.dataset.autofilled = '1';
    }
    if (result.latitude != null && latInput) {
        latInput.value = Number(result.latitude).toFixed(7);
    }
    if (result.longitude != null && lngInput) {
        lngInput.value = Number(result.longitude).toFixed(7);
    }
}

function initLocationSearch(root = document) {
    root.querySelectorAll('[data-location-search]').forEach((block) => {
        if (block.dataset.locationReady === '1') {
            return;
        }

        const form = block.closest('[data-property-form]') || document;
        const input = block.querySelector('[data-location-query]');
        const button = block.querySelector('[data-location-submit]');
        const status = block.querySelector('[data-location-status]');
        const mapEl = form.querySelector('[data-loans-map]');

        if (! input || ! button) {
            return;
        }

        const run = async () => {
            const query = input.value.trim();
            if (! query) {
                if (status) {
                    status.textContent = '郵便番号または住所を入力してください';
                }
                return;
            }

            button.disabled = true;
            if (status) {
                status.textContent = '検索中…';
            }

            try {
                const result = await searchLocation(query);
                applyToForm(form, result);

                if (result.latitude != null && result.longitude != null && mapEl) {
                    mapEl.dataset.deferMap = '0';
                    focusMap(mapEl, Number(result.latitude), Number(result.longitude), 16);
                }

                if (status) {
                    status.textContent = result.latitude != null
                        ? `地図を表示しました（${result.label || query}）`
                        : (result.message || '住所を入力しました');
                }
            } catch (error) {
                if (status) {
                    status.textContent = error.message || '検索に失敗しました';
                }
            } finally {
                button.disabled = false;
            }
        };

        button.addEventListener('click', (event) => {
            event.preventDefault();
            run();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                run();
            }
        });

        block.dataset.locationReady = '1';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initLocationSearch());
} else {
    initLocationSearch();
}
