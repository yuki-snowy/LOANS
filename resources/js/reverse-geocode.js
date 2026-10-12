/**
 * 地図座標から郵便番号を自動入力（サーバー経由で Nominatim）。
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
        headers: {
            Accept: 'application/json',
        },
    });

    if (! response.ok) {
        throw new Error('reverse geocode failed');
    }

    return response.json();
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

        if (! latInput || ! lngInput || ! postalInput) {
            return;
        }

        let timer = null;
        let lastKey = '';

        const run = async () => {
            const lat = Number(latInput.value);
            const lng = Number(lngInput.value);
            if (! Number.isFinite(lat) || ! Number.isFinite(lng)) {
                return;
            }

            const key = `${lat.toFixed(5)},${lng.toFixed(5)}`;
            if (key === lastKey) {
                return;
            }
            lastKey = key;

            if (statusEl) {
                statusEl.textContent = '座標から郵便番号を検索中…';
            }

            try {
                const result = await reverseGeocode(lat, lng);
                if (! result?.ok) {
                    if (statusEl) {
                        statusEl.textContent = '郵便番号を取得できませんでした';
                    }
                    return;
                }

                if (result.postal_code) {
                    postalInput.value = formatPostal(digitsOnly(result.postal_code));
                    postalInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (result.prefecture && prefectureInput) {
                    prefectureInput.value = result.prefecture;
                }

                if (result.address && addressInput) {
                    if (! addressInput.value || addressInput.dataset.autofilled === '1') {
                        addressInput.value = result.address;
                        addressInput.dataset.autofilled = '1';
                    }
                }

                if (statusEl) {
                    statusEl.textContent = result.postal_code
                        ? '座標から郵便番号を入力しました'
                        : '座標の住所は見つかりましたが郵便番号はありません';
                }
            } catch {
                if (statusEl) {
                    statusEl.textContent = '座標からの郵便番号検索に失敗しました';
                }
            }
        };

        const schedule = () => {
            clearTimeout(timer);
            timer = setTimeout(run, 400);
        };

        latInput.addEventListener('change', schedule);
        lngInput.addEventListener('change', schedule);
        form.addEventListener('loans:coordinates', schedule);

        form.dataset.reverseReady = '1';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initReverseGeocode());
} else {
    initReverseGeocode();
}
