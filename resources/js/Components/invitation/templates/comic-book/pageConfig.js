// Comic Book Template — Page Configuration
// Maps 10 comic pages to catalog section keys.
// Each entry: { id, sectionKey, title, layout, panels, sfx, bubbles }
//
// Legal: all SFX/labels are generic comic vocabulary.
// NO Marvel/DC/named character references.

export const PAGES = [
    {
        id:         'origin',
        sectionKey: 'opening',
        title:      'EPISODE 1: THE BIG DAY',
        layout:     'splash',       // single large splash panel
        panels:     1,
        sfx:        'kapow',
        bubbles:    ['narration'],
    },
    {
        id:         'heroes',
        sectionKey: 'couple',
        title:      'OUR HEROES',
        layout:     'duo',          // 2-panel side-by-side
        panels:     2,
        sfx:        'pow',
        bubbles:    ['speech', 'speech'],
    },
    {
        id:         'flashback',
        sectionKey: 'love_story',
        title:      'THE ORIGIN STORY',
        layout:     'grid6',        // 3×2 grid
        panels:     6,
        sfx:        'wow',
        bubbles:    ['narration'],
    },
    {
        id:         'team_up',
        sectionKey: 'events',
        title:      'EVENT SCHEDULE',
        layout:     'stack',        // stacked vertical panels
        panels:     3,              // up to 3 events
        sfx:        'bam',
        bubbles:    ['narration'],
    },
    {
        id:         'countdown',
        sectionKey: 'countdown',
        title:      'COUNTDOWN…',
        layout:     'digits',       // 4 sub-panels for days/hours/min/sec
        panels:     4,
        sfx:        'wham',
        bubbles:    [],
    },
    {
        id:         'gallery',
        sectionKey: 'gallery',
        title:      'PHOTO ALBUM',
        layout:     'photoGrid',    // responsive photo grid
        panels:     6,              // 2×2 or 3×2
        sfx:        'wow',
        bubbles:    [],
    },
    {
        id:         'rsvp',
        sectionKey: 'rsvp',
        title:      'RSVP CALL TO ACTION!',
        layout:     'broadcast',    // emergency broadcast style
        panels:     1,
        sfx:        'pow',
        bubbles:    ['shout'],
    },
    {
        id:         'support',
        sectionKey: 'gift',
        title:      'TIP JAR (Bonus Issue!)',
        layout:     'tipJar',       // intro panel + account panels
        panels:     2,
        sfx:        'kapow',
        bubbles:    ['narration'],
    },
    {
        id:         'tribute',
        sectionKey: 'wishes',
        title:      'READER LETTERS',
        layout:     'letterGrid',   // speech bubble grid + form
        panels:     2,
        sfx:        'wham',
        bubbles:    ['speech'],
    },
    {
        id:         'closing',
        sectionKey: 'closing',
        title:      'TO BE CONTINUED…',
        layout:     'cliffhanger',  // single splash closing panel
        panels:     1,
        sfx:        'wow',
        bubbles:    ['narration'],
    },
]

export const PAGE_BY_KEY = Object.fromEntries(PAGES.map(p => [p.sectionKey, p]))
