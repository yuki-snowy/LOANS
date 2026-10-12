/**
 * 四角枠＋十字架の画像追加 UI（サイズ超過は圧縮）。
 */

const MAX_IMAGES = 10;
const MAX_EDGE = 1280;
const MAX_BYTES = 800 * 1024;
const JPEG_QUALITY_START = 0.78;

async function compressImage(file) {
    if (! file.type.startsWith('image/')) {
        return null;
    }

    const bitmap = await createImageBitmap(file);
    const scale = Math.min(1, MAX_EDGE / Math.max(bitmap.width, bitmap.height));
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

    let quality = JPEG_QUALITY_START;
    let blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));

    while (blob && blob.size > MAX_BYTES && quality > 0.45) {
        quality -= 0.08;
        blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
    }

    if (! blob) {
        return file;
    }

    // 元が十分小さく、縮小も不要ならそのまま
    if (file.size <= MAX_BYTES && scale === 1 && file.type === 'image/jpeg') {
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

function createAddTile(input) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'image-tile image-tile-add';
    button.setAttribute('aria-label', '画像を追加');
    button.innerHTML = '<span class="image-tile-cross" aria-hidden="true"></span>';
    button.addEventListener('click', () => input.click());

    return button;
}

function createPreviewTile(file, onRemove) {
    const url = URL.createObjectURL(file);
    const tile = document.createElement('div');
    tile.className = 'image-tile image-tile-preview';
    tile.innerHTML = `
        <img src="${url}" alt="プレビュー" class="h-full w-full object-cover">
        <button type="button" class="image-tile-remove" aria-label="この画像を外す">×</button>
        <span class="image-tile-size">${formatBytes(file.size)}</span>
    `;
    tile.querySelector('.image-tile-remove')?.addEventListener('click', () => {
        URL.revokeObjectURL(url);
        onRemove();
    });

    return tile;
}

function syncInputFiles(input, files) {
    const transfer = new DataTransfer();
    files.forEach((file) => transfer.items.add(file));
    input.files = transfer.files;
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

        /** @type {File[]} */
        let currentFiles = [];

        const renderTiles = () => {
            preview.innerHTML = '';

            currentFiles.forEach((file, index) => {
                preview.appendChild(
                    createPreviewTile(file, () => {
                        currentFiles = currentFiles.filter((_, i) => i !== index);
                        syncInputFiles(input, currentFiles);
                        renderTiles();
                        if (status) {
                            status.textContent = currentFiles.length ? `${currentFiles.length}枚選択中` : '';
                        }
                    }),
                );
            });

            if (currentFiles.length < MAX_IMAGES) {
                preview.appendChild(createAddTile(input));
            }
        };

        renderTiles();

        input.addEventListener('change', async () => {
            const selected = Array.from(input.files || []);
            if (selected.length === 0) {
                return;
            }

            // 追加選択分だけ取り込む（既存プレビューは currentFiles 側で保持）
            const incoming = selected.filter(
                (file) => ! currentFiles.some(
                    (kept) => kept.name === file.name && kept.size === file.size && kept.lastModified === file.lastModified,
                ),
            );

            if (status) {
                status.textContent = '画像を圧縮しています…';
            }

            const room = Math.max(0, MAX_IMAGES - currentFiles.length);
            const limited = incoming.slice(0, room);
            let originalTotal = 0;
            let compressedTotal = 0;

            for (const file of limited) {
                originalTotal += file.size;
                try {
                    const next = await compressImage(file);
                    if (next) {
                        currentFiles.push(next);
                        compressedTotal += next.size;
                    }
                } catch {
                    currentFiles.push(file);
                    compressedTotal += file.size;
                }
            }

            currentFiles = currentFiles.slice(0, MAX_IMAGES);
            syncInputFiles(input, currentFiles);
            renderTiles();

            if (status) {
                const saved = Math.max(0, originalTotal - compressedTotal);
                const overflow = incoming.length > room;
                status.textContent = overflow
                    ? `最大${MAX_IMAGES}枚までです。追加分を圧縮しました（約 ${formatBytes(saved)} 削減）`
                    : `圧縮しました（約 ${formatBytes(saved)} 削減） / ${currentFiles.length}枚`;
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
