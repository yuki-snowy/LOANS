/**
 * エントリは軽量に保ち、必要な画面だけ動的 import する。
 */

function onReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
}

onReady(() => {
    const tasks = [];
    const needsMap = document.querySelector('[data-loans-map], [data-load-map], [data-location-search]');

    if (needsMap) {
        tasks.push(
            import('./loans-map.js').then(async (mapModule) => {
                mapModule.bootMaps();
                if (document.querySelector('[data-location-search]')) {
                    await import('./location-search.js');
                }
            }),
        );
    }

    if (document.querySelector('[data-postal-autofill]')) {
        tasks.push(import('./postal-autofill.js'));
    }

    if (document.querySelector('[data-reverse-geocode]')) {
        tasks.push(import('./reverse-geocode.js'));
    }

    if (document.querySelector('[data-image-upload]')) {
        tasks.push(import('./image-upload.js'));
    }

    Promise.all(tasks).catch((error) => {
        console.error(error);
    });
});
