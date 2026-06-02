<script setup>
import { computed } from 'vue';

const props = defineProps({
    invitation:   { type: Object, required: true },
    primaryColor: { type: String, default: '#92A89C' },
    fontFamily:   { type: String, default: 'Playfair Display' },
    opened:       { type: Boolean, default: false },
});

const emit = defineEmits(['open']);

// ── Cover data ─────────────────────────────────────────────────────────────
const cover = computed(() => props.invitation.cover ?? {});

const pretitle        = computed(() => cover.value.pretitle      ?? 'The Wedding Of');
const coupleNames     = computed(() => cover.value.couple_names  ?? '');
const eventDateText   = computed(() => cover.value.event_date_text ?? '');
const introText       = computed(() => cover.value.intro_text    ?? '');
const buttonText      = computed(() => cover.value.button_text   ?? 'Buka Undangan');
const showPretitle    = computed(() => cover.value.show_pretitle ?? true);
const showDate        = computed(() => cover.value.show_date     ?? true);
const overlayOpacity  = computed(() => cover.value.overlay_opacity ?? 0.35);
const textAlign       = computed(() => cover.value.text_align    ?? 'center');
const contentPos      = computed(() => cover.value.content_position ?? 'center');
const showOrnament    = computed(() => cover.value.show_ornament ?? true);
const openAction      = computed(() => cover.value.open_action   ?? 'enter_content');

// ── Background ─────────────────────────────────────────────────────────────
const bgImage = computed(() => {
    // Mobile-specific image when available, otherwise desktop
    const mobileUrl = cover.value.background_mobile_image?.url;
    const desktopUrl = cover.value.background_image?.url;
    // Prefer mobile on small screens — handled via CSS, serve desktop URL here
    return desktopUrl ?? null;
});

const bgPosition = computed(() => cover.value.background_position ?? 'center');
const bgSize     = computed(() => cover.value.background_size     ?? 'cover');

const bgStyle = computed(() => {
    if (!bgImage.value) return { background: `linear-gradient(160deg, ${props.primaryColor}22, ${props.primaryColor}08)` };
    return {
        backgroundImage:    `url(${bgImage.value})`,
        backgroundPosition: bgPosition.value,
        backgroundSize:     bgSize.value,
        backgroundRepeat:   'no-repeat',
    };
});

const overlayStyle = computed(() => ({
    background: bgImage.value
        ? `rgba(0,0,0,${overlayOpacity.value})`
        : 'transparent',
}));

// ── Guest name ─────────────────────────────────────────────────────────────
const showGuestName = computed(() => cover.value.show_guest_name ?? true);

const resolvedGuestName = computed(() => {
    if (!showGuestName.value) return null;

    const mode = cover.value.guest_name_mode ?? 'query_param';

    if (mode === 'none') return null;

    if (mode === 'manual') {
        const name = (cover.value.guest_name ?? '').trim();
        return name || (cover.value.fallback_guest_text ?? 'Tamu Undangan');
    }

    // query_param
    const key = cover.value.guest_query_key ?? 'to';
    const params = new URLSearchParams(window.location.search);
    const raw    = params.get(key) ?? '';
    const decoded = decodeURIComponent(raw).replace(/\+/g, ' ').trim();
    return decoded || (cover.value.fallback_guest_text ?? 'Tamu Undangan');
});

// ── Layout helpers ─────────────────────────────────────────────────────────
const justifyMap = { top: 'justify-start', center: 'justify-center', bottom: 'justify-end' };
const contentJustify = computed(() => justifyMap[contentPos.value] ?? 'justify-center');
const contentPaddingTop    = computed(() => contentPos.value === 'top'    ? 'pt-20' : '');
const contentPaddingBottom = computed(() => contentPos.value === 'bottom' ? 'pb-20' : '');
const textAlignClass       = computed(() => `text-${textAlign.value}`);

// ── Text colours ───────────────────────────────────────────────────────────
const hasBg      = computed(() => !!bgImage.value);
const nameColor  = computed(() => hasBg.value ? 'white'              : '#292524');
const subColor   = computed(() => hasBg.value ? 'rgba(255,255,255,0.80)' : props.primaryColor);
const metaColor  = computed(() => hasBg.value ? 'rgba(255,255,255,0.65)' : '#78716c');
const divColor   = computed(() => hasBg.value ? 'rgba(255,255,255,0.40)' : props.primaryColor);

// ── CTA ────────────────────────────────────────────────────────────────────
function handleOpen() {
    emit('open', { action: openAction.value });
}
</script>

<template>
    <!-- Full-screen opening gate — position:fixed so it sits above content.
         Only while closed; once opened the host renderer shows the content. -->
    <div
        v-if="!opened"
        class="fixed inset-0 z-50 flex flex-col overflow-hidden"
        :class="[contentJustify]"
        :style="{ fontFamily }"
    >
        <!-- Background layer -->
        <div class="absolute inset-0" :style="bgStyle"/>

        <!-- Overlay -->
        <div class="absolute inset-0" :style="overlayStyle"/>

        <!-- Decorative ornament circles (no-photo fallback) -->
        <template v-if="showOrnament && !bgImage">
            <div
                class="inv-float-x absolute -top-24 -right-24 w-72 h-72 rounded-full opacity-10 pointer-events-none"
                :style="{ backgroundColor: primaryColor }"
            />
            <div
                class="inv-float absolute top-1/3 -left-12 w-48 h-48 rounded-full opacity-[0.08] pointer-events-none"
                :style="{ backgroundColor: primaryColor }"
            />
            <div
                class="inv-float-x absolute -bottom-16 right-1/4 w-40 h-40 rounded-full opacity-[0.06] pointer-events-none"
                :style="{ backgroundColor: primaryColor }"
            />
        </template>

        <!-- Content block -->
        <div
            class="relative z-10 w-full max-w-sm mx-auto px-8 space-y-5"
            :class="[textAlignClass, contentPaddingTop, contentPaddingBottom]"
        >
            <!-- Pretitle -->
            <p
                v-if="showPretitle && pretitle"
                class="text-xs tracking-[0.28em] uppercase font-light"
                :style="{ color: subColor }"
            >
                {{ pretitle }}
            </p>

            <!-- Couple names -->
            <h1
                v-if="coupleNames"
                class="text-4xl font-semibold leading-tight break-words"
                :style="{ color: nameColor, fontFamily }"
            >
                {{ coupleNames }}
            </h1>

            <!-- Divider ornament -->
            <div :class="['flex items-center gap-3', textAlign === 'center' ? 'justify-center' : textAlign === 'right' ? 'justify-end' : 'justify-start']">
                <div class="h-px w-10" :style="{ backgroundColor: divColor }"/>
                <svg class="inv-breathe w-3 h-3 opacity-60" viewBox="0 0 24 24" fill="currentColor" :style="{ color: divColor }">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
                <div class="h-px w-10" :style="{ backgroundColor: divColor }"/>
            </div>

            <!-- Event date -->
            <p
                v-if="showDate && eventDateText"
                class="text-sm font-light tracking-wide"
                :style="{ color: metaColor }"
            >
                {{ eventDateText }}
            </p>

            <!-- Intro text -->
            <p
                v-if="introText"
                class="text-xs leading-relaxed"
                :style="{ color: metaColor }"
            >
                {{ introText }}
            </p>

            <!-- Guest name -->
            <p
                v-if="resolvedGuestName"
                class="text-sm font-medium break-words"
                :style="{ color: subColor }"
            >
                {{ resolvedGuestName }}
            </p>

            <!-- CTA button -->
            <button
                @click="handleOpen"
                class="inv-breathe w-full py-4 rounded-2xl text-white text-sm font-semibold tracking-wide transition-all hover:opacity-90 active:scale-95 shadow-lg mt-2"
                :style="{ backgroundColor: primaryColor }"
            >
                {{ buttonText }}
                <svg class="inline-block w-4 h-4 ml-2 -mt-0.5 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Spacer so layout below doesn't collapse (only while the fixed gate shows) -->
    <div v-if="!opened" class="h-screen w-full"/>
</template>
