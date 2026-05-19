<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    open:       { type: Boolean, required: true },
    storyKey:   { type: String,  default: '' },
    giftAccounts: { type: Array, default: () => [] },
    galleries:    { type: Array, default: () => [] },
    events:       { type: Array, default: () => [] },
    wishes:       { type: Array, default: () => [] },
    copyToClipboard: { type: Function, default: () => {} },
    copiedAccount:   { type: [String, Number], default: null },
})
const emit = defineEmits(['close'])

const title = computed(() => ({
    gift:    'WEDDING GIFT ACCOUNTS',
    gallery: 'ALL PHOTOS',
    events:  'ALL EVENTS',
    wishes:  'ALL WISHES',
}[props.storyKey] ?? 'MORE'))

function urlOf(g) { return g?.image_url ?? g?.file_url ?? g?.url ?? (typeof g === 'string' ? g : null) }
</script>

<template>
    <div
        class="igs-swipe-up-backdrop"
        :class="{ 'igs-swipe-up-backdrop--open': open }"
        @click="emit('close')"
        aria-hidden="true"
    />
    <aside
        class="igs-swipe-up-panel"
        :data-open="open ? 'true' : 'false'"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
    >
        <div class="igs-swipe-up-grip" aria-hidden="true"/>
        <header class="igs-swipe-up-header">
            <h3>{{ title }}</h3>
            <button type="button" class="igs-swipe-up-close" aria-label="Close panel" @click="emit('close')">×</button>
        </header>
        <div class="igs-swipe-up-body">
            <template v-if="storyKey === 'gift'">
                <article
                    v-for="(acc, i) in giftAccounts"
                    :key="i"
                    class="igs-gift-account"
                >
                    <p class="igs-gift-account-bank">{{ acc.bank }}</p>
                    <p class="igs-gift-account-name">{{ acc.account_name }}</p>
                    <p class="igs-gift-account-num">{{ acc.account_number }}</p>
                    <button
                        type="button"
                        class="igs-gift-account-copy"
                        :aria-label="`Copy account number ${acc.account_number}`"
                        @click="copyToClipboard(acc.account_number)"
                    >
                        {{ copiedAccount === acc.account_number ? 'COPIED ✓' : 'COPY ↗' }}
                    </button>
                </article>
            </template>
            <template v-else-if="storyKey === 'gallery'">
                <div class="igs-swipe-up-photos">
                    <img
                        v-for="(g, i) in galleries"
                        :key="i"
                        :src="urlOf(g)"
                        :alt="`Photo ${i + 1}`"
                        loading="lazy"
                    />
                </div>
            </template>
            <template v-else-if="storyKey === 'events'">
                <article v-for="(ev, i) in events" :key="i" class="igs-swipe-up-event">
                    <p class="igs-swipe-up-event-name">{{ ev.event_name }}</p>
                    <p class="igs-swipe-up-event-meta">{{ ev.event_date }} · {{ ev.start_time || ev.time_start }}</p>
                    <p class="igs-swipe-up-event-addr">{{ ev.venue_address || ev.address }}</p>
                </article>
            </template>
            <template v-else-if="storyKey === 'wishes'">
                <article v-for="(m, i) in wishes" :key="m.id ?? `m-${i}`" class="igs-swipe-up-wish">
                    <p><strong>{{ m.name }}</strong></p>
                    <p>{{ m.message }}</p>
                </article>
            </template>
            <template v-else>
                <p class="igs-swipe-up-fallback">More info coming soon.</p>
            </template>
        </div>
    </aside>
</template>

<style scoped>
.igs-swipe-up-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 100;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.igs-swipe-up-backdrop--open {
    opacity: 1;
    pointer-events: auto;
}
.igs-swipe-up-panel {
    position: fixed;
    inset: auto 0 0 0;
    z-index: 101;
    background: #FFFFFF;
    color: #191919;
    border-radius: 16px 16px 0 0;
    padding: 12px 16px calc(20px + env(safe-area-inset-bottom, 0px)) 16px;
    max-height: 80dvh;
    transform: translateY(100%);
    transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
    overflow-y: auto;
}
.igs-swipe-up-panel[data-open="true"] {
    transform: translateY(0);
}
.igs-swipe-up-grip {
    width: 36px;
    height: 4px;
    border-radius: 2px;
    background: rgba(25,25,25,0.2);
    margin: 4px auto 8px;
}
.igs-swipe-up-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.igs-swipe-up-header h3 {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.12em;
    margin: 0;
}
.igs-swipe-up-close {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(25,25,25,0.08);
    color: #191919;
    font-size: 24px;
    cursor: pointer;
}
.igs-swipe-up-body { padding: 12px 0; display: flex; flex-direction: column; gap: 12px; }
.igs-gift-account {
    border: 1px solid rgba(25,25,25,0.1);
    border-radius: 12px;
    padding: 14px;
}
.igs-gift-account-bank {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: #6b6b6b;
    margin: 0 0 2px;
}
.igs-gift-account-name {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 18px;
    margin: 0;
}
.igs-gift-account-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 22px;
    font-variant-numeric: tabular-nums;
    margin: 4px 0 10px;
}
.igs-gift-account-copy {
    background: #191919;
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 8px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.12em;
    min-height: 44px;
    cursor: pointer;
}
.igs-swipe-up-photos {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
}
.igs-swipe-up-photos img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
}
.igs-swipe-up-event,
.igs-swipe-up-wish {
    border-bottom: 1px solid rgba(25,25,25,0.08);
    padding-bottom: 10px;
}
.igs-swipe-up-event p,
.igs-swipe-up-wish p {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    margin: 2px 0;
}
.igs-swipe-up-event-name {
    font-weight: 800 !important;
    font-size: 15px !important;
}
.igs-swipe-up-fallback {
    text-align: center;
    color: #6b6b6b;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    margin: 16px 0;
}
@media (prefers-reduced-motion: reduce) {
    .igs-swipe-up-panel {
        transition: opacity 0.2s ease;
        transform: none;
        opacity: 0;
        pointer-events: none;
    }
    .igs-swipe-up-panel[data-open="true"] {
        opacity: 1;
        pointer-events: auto;
    }
    .igs-swipe-up-backdrop { transition: none; }
}
</style>
