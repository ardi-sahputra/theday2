// In-memory cache for the checklist page data.
//
// Inertia keeps the JS app alive across navigations (it only swaps the page
// component), so this module-level object survives menu switches within the
// same SPA session. Returning to the Planner can then render instantly from
// cache and revalidate quietly in the background, instead of showing a spinner
// on every visit. A full page reload (or logout) resets it.
export const checklistCache = {
    tasks: null,    // array | null  (null = never loaded this session)
    summary: null,  // object | null
};
