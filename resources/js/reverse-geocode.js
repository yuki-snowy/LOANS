/**
 * 地図座標から住所・郵便番号を取得（ボタン実行・HeartRails経由）。
 * 自動実行しない（ページ遷移の重さを避ける）。
 */

function digitsOnly(value) {
    return String(value || '').replace(/\D/g, '');
}

function formatPostal(digits) {
    if (digits.length !== 7) {
        return digits;
    }

    return `${digits.slice(0, 3)}-${digits.slice(3)}`;
}

async function reverseGeocode(latitude, longitude) {
    const params = new URLSearchParams({
        latitude: String(latitude),
        longitude: String(longitude),
    });
    const response = await fetch(`/geocode/reverse?${params.toString()}`, {
        headers: { Accept: 'application/json' },
    });
    const payload = await response.json();
    if (! response.ok || ! payload?.ok) {
        throw new Error(payload?.message || '住所の取得に失敗しました');
    }

    return payload;
}

function initReverseGeocode(root = document) {
    root.querySelectorAll('[data-property-form]').forEach((form) => {
        if (form.dataset.reverseReady === '1') {
            return;
        }

        const latInput = form.querySelector('[data-latitude]');
        const lngInput = form.querySelector('[data-longitude]');
        const postalInput = form.querySelector('[data-postal-code]');
        const prefectureInput = form.querySelector('[data-prefecture]');
        const addressInput = form.querySelector('[data-address]');
        const statusEl = form.querySelector('[data-postal-status]');
        const button = form.querySelector('[data-reverse-geocode]');

        if (! latInput || ! lngInput || ! button) {
            return;
        }

        button.addEventListener('click', async (event) => {
            event.preventDefault();
            const lat = Number(latInput.value);
            const lng = Number(lngInput.value);
            if (! Number.isFinite(lat) || ! Number.isFinite(lng)) {
                if (statusEl) {
                    statusEl.textContent = '先に地図で座標を指定してください';
                }
                return;
            }

            button.disabled = true;
            if (statusEl) {
                statusEl.textContent = '座標から住所を取得中…';
            }

            try {
                const result = await reverseGeocode(lat, lng);
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
                if (statusEl) {
                    statusEl.textContent = '座標から住所を入力しました';
                }
            } catch (error) {
                if (statusEl) {
                    statusEl.textContent = error.message || '住所取得に失敗しました';
                }
            } finally {
                button.disabled = false;
            }
        });

        form.dataset.reverseReady = '1';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initReverseGeocode());
} else {
    initReverseGeocode();
}
