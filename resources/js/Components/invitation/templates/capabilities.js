// resources/js/Components/invitation/templates/capabilities.js
//
// Per-template field/section capability map. The editor uses this to HIDE
// inputs a template doesn't actually render — e.g. a no-photo template
// shouldn't show photo uploads, a template without an Instagram line
// shouldn't ask for Instagram handles.
//
// Source of truth: what each template's Vue component actually renders.
// When you add/change a template, update its entry here (or rely on the
// defaults). Anything not listed inherits DEFAULT_CAPS (everything on),
// so untouched templates keep showing every input.
//
// ─────────────────────────────────────────────────────────────────────────
// THIS FILE IS THE SINGLE SOURCE OF TRUTH for editor field/section gating.
//
// Adding a NEW editor field/section that some templates won't render?
//   1. Add its key + default to DEFAULT_CAPS below.
//   2. Gate the input in the editor panel with `v-if="caps.<key> !== false"`
//      (or `v-if="caps.<key>"` for opt-in like liveStreaming).
//   3. Override per-slug in TEMPLATE_CAPS for templates that differ.
//
// Changing a template so it now renders (or stops rendering) a field?
//   → update that slug's entry in TEMPLATE_CAPS here. (This map does NOT
//     auto-sync with template code — keep it in step by hand.)
// ─────────────────────────────────────────────────────────────────────────
//
// Keys map to editor inputs:
//   photos       — couple/cover photo uploads
//   instagram    — groom/bride Instagram handle fields
//   parents      — "Putra dari" / "Putri dari" parent names
//   quote        — opening quote / salam
//   gallery      — photo gallery section
//   loveStory    — "Kisah Kami" timeline
//   rsvp         — RSVP form
//   gift         — gift registry
//   envelope     — digital envelope (QRIS / rekening)
//   wishes       — public guestbook
//   liveStreaming— YouTube live stream
//   video        — prewedding / after-movie video block
//   additionalInfo— free-text notes (dress code, protokol)
//   music        — background music

export const DEFAULT_CAPS = {
    photos: true,
    instagram: false,    // most templates don't render IG handles — opt in below
    parents: true,
    quote: true,
    gallery: true,
    loveStory: true,
    rsvp: true,
    gift: true,
    envelope: true,
    wishes: true,
    liveStreaming: false, // only templates that render a stream opt in
    video: false,          // only templates that render a video block opt in
    additionalInfo: false, // only templates that render free-text notes opt in
    music: true,
};

// Per-slug overrides (only what differs from DEFAULT_CAPS).
// Source: audit of each template component (what it actually renders).
export const TEMPLATE_CAPS = {
    // Live streaming sections
    'nusantara':         { liveStreaming: true, video: true, additionalInfo: true },
    'pearl':             { liveStreaming: true, video: true, additionalInfo: true },
    // Templates that DO show Instagram handles
    'beach':             { instagram: true },
    'garden':            { instagram: true },
    'night-sky':         { instagram: true },
    'ig-stories':        { instagram: true, quote: false },
    // No quote/salam block
    'spotify-wrapped':   { quote: false },
    'year-scrubber':     { quote: false },
    'comic-book':        { quote: false },
    // Default-renderer template: renders cover/opening/events/gallery/rsvp/
    // wishes/closing only. No couple photos, and no love_story/quote/gift/
    // envelope sections — so don't offer toggles the renderer can't honor.
    'bunga-abadi':       { quote: false, photos: false, loveStory: false, gift: false, envelope: false },
    // No-photo (text/illustration) templates
    'letterpress':       { photos: false },
    'islamic-geometric': { photos: false },
    'botanical':         { photos: false },
    'ayat-hadits':       { photos: false },
    // Minimal scene template — no couple photos/parents/quote
    'snow-globe':        { photos: false, parents: false, quote: false },
    // No parent names
    'tarot-reading':     { parents: false },
};

/**
 * Resolved capabilities for a template slug.
 * @param {string} slug
 * @returns {Record<string, boolean>}
 */
export function templateCaps(slug) {
    return { ...DEFAULT_CAPS, ...(TEMPLATE_CAPS[slug] ?? {}) };
}
