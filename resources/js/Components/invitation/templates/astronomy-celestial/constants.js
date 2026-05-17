// Indonesian sky reference — Jakarta.
// HARDCODED per spec decision 2026-05-17. Do NOT replace with event coordinates,
// do NOT parse `events[].maps_url`, do NOT geocode. The v1 reference point is fixed.
// Personalization comes from the unique combination of event_date + start_time only.

export const STAR_MAP_LAT = -6.2088
export const STAR_MAP_LNG = 106.8456
export const STAR_MAP_TZ  = '+07:00'
export const STAR_MAP_TZ_LABEL = 'WIB'
export const STAR_MAP_PLACE   = 'JAKARTA'

// Display helpers
export function formatLatLabel() {
    return `${Math.abs(STAR_MAP_LAT).toFixed(4)}°S`
}
export function formatLngLabel() {
    return `${Math.abs(STAR_MAP_LNG).toFixed(4)}°E`
}
