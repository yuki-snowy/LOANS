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

function initBackToTop() {
    const button = document.querySelector('[data-back-to-top]');
    if (! button) {
        return;
    }

    const threshold = 280;
    let ticking = false;

    const sync = () => {
        const show = window.scrollY > threshold;
        button.hidden = ! show;
        button.classList.toggle('is-visible', show);
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (! ticking) {
            window.requestAnimationFrame(sync);
            ticking = true;
        }
    }, { passive: true });

    button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    sync();
}

onReady(() => {
    initBackToTop();

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
