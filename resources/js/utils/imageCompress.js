// resources/js/utils/imageCompress.js
//
// Client-side image optimisation before upload: resize the longest edge down
// and re-encode to WebP. A 12MP phone photo (~4-5 MB) becomes ~150-250 KB,
// which makes invitations load far faster for guests and cuts storage/bandwidth.
//
// Safe by design — if anything is off (non-image, SVG, animated GIF, WebP not
// supported, or the result ends up larger), it returns the ORIGINAL file
// untouched, so callers can always just `await compressImage(file)`.

const SKIP_TYPES = ['image/gif', 'image/svg+xml'];

async function loadBitmap(file) {
    // createImageBitmap honours EXIF orientation and is fastest where available.
    if (typeof createImageBitmap === 'function') {
        try {
            return await createImageBitmap(file, { imageOrientation: 'from-image' });
        } catch {
            /* fall through to <img> */
        }
    }
    return await new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
        img.onerror = (e) => { URL.revokeObjectURL(url); reject(e); };
        img.src = url;
    });
}

/**
 * @param {File} file
 * @param {{maxEdge?:number, quality?:number, mimeType?:string}} [opts]
 * @returns {Promise<File>} compressed File (or the original if skipped/failed)
 */
export async function compressImage(file, opts = {}) {
    const { maxEdge = 1600, quality = 0.8, mimeType = 'image/webp' } = opts;

    if (!file || !(file instanceof File)) return file;
    if (!file.type.startsWith('image/') || SKIP_TYPES.includes(file.type)) return file;

    try {
        const bitmap = await loadBitmap(file);
        const srcW = bitmap.width;
        const srcH = bitmap.height;
        if (!srcW || !srcH) { bitmap.close?.(); return file; }

        const scale = Math.min(1, maxEdge / Math.max(srcW, srcH));
        const w = Math.round(srcW * scale);
        const h = Math.round(srcH * scale);

        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        if (!ctx) { bitmap.close?.(); return file; }
        ctx.drawImage(bitmap, 0, 0, w, h);
        bitmap.close?.();

        const blob = await new Promise((resolve) =>
            canvas.toBlob(resolve, mimeType, quality)
        );
        if (!blob) return file;

        // Already small & not resized, or compression made it bigger → keep original.
        if (scale === 1 && blob.size >= file.size) return file;

        const outExt = blob.type === 'image/webp' ? 'webp'
            : (blob.type.split('/')[1] || 'jpg');
        const baseName = file.name.replace(/\.[^.]+$/, '') || 'photo';

        return new File([blob], `${baseName}.${outExt}`, {
            type: blob.type,
            lastModified: file.lastModified,
        });
    } catch {
        return file; // any failure → upload the original
    }
}
