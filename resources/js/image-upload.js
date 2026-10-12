/**
 * 物件画像の複数選択・クライアント圧縮・プレビュー。
 */

const MAX_IMAGES = 10;
const MAX_WIDTH = 1600;
const JPEG_QUALITY = 0.72;

async function compressImage(file) {
    if (! file.type.startsWith('image/')) {
        return null;
    }

    const bitmap = await createImageBitmap(file);
    const scale = Math.min(1, MAX_WIDTH / bitmap.width);
    const width = Math.max(1, Math.round(bitmap.width * scale));
    const height = Math.max(1, Math.round(bitmap.height * scale));

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    if (! ctx) {
        bitmap.close?.();
        return file;
    }

    ctx.drawImage(bitmap, 0, 0, width, height);
    bitmap.close?.();

    const blob = await new Promise((resolve) => {
        canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY);
    });

    if (! blob) {
        return file;
    }

    const baseName = file.name.replace(/\.[^.]+$/, '') || 'image';

    return new File([blob], `${baseName}.jpg`, {
        type: 'image/jpeg',
        lastModified: Date.now(),
    });
}

function formatBytes(bytes) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }
    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

function renderPreviews(container, files) {
    container.innerHTML = '';
    files.forEach((file, index) => {
        const url = URL.createObjectURL(file);
        const item = document.createElement('div');
        item.className = 'overflow-hidden border border-line bg-suumo-muted';
        item.innerHTML = `
            <img src="${url}" alt="プレビュー ${index + 1}" class="aspect-[4/3] h-28 w-full object-cover">
            <p class="px-2 py-1 text-[11px] text-muted">${formatBytes(file.size)}</p>
        `;
        container.appendChild(item);
    });
}

function initImageUpload(root = document) {
    root.querySelectorAll('[data-image-upload]').forEach((block) => {
        if (block.dataset.imageReady === '1') {
            return;
        }

        const input = block.querySelector('[data-image-input]');
        const preview = block.querySelector('[data-image-preview]');
        const status = block.querySelector('[data-image-status]');

        if (! input || ! preview) {
            return;
        }

        input.addEventListener('change', async () => {
            const selected = Array.from(input.files || []);
            if (selected.length === 0) {
                preview.innerHTML = '';
                if (status) {
                    status.textContent = '';
                }
                return;
            }

            if (status) {
                status.textContent = '画像を圧縮しています…';
            }

            const limited = selected.slice(0, MAX_IMAGES);
            const compressed = [];
            let originalTotal = 0;
            let compressedTotal = 0;

            for (const file of limited) {
                originalTotal += file.size;
                try {
                    const next = await compressImage(file);
                    if (next) {
                        compressed.push(next);
                        compressedTotal += next.size;
                    }
                } catch {
                    compressed.push(file);
                    compressedTotal += file.size;
                }
            }

            const transfer = new DataTransfer();
            compressed.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            renderPreviews(preview, compressed);

            if (status) {
                const saved = Math.max(0, originalTotal - compressedTotal);
                status.textContent = selected.length > MAX_IMAGES
                    ? `先頭${MAX_IMAGES}枚を圧縮しました（約 ${formatBytes(saved)} 削減）`
                    : `圧縮しました（約 ${formatBytes(saved)} 削減）`;
            }
        });

        block.dataset.imageReady = '1';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initImageUpload());
} else {
    initImageUpload();
}
