<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-sc" :data-section="sectionKey">
        <template v-if="sectionKey === 'opening'">
            <p class="th-sc__opening"><span class="th-sc__dropcap">{{ openingFirstChar }}</span>{{ openingRest }}</p>
            <hr class="th-sc__inkline"/>
        </template>

        <template v-else-if="sectionKey === 'couple'">
            <div class="th-sc__couple">
                <figure class="th-sc__person">
                    <div v-if="api.invitation.groom_photo_url" class="th-sc__photo">
                        <img :src="api.invitation.groom_photo_url" :alt="api.groomName.value"/>
                    </div>
                    <figcaption>
                        <h3>{{ api.groomName.value }}</h3>
                        <p v-if="api.invitation.groom_father || api.invitation.groom_mother">
                            Putra dari
                            <span v-if="api.invitation.groom_father">{{ api.invitation.groom_father }}</span>
                            <span v-if="api.invitation.groom_father && api.invitation.groom_mother"> &amp; </span>
                            <span v-if="api.invitation.groom_mother">{{ api.invitation.groom_mother }}</span>
                        </p>
                    </figcaption>
                </figure>
                <hr class="th-sc__hairline"/>
                <figure class="th-sc__person">
                    <div v-if="api.invitation.bride_photo_url" class="th-sc__photo">
                        <img :src="api.invitation.bride_photo_url" :alt="api.brideName.value"/>
                    </div>
                    <figcaption>
                        <h3>{{ api.brideName.value }}</h3>
                        <p v-if="api.invitation.bride_father || api.invitation.bride_mother">
                            Putri dari
                            <span v-if="api.invitation.bride_father">{{ api.invitation.bride_father }}</span>
                            <span v-if="api.invitation.bride_father && api.invitation.bride_mother"> &amp; </span>
                            <span v-if="api.invitation.bride_mother">{{ api.invitation.bride_mother }}</span>
                        </p>
                    </figcaption>
                </figure>
            </div>
        </template>

        <template v-else-if="sectionKey === 'events'">
            <article v-for="ev in api.events.value" :key="ev.id" class="th-sc__event">
                <h4>{{ ev.event_name }}</h4>
                <p class="th-sc__event-date">{{ formatDate(ev.event_date) }}</p>
                <p class="th-sc__event-time">{{ ev.event_time }} {{ ev.timezone || '' }}</p>
                <p class="th-sc__event-addr">{{ ev.address }}</p>
                <a v-if="ev.maps_url" :href="ev.maps_url" target="_blank" rel="noopener" class="th-sc__btn">
                    LIHAT DI PETA DUNIA
                </a>
            </article>
        </template>

        <template v-else-if="sectionKey === 'countdown'">
            <div v-if="!api.countdown.value.expired" class="th-sc__count">
                <div v-for="part in countParts" :key="part.k" class="th-sc__count-cell">
                    <span class="th-sc__count-num">{{ api.pad(part.v) }}</span>
                    <span class="th-sc__count-lbl">{{ part.l }}</span>
                </div>
            </div>
            <p v-else class="th-sc__empty">Hari bahagia telah tiba.</p>
        </template>

        <template v-else-if="sectionKey === 'love_story'">
            <ol class="th-sc__timeline">
                <li v-for="(story, i) in stories" :key="i">
                    <span class="th-sc__story-x" aria-hidden="true">&#10006;</span>
                    <time v-if="story.date">{{ story.date }}</time>
                    <h4 v-if="story.title">{{ story.title }}</h4>
                    <p v-if="story.description">{{ story.description }}</p>
                </li>
            </ol>
        </template>

        <template v-else-if="sectionKey === 'gallery'">
            <div class="th-sc__gallery">
                <figure v-for="(g, i) in api.galleries.value" :key="g.id ?? i"
                        class="th-sc__photo-frame" :style="{ transform: `rotate(${tiltFor(i)}deg)` }">
                    <img :src="g.image_url ?? g.file_url" :alt="g.caption || ''"/>
                </figure>
            </div>
        </template>

        <template v-else-if="sectionKey === 'rsvp'">
            <p class="th-sc__rsvp-sub">"Tandai keberangkatanmu di buku tamu."</p>
            <form class="th-sc__form" @submit.prevent="api.submitRsvp">
                <label><span>NAMA</span><input v-model="api.rsvpForm.guest_name" type="text" required/></label>
                <label><span>KEHADIRAN</span>
                    <select v-model="api.rsvpForm.attendance" required>
                        <option value="">— Pilih —</option>
                        <option value="attending">Berlayar (Hadir)</option>
                        <option value="not_attending">Belum Bisa</option>
                        <option value="maybe">Mungkin</option>
                    </select>
                </label>
                <label><span>JUMLAH</span><input v-model.number="api.rsvpForm.guest_count" type="number" min="1" max="9"/></label>
                <label><span>CATATAN</span><textarea v-model="api.rsvpForm.notes" rows="2"/></label>
                <button type="submit" class="th-sc__btn" :disabled="api.rsvpSubmitting.value">
                    {{ api.rsvpSubmitting.value ? 'MENGIRIM…' : 'BERLAYAR / KIRIM JAWABAN' }}
                </button>
                <p v-if="api.rsvpSuccess.value" class="th-sc__ok">JAWABAN TERCATAT</p>
                <p v-if="api.rsvpError.value" class="th-sc__err">{{ api.rsvpError.value }}</p>
            </form>
        </template>

        <template v-else-if="sectionKey === 'gift'">
            <p class="th-sc__gift-sub">"Doa adalah harta yang paling berharga. Namun jika Anda berkenan menyumbang koin emas…"</p>
            <article v-for="acc in accounts" :key="acc.account_number" class="th-sc__gift-card">
                <p class="th-sc__gift-bank">{{ acc.bank_name }}</p>
                <p class="th-sc__gift-name">{{ acc.account_name }}</p>
                <p class="th-sc__gift-num">{{ acc.account_number }}</p>
                <button type="button" class="th-sc__btn th-sc__btn--sm" @click="api.copyToClipboard(acc.account_number)">
                    SALIN KOIN
                </button>
            </article>
        </template>

        <template v-else-if="sectionKey === 'wishes'">
            <form class="th-sc__form" @submit.prevent="api.submitMessage">
                <label><span>NAMA</span><input v-model="api.msgForm.name" type="text" required/></label>
                <label><span>PESAN</span><textarea v-model="api.msgForm.message" rows="3" required/></label>
                <button type="submit" class="th-sc__btn" :disabled="api.msgSubmitting.value">
                    {{ api.msgSubmitting.value ? 'MELEPAS BOTOL…' : 'LEPASKAN BOTOL' }}
                </button>
                <p v-if="api.msgError.value" class="th-sc__err">{{ api.msgError.value }}</p>
            </form>
            <ul v-if="api.localMessages.value.length" class="th-sc__wish-list">
                <li v-for="m in api.localMessages.value" :key="m.id">
                    <h5>{{ m.name }}</h5><p>{{ m.message }}</p>
                </li>
            </ul>
            <p v-else class="th-sc__empty">"Jadilah botol pertama yang dilemparkan ke laut."</p>
        </template>

        <template v-else-if="sectionKey === 'quote'">
            <div class="th-sc__quote">
                <span class="th-sc__quote-mark" aria-hidden="true">&ldquo;</span>
                <p>{{ api.sectionData('quote').text }}</p>
                <p v-if="api.sectionData('quote').source" class="th-sc__quote-src">
                    — {{ api.sectionData('quote').source }}
                </p>
            </div>
        </template>

        <template v-else-if="sectionKey === 'closing'">
            <div class="th-sc__closing">
                <svg class="th-sc__anchor" viewBox="0 0 96 96" aria-hidden="true">
                    <circle cx="48" cy="20" r="8" fill="none" stroke="currentColor" stroke-width="3"/>
                    <line x1="48" y1="28" x2="48" y2="78" stroke="currentColor" stroke-width="3"/>
                    <line x1="32" y1="40" x2="64" y2="40" stroke="currentColor" stroke-width="3"/>
                    <path d="M20 70 Q48 96 76 70" fill="none" stroke="currentColor" stroke-width="3"/>
                </svg>
                <p class="th-sc__monogram">{{ initials }}</p>
                <h4>{{ api.groomName.value }} &amp; {{ api.brideName.value }}</h4>
                <hr class="th-sc__hairline"/>
                <p class="th-sc__closing-text">{{ api.closingText.value }}</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
    sectionKey: { type: String, required: true },
    api:        { type: Object, required: true },
    initials:   { type: String, default: 'A & B' },
})
const openingFirstChar = computed(() => (props.api.openingText.value || '').trimStart()[0] || '')
const openingRest = computed(() => (props.api.openingText.value || '').trimStart().slice(1))
const stories = computed(() => {
    const d = props.api.sectionData('love_story')
    return Array.isArray(d?.stories) ? d.stories : []
})
const accounts = computed(() => {
    const d = props.api.sectionData('gift')
    return Array.isArray(d?.accounts) ? d.accounts : []
})
const countParts = computed(() => {
    const c = props.api.countdown.value
    return [
        { k: 'd', v: c.days,    l: 'HARI' },
        { k: 'h', v: c.hours,   l: 'JAM' },
        { k: 'm', v: c.minutes, l: 'MENIT' },
        { k: 's', v: c.seconds, l: 'DETIK' },
    ]
})
function tiltFor(i) { return [-1, 0, 1, 0][i % 4] }
function formatDate(s) {
    if (!s) return ''
    try {
        return new Date(s).toLocaleDateString('id-ID', { year:'numeric', month:'long', day:'numeric' })
    } catch { return s }
}
</script>

<style scoped>
.th-sc { color: var(--th-ink, #3D2817); }
.th-sc h3, .th-sc h4, .th-sc h5 {
    font-family: 'IM Fell English', serif; font-style: italic;
    color: var(--th-ink, #3D2817); margin: 0 0 4px;
}
.th-sc h3 { font-size: 22px; } .th-sc h4 { font-size: 18px; } .th-sc h5 { font-size: 16px; }
.th-sc__inkline { border: 0; height: 1px; background: rgba(107,79,56,0.4); width: 60%; margin: 12px auto; }
.th-sc__hairline { border: 0; height: 1px; background: var(--th-gold-flourish, #C9A961); width: 60px; margin: 12px auto; }
.th-sc__opening { font-style: italic; }
.th-sc__dropcap {
    font-family: 'IM Fell English', serif; font-size: 56px;
    float: left; line-height: 0.85; margin: 6px 8px 0 0;
    color: var(--th-gold-flourish, #C9A961);
}
.th-sc__couple { display: grid; gap: 16px; }
.th-sc__photo {
    width: 160px; aspect-ratio: 3/4; margin: 0 auto 8px;
    background: #c8b077; overflow: hidden;
    border: 2px solid var(--th-parchment-dark, #C8B077); position: relative;
}
.th-sc__photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.th-sc__person figcaption { text-align: center; }
.th-sc__person p { font-size: 13px; color: var(--th-ink-faded, #6B4F38); margin: 0; }
.th-sc__event {
    border: 1px solid var(--th-ink-faded, #6B4F38);
    padding: 16px 18px; margin-bottom: 14px; background: rgba(242,226,181,0.4);
}
.th-sc__event h4 { font-family: 'Cinzel', serif; font-style: normal;
    letter-spacing: 0.18em; font-size: 13px; text-transform: uppercase; }
.th-sc__event-date { font-family: 'IM Fell English', serif; font-style: italic; font-size: 20px; margin: 4px 0; }
.th-sc__event-time { margin: 0; font-size: 14px; }
.th-sc__event-addr { margin: 4px 0 8px; font-style: italic; color: var(--th-ink-faded, #6B4F38); font-size: 13px; }
.th-sc__count { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.th-sc__count-cell {
    background: var(--th-parchment-light, #F2E2B5);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 8px rgba(80,50,20,0.15), inset 0 0 0 1px var(--th-ink-faded, #6B4F38);
    padding: 10px 4px 8px; text-align: center;
}
.th-sc__count-num { display: block; font-family: 'IM Fell English', serif;
    font-size: 32px; color: var(--th-ink, #3D2817); font-variant-numeric: tabular-nums; }
.th-sc__count-lbl { display: block; font-family: 'Cinzel', serif; font-size: 10px;
    letter-spacing: 0.15em; color: var(--th-ink, #3D2817); margin-top: 4px; }
.th-sc__timeline { list-style: none; padding: 0;
    border-left: 1px solid var(--th-ink-faded, #6B4F38); margin-left: 8px; }
.th-sc__timeline li { position: relative; padding: 0 0 14px 16px; }
.th-sc__story-x { position: absolute; left: -8px; top: 2px;
    color: var(--th-blood-red, #8B1A1F); font-size: 12px; }
.th-sc__timeline time { font-family: 'IM Fell English', serif; font-style: italic;
    color: var(--th-gold-flourish, #C9A961); font-size: 13px; }
.th-sc__timeline p { margin: 4px 0 0; font-size: 14px; line-height: 1.7; }
.th-sc__gallery { column-count: 2; column-gap: 8px; }
.th-sc__photo-frame { break-inside: avoid; margin: 0 0 8px;
    border: 2px solid var(--th-parchment-dark, #C8B077); background: #fff; padding: 0; }
.th-sc__photo-frame img { width: 100%; height: auto; display: block; }
.th-sc__form { display: flex; flex-direction: column; gap: 14px; }
.th-sc__form label { display: flex; flex-direction: column; gap: 6px; }
.th-sc__form span { font-family: 'Cinzel', serif; font-size: 11px;
    letter-spacing: 0.18em; color: var(--th-ink, #3D2817); }
.th-sc__form input, .th-sc__form select, .th-sc__form textarea {
    background: var(--th-parchment-light, #F2E2B5);
    border: 1px solid var(--th-ink-faded, #6B4F38);
    color: var(--th-ink, #3D2817); font-family: 'Crimson Text', serif;
    font-size: 15px; padding: 10px 12px; border-radius: 2px;
}
.th-sc__form input:focus, .th-sc__form select:focus, .th-sc__form textarea:focus {
    outline: none; border-color: var(--th-ink, #3D2817);
}
.th-sc__btn {
    display: inline-flex; align-items: center; justify-content: center;
    font-family: 'Pirata One', cursive;
    letter-spacing: 0.15em; font-size: 16px;
    color: var(--th-gold-deep, #9E7E3E);
    background: var(--th-parchment-light, #F2E2B5);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-parchment-dark, #C8B077);
    padding: 8px 22px; min-height: 44px; border-radius: 2px; cursor: pointer;
    text-decoration: none; align-self: center;
}
.th-sc__btn--sm { font-size: 13px; padding: 6px 14px; }
.th-sc__btn:hover, .th-sc__btn:focus-visible { background: var(--th-parchment, #E8D5A0); outline: none; }
.th-sc__btn[disabled] { opacity: 0.6; cursor: default; }
.th-sc__ok { color: var(--th-gold-deep, #9E7E3E); font-family: 'Cinzel', serif;
    letter-spacing: 0.18em; text-align: center; }
.th-sc__err { color: var(--th-blood-red, #8B1A1F); font-family: 'Crimson Text', serif; font-style: italic; }
.th-sc__rsvp-sub { font-family: 'Crimson Text', serif; font-style: italic; margin: 0 0 14px;
    color: var(--th-ink-faded, #6B4F38); }
.th-sc__gift-sub { font-family: 'Crimson Text', serif; font-style: italic; margin: 0 0 16px;
    color: var(--th-ink-faded, #6B4F38); font-size: 14px; }
.th-sc__gift-card {
    border-top: 3px solid var(--th-blood-red, #8B1A1F);
    background: rgba(242,226,181,0.5); padding: 16px 18px;
    margin-bottom: 12px; text-align: center;
}
.th-sc__gift-bank { font-family: 'Cinzel', serif; font-size: 11px;
    letter-spacing: 0.18em; text-transform: uppercase; margin: 0; }
.th-sc__gift-name { font-family: 'IM Fell English', serif; font-style: italic;
    font-size: 18px; margin: 4px 0; }
.th-sc__gift-num { font-family: 'Crimson Text', serif; font-variant-numeric: tabular-nums;
    font-size: 18px; color: var(--th-gold-deep, #9E7E3E); letter-spacing: 0.05em; margin: 0 0 8px; }
.th-sc__wish-list { list-style: none; padding: 0; margin: 16px 0 0; }
.th-sc__wish-list li { border-top: 1px solid rgba(107,79,56,0.3); padding: 10px 0 8px; }
.th-sc__empty { font-style: italic; color: var(--th-ink-faded, #6B4F38); text-align: center; }
.th-sc__quote { text-align: center; max-width: 480px; margin: 0 auto; }
.th-sc__quote-mark { font-family: 'Pirata One', cursive; font-size: 64px;
    color: var(--th-gold-flourish, #C9A961); opacity: 0.5; display: block; line-height: 1; }
.th-sc__quote p { font-family: 'IM Fell English', serif; font-style: italic;
    font-size: 20px; line-height: 1.6; }
.th-sc__quote-src { font-family: 'Cinzel', serif; font-size: 12px;
    letter-spacing: 0.18em; color: var(--th-gold-flourish, #C9A961); text-transform: uppercase; }
.th-sc__closing { text-align: center; padding: 12px 0; }
.th-sc__anchor { width: 64px; height: 64px;
    color: var(--th-gold-flourish, #C9A961); display: block; margin: 0 auto 8px; }
.th-sc__monogram { font-family: 'IM Fell English', serif; font-style: italic; font-size: 28px; margin: 0; }
.th-sc__closing-text { font-family: 'Crimson Text', serif; font-style: italic;
    font-size: 15px; color: var(--th-ink-faded, #6B4F38); line-height: 1.7;
    margin: 8px auto 0; max-width: 420px; }
</style>
