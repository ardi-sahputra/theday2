// resources/js/Components/invitation/templates/vinyl-record/track-config.js
// 12-track narrative mapping to section catalog. Order + ids are stable; AI MUST NOT rename.
// Side A = First Listen (intro), Side B = Deeper Cuts (commitment).

export const TRACK_LIST = [
    { id: 'A1', side: 'A', title: 'Welcome',         key: 'opening',     duration: '1:23' },
    { id: 'A2', side: 'A', title: 'Two Hearts',      key: 'couple',      duration: '2:45' },
    { id: 'A3', side: 'A', title: 'The Calendar',    key: 'events',      duration: '1:55' },
    { id: 'A4', side: 'A', title: 'Countdown',       key: 'countdown',   duration: '3:33' },
    { id: 'A5', side: 'A', title: 'Our Story',       key: 'love_story',  duration: '5:12' },
    { id: 'A6', side: 'A', title: 'Memories',        key: 'gallery',     duration: '4:01' },
    { id: 'B1', side: 'B', title: 'RSVP Anthem',     key: 'rsvp',        duration: '2:30' },
    { id: 'B2', side: 'B', title: 'Token of Love',   key: 'gift',        duration: '1:48' },
    { id: 'B3', side: 'B', title: 'Voices of Joy',   key: 'wishes',      duration: '3:15' },
    { id: 'B4', side: 'B', title: 'Sacred Verse',    key: 'quote',       duration: '1:30' },
    { id: 'B5', side: 'B', title: 'Theme Song',      key: 'music',       duration: 'auto' },
    { id: 'B6', side: 'B', title: 'Encore',          key: 'closing',     duration: '4:20' },
]

export const LABEL_COLOR_HEX = {
    red:   '#C73E3A',
    blue:  '#2A4D8C',
    green: '#5F7048',
    gold:  '#B8902F',
}

export const GRAIN_OPACITY = {
    subtle: 0.08,
    medium: 0.14,
    strong: 0.20,
}
