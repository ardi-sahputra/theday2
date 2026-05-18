// AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing
// Story component map and props builder for IgStoriesTemplate.vue

import StoryIntro      from './StoryIntro.vue'
import StoryCouple     from './StoryCouple.vue'
import StoryLoveStory  from './StoryLoveStory.vue'
import StoryEvents     from './StoryEvents.vue'
import StoryCountdown  from './StoryCountdown.vue'
import StoryGallery    from './StoryGallery.vue'
import StoryRsvp       from './StoryRsvp.vue'
import StoryGift       from './StoryGift.vue'
import StoryWishes     from './StoryWishes.vue'
import StoryOutro      from './StoryOutro.vue'

export const COMPONENT_MAP = {
    opening:    StoryIntro,
    couple:     StoryCouple,
    love_story: StoryLoveStory,
    events:     StoryEvents,
    countdown:  StoryCountdown,
    gallery:    StoryGallery,
    rsvp:       StoryRsvp,
    gift:       StoryGift,
    wishes:     StoryWishes,
    closing:    StoryOutro,
}

export function storyComponent(key) {
    return COMPONENT_MAP[key] || StoryIntro
}

export function buildStoryProps(key, data) {
    const {
        groomName, brideName, groomNick, brideNick,
        coverPhotoUrl, events, galleries,
        openingText, closingText, firstEventDate,
        countdown, targetDate, pad,
        rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
        msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
        localMessages, guestName, igUsername, igBrandName,
        groomParents, brideParents, loveStoryItems, giftAccounts, showWatermark,
    } = data

    switch (key) {
        case 'opening':
            return { groomNick, brideNick, firstEventDate, openingText }
        case 'couple':
            return { groomName, brideName, groomParents, brideParents, coverUrl: coverPhotoUrl, igUsername }
        case 'love_story':
            return { stories: loveStoryItems }
        case 'events':
            return { events, firstEventDate }
        case 'countdown':
            return { countdown, targetDate, firstEventDate, pad }
        case 'gallery':
            return { galleries }
        case 'rsvp':
            return { rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp }
        case 'gift':
            return { accountsCount: giftAccounts.length }
        case 'wishes':
            return { localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage, guestName }
        case 'closing':
            return { brandName: igBrandName, groomNick, brideNick, closingText, showWatermark }
        default:
            return {}
    }
}
