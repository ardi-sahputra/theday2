// In-memory cache for the Guest List page (same idea as checklistCache).
//
// Survives Inertia menu switches within the SPA session so returning to the
// Guest List renders instantly and revalidates in the background instead of
// showing a full-page spinner. Only the *default* (unfiltered, page 1) view is
// cached, because a fresh mount always starts from that state. A full reload or
// logout resets it.
export const guestListCache = {
    guests:     null, // array | null  (null = never loaded this session)
    guestsMeta: null, // object | null
    summary:    null, // object | null
    categories: null, // array | null
};
