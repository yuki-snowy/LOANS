/**
 * 郵便番号から都道府県・住所を自動入力（zipcloud・無料・APIキー不要）。
 */
const ZIPCLOUD_URL = 'https://zipcloud.ibsnet.co.jp/api/search';

function digitsOnly(value) {
    return String(value || '').replace(/\D/g, '');
}

function formatPostal(digits) {
    if (digits.length !== 7) {
        return digits;
    }

    return `${digits.slice(0, 3)}-${digits.slice(3)}`;
}

async function lookupPostalCode(postalCode) {
    const digits = digitsOnly(postalCode);
    if (digits.length !== 7) {
        return null;
    }

    const response = await fetch(`${ZIPCLOUD_URL}?zipcode=${digits}`);
    if (!response.ok) {
        throw new Error('postal lookup failed');
    }

    const payload = await response.json();
    if (payload.status !== 200 || !payload.results?.length) {
        return null;
    }

    const result = payload.results[0];

    return {
        postalCode: formatPostal(digits),
        prefecture: result.address1 || '',
        address: `${result.address2 || ''}${result.address3 || ''}`,
    };
}

function initPostalAutofill(root = document) {
    root.querySelectorAll('[data-postal-autofill]').forEach((form) => {
        if (form.dataset.postalReady === '1') {
            return;
        }

        const postalInput = form.querySelector('[data-postal-code]');
        const prefectureInput = form.querySelector('[data-prefecture]');
        const addressInput = form.querySelector('[data-address]');
        const statusEl = form.querySelector('[data-postal-status]');

        if (!postalInput || !prefectureInput || !addressInput) {
            return;
        }

        let timer = null;

        const runLookup = async () => {
            const digits = digitsOnly(postalInput.value);
            if (digits.length !== 7) {
                return;
            }

            postalInput.value = formatPostal(digits);
            if (statusEl) {
                statusEl.textContent = '住所を検索中…';
            }

            try {
                const result = await lookupPostalCode(digits);
                if (!result) {
                    if (statusEl) {
                        statusEl.textContent = '該当する住所が見つかりませんでした';
                    }
                    return;
                }

                prefectureInput.value = result.prefecture;
                if (!addressInput.value || addressInput.dataset.autofilled === '1') {
                    addressInput.value = result.address;
                    addressInput.dataset.autofilled = '1';
                }

                if (statusEl) {
                    statusEl.textContent = '住所を入力しました';
                }
            } catch {
                if (statusEl) {
                    statusEl.textContent = '住所検索に失敗しました';
                }
            }
        };

        postalInput.addEventListener('input', () => {
            clearTimeout(timer);
            const digits = digitsOnly(postalInput.value);
            if (digits.length === 7) {
                timer = setTimeout(runLookup, 250);
            } else if (statusEl) {
                statusEl.textContent = '';
            }
        });

        postalInput.addEventListener('blur', () => {
            const digits = digitsOnly(postalInput.value);
            if (digits.length === 7) {
                postalInput.value = formatPostal(digits);
                runLookup();
            }
        });

        addressInput.addEventListener('input', () => {
            addressInput.dataset.autofilled = '0';
        });

        form.dataset.postalReady = '1';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initPostalAutofill());
} else {
    initPostalAutofill();
}
