// resources/js/utils/imageColors.js
//
// Client-side dominant-color extraction for moodboard pins. Pure canvas, no deps.
// Draws the image onto a tiny offscreen canvas (longest edge ~80px), quantizes
// RGB into coarse buckets, drops near-white/near-black noise, and returns the
// top `count` distinct colors by frequency as `#rrggbb` hex.
//
// Safe by design — any failure (non-image, decode error, tainted canvas) returns
// an empty array so callers can always `await extractColors(file)` without try.

const MAX_EDGE = 80;       // downscale longest edge to this many px before sampling
const BUCKET_STEP = 24;    // quantization bucket size per channel (~10 buckets/channel)

async function toBitmap(input) {
    // Already a usable drawable image element.
    if (typeof HTMLImageElement !== 'undefined' && input instanceof HTMLImageElement) {
        if (input.complete && input.naturalWidth) return input;
        await new Promise((resolve, reject) => {
            input.onload = resolve;
            input.onerror = reject;
        });
        return input;
    }

    // File or Blob → prefer createImageBitmap (fast, honours EXIF).
    if (typeof createImageBitmap === 'function') {
        try {
            return await createImageBitmap(input, { imageOrientation: 'from-image' });
        } catch {
            /* fall through to <img> */
        }
    }

    return await new Promise((resolve, reject) => {
        const url = URL.createObjectURL(input);
        const img = new Image();
        img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
        img.onerror = (e) => { URL.revokeObjectURL(url); reject(e); };
        img.src = url;
    });
}

function toHex(r, g, b) {
    const h = (n) => n.toString(16).padStart(2, '0');
    return `#${h(r)}${h(g)}${h(b)}`;
}

/**
 * Extract the dominant colors from an image.
 * @param {File|Blob|HTMLImageElement} fileOrBlobOrImg
 * @param {number} [count=4] number of distinct hex colors to return
 * @returns {Promise<string[]>} hex strings sorted by frequency (most frequent first)
 */
export async function extractColors(fileOrBlobOrImg, count = 4) {
    if (!fileOrBlobOrImg) return [];

    try {
        const bitmap = await toBitmap(fileOrBlobOrImg);
        const srcW = bitmap.width || bitmap.naturalWidth;
        const srcH = bitmap.height || bitmap.naturalHeight;
        if (!srcW || !srcH) { bitmap.close?.(); return []; }

        const scale = Math.min(1, MAX_EDGE / Math.max(srcW, srcH));
        const w = Math.max(1, Math.round(srcW * scale));
        const h = Math.max(1, Math.round(srcH * scale));

        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        if (!ctx) { bitmap.close?.(); return []; }
        ctx.drawImage(bitmap, 0, 0, w, h);
        bitmap.close?.();

        let data;
        try {
            data = ctx.getImageData(0, 0, w, h).data;
        } catch {
            return []; // tainted canvas / cross-origin
        }

        // Quantize into coarse RGB buckets, tally frequency, remember a
        // representative (the first sampled) color per bucket for accuracy.
        const buckets = new Map();
        for (let i = 0; i < data.length; i += 4) {
            const a = data[i + 3];
            if (a < 125) continue; // skip mostly-transparent pixels

            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];

            // Drop near-white and near-black noise.
            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            if (max > 244 && min > 244) continue;   // near-white
            if (max < 18) continue;                 // near-black

            const key =
                (Math.floor(r / BUCKET_STEP) << 16) |
                (Math.floor(g / BUCKET_STEP) << 8) |
                Math.floor(b / BUCKET_STEP);

            const entry = buckets.get(key);
            if (entry) {
                entry.count++;
                entry.r += r; entry.g += g; entry.b += b;
            } else {
                buckets.set(key, { count: 1, r, g, b });
            }
        }

        if (buckets.size === 0) return [];

        const sorted = [...buckets.values()].sort((a, b) => b.count - a.count);

        const out = [];
        const seen = new Set();
        for (const e of sorted) {
            const r = Math.round(e.r / e.count);
            const g = Math.round(e.g / e.count);
            const b = Math.round(e.b / e.count);
            const hex = toHex(r, g, b);
            if (seen.has(hex)) continue;
            seen.add(hex);
            out.push(hex);
            if (out.length >= count) break;
        }
        return out;
    } catch {
        return [];
    }
}
