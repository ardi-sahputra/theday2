<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import PhoneMockup from '@/Components/ui/PhoneMockup.vue';
import { useLocale } from '@/Composables/useLocale';
import { TEMPLATE_MAP } from '@/Components/invitation/templates/registry';

const { locale, t } = useLocale();

const props = defineProps({
    isOpen:   { type: Boolean, required: true },
    template: { type: Object,  default: null  },
    // Premium gating (dashboard). Public gallery leaves these at defaults,
    // so its behaviour is unchanged: always show "use", never a spinner.
    canUsePremium: { type: Boolean, default: true },
    creatingId:    { type: [String, Number], default: null },
});

const emit = defineEmits(['close', 'use-template', 'upgrade']);

// Whether the viewer can actually use THIS template (free always; premium gated)
const canUseThis = computed(() =>
    props.template?.tier === 'free' || props.canUsePremium
);
const isCreating = computed(() =>
    props.template != null && props.creatingId === props.template.id
);

// ── Helpers ───────────────────────────────────────────────────────────────────
const cfg   = computed(() => props.template?.default_config ?? {});
const demo  = computed(() => props.template?.demo_data      ?? {});
const tier  = computed(() => props.template?.tier           ?? 'free');

const primary   = computed(() => cfg.value.primary_color   ?? '#92A89C');
const secondary = computed(() => cfg.value.secondary_color ?? '#FEFAE0');
const accent    = computed(() => cfg.value.accent_color    ?? '#CCD5AE');
const fontTitle = computed(() => cfg.value.font_title      ?? 'Playfair Display');
const fontBody  = computed(() => cfg.value.font_body       ?? 'sans-serif');

// ── Premium template detection ────────────────────────────────────────────────
const premiumComponent = computed(() =>
    props.template ? (TEMPLATE_MAP[props.template.slug] ?? null) : null
);

// Build a proper invitation object from demo_data (mirrors what the controller does)
function formatDateId(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
}

const demoInvitation = computed(() => {
    if (!premiumComponent.value || !props.template) return null;
    const d    = demo.value;
    const config = { ...(props.template.default_config ?? {}), ...(d.custom_config ?? {}) };
    return {
        id:            'preview-demo',
        slug:          'demo',
        event_type:    'pernikahan',
        details:       d.details ?? {},
        events:        (d.events ?? []).map((e, i) => ({
            id:                   i + 1,
            event_name:           e.event_name ?? '',
            event_date:           e.event_date ?? null,
            event_date_formatted: formatDateId(e.event_date),
            start_time:           e.start_time ?? null,
            end_time:             e.end_time   ?? null,
            venue_name:           e.venue_name    ?? null,
            venue_address:        e.venue_address ?? null,
            maps_url:             e.maps_url      ?? null,
        })),
        galleries:     (d.gallery ?? []).map((url, i) => ({ id: i + 1, image_url: url, caption: null })),
        music:         null,
        config,
        template_slug: props.template.slug,
        expires_at:    null,
    };
});

const tierBadge = {
    free:    { bg: '#D1FAE5', color: '#065F46' },
    premium: { bg: 'rgba(200,162,107,0.15)', color: '#C8A26B' },
};

const isStorybook = computed(() => props.template?.category?.slug === 'storybook');

const features = computed(() => isStorybook.value
    ? [
        { label: t('public.templateModal.featStoryInteractiveWorld') },
        { label: t('public.templateModal.featStoryCssHotspots')      },
        { label: t('public.templateModal.featStoryRsvpForm')         },
        { label: t('public.templateModal.featStoryGuestbook')        },
        { label: t('public.templateModal.featStoryGallery')          },
        { label: t('public.templateModal.featStoryWhatsapp')         },
    ]
    : [
        { label: t('public.templateModal.featScrollAnimation') },
        { label: t('public.templateModal.featRsvpForm')        },
        { label: t('public.templateModal.featGallery')         },
        { label: t('public.templateModal.featCountdown')       },
        { label: t('public.templateModal.featGuestWishes')     },
        { label: t('public.templateModal.featWhatsapp')        },
    ]
);

const tName = computed(() =>
    locale.value === 'en' ? (props.template?.name_en || props.template?.name) : props.template?.name
);
const tDesc = computed(() =>
    locale.value === 'en'
        ? (props.template?.description_en || props.template?.description)
        : props.template?.description
);
const tCatName = computed(() =>
    locale.value === 'en'
        ? (props.template?.category?.name_en || props.template?.category?.name)
        : props.template?.category?.name
);

// ── Demo content derivation ───────────────────────────────────────────────────
const isWedding = computed(() =>
    props.template?.category?.slug === 'pernikahan' ||
    !! demo.value.details?.groom_name
);

const groomName = computed(() => demo.value.details?.groom_name ?? 'Ahmad Rizky');
const brideName = computed(() => demo.value.details?.bride_name ?? 'Siti Nurhaliza');

const events  = computed(() => demo.value.events  ?? []);
const gallery = computed(() => demo.value.gallery ?? []);

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

// ── Keyboard close ────────────────────────────────────────────────────────────
function onKey(e) { if (e.key === 'Escape') emit('close'); }
onMounted(()  => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-250 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen && template"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                style="background: rgba(0,0,0,0.75); backdrop-filter: blur(6px)"
                @click.self="emit('close')"
            >
                <Transition
                    enter-active-class="transition duration-250 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                    appear
                >
                    <div
                        v-if="isOpen"
                        class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden"
                    >
                        <!-- ── Header ─────────────────────────────────────────── -->
                        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100 flex-shrink-0">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :style="`background:${tierBadge[tier].bg};color:${tierBadge[tier].color}`"
                                >
                                    {{ tier === 'free' ? t('public.templateModal.tierFree') : 'Premium' }}
                                </span>
                                <h3 class="text-base font-semibold text-stone-800">{{ tName }}</h3>
                                <span class="text-xs text-stone-400 hidden sm:inline">· {{ tCatName }}</span>
                            </div>
                            <button
                                @click="emit('close')"
                                class="p-2 rounded-xl text-stone-400 hover:text-stone-600 hover:bg-stone-100 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- ── Body ───────────────────────────────────────────── -->
                        <div class="flex-1 min-h-0 overflow-y-auto">
                        <div class="flex flex-col md:flex-row md:items-start">

                            <!-- Left: phone mockup — hidden on mobile, sticky on desktop -->
                            <div
                                class="hidden md:flex items-center justify-center p-8 flex-shrink-0 md:sticky md:top-0"
                                :style="`background: linear-gradient(150deg, ${secondary}ee, ${primary}22); min-height: 100%`"
                            >
                                <PhoneMockup v-if="premiumComponent">
                                    <component
                                        :is="premiumComponent"
                                        :invitation="demoInvitation"
                                        :messages="[]"
                                        :is-demo="true"
                                        :auto-open="true"
                                    />
                                </PhoneMockup>

                                <PhoneMockup v-else>
                                    <div
                                        class="min-h-full"
                                        :style="`background: linear-gradient(180deg, ${secondary}, white 60%); font-family: '${fontBody}', sans-serif`"
                                    >
                                        <div class="flex flex-col items-center justify-center text-center px-6 pt-10 pb-8"
                                             :style="`background: linear-gradient(160deg, ${secondary}, ${primary}18)`">
                                            <div class="w-12 h-0.5 mb-5 rounded-full" :style="`background:${primary}`"/>
                                            <p class="text-[10px] tracking-[0.2em] uppercase font-medium mb-2" :style="`color:${primary}`">
                                                {{ t('public.templateModal.weddingInvitation') }}
                                            </p>
                                            <p class="text-3xl font-bold leading-tight text-stone-800 mb-1"
                                               :style="`font-family: '${fontTitle}', serif`">{{ groomName }}</p>
                                            <div class="flex items-center gap-3 my-1.5">
                                                <div class="w-8 h-px" :style="`background:${primary}`"/>
                                                <span class="text-sm" :style="`color:${primary}`">&amp;</span>
                                                <div class="w-8 h-px" :style="`background:${primary}`"/>
                                            </div>
                                            <p class="text-3xl font-bold leading-tight text-stone-800"
                                               :style="`font-family: '${fontTitle}', serif`">{{ brideName }}</p>
                                            <div class="w-12 h-0.5 mt-5 rounded-full" :style="`background:${primary}`"/>
                                        </div>
                                        <div v-if="demo.details?.opening_text" class="px-6 py-5 text-center">
                                            <p class="text-[11px] text-stone-500 leading-relaxed italic line-clamp-4">
                                                {{ demo.details.opening_text }}
                                            </p>
                                        </div>
                                        <div v-if="events.length" class="px-4 pb-4 space-y-3">
                                            <p class="text-[10px] tracking-[0.15em] uppercase font-semibold text-center mb-3" :style="`color:${primary}`">{{ t('public.templateModal.dateLocation') }}</p>
                                            <div v-for="(ev, i) in events" :key="i" class="rounded-2xl p-3.5"
                                                 :style="`background:${primary}12; border:1px solid ${primary}25`">
                                                <p class="text-xs font-bold text-stone-800 mb-1">{{ ev.event_name }}</p>
                                                <p class="text-[11px] text-stone-500 mb-0.5">{{ formatDate(ev.event_date) }}</p>
                                                <p class="text-[11px] text-stone-500 mb-0.5">{{ ev.start_time }}<span v-if="ev.end_time"> – {{ ev.end_time }}</span></p>
                                                <div class="h-px my-2" :style="`background:${primary}20`"/>
                                                <p class="text-[11px] font-medium text-stone-700">{{ ev.venue_name }}</p>
                                                <p class="text-[10px] text-stone-400 leading-tight mt-0.5">{{ ev.venue_address }}</p>
                                            </div>
                                        </div>
                                        <div v-if="gallery.length" class="px-4 pb-5">
                                            <p class="text-[10px] tracking-[0.15em] uppercase font-semibold text-center mb-3" :style="`color:${primary}`">{{ t('public.templateModal.photoGallery') }}</p>
                                            <div class="flex gap-2 overflow-x-auto pb-1" style="scrollbar-width:none">
                                                <img v-for="(src, i) in gallery" :key="i" :src="src"
                                                     class="w-20 h-24 object-cover rounded-xl flex-shrink-0"
                                                     :style="`border:2px solid ${primary}30`"
                                                     @error="e => e.target.style.background = primary + '22'" />
                                            </div>
                                        </div>
                                        <div class="px-5 pb-8 text-center">
                                            <button class="w-full py-3 rounded-2xl text-xs font-bold text-white shadow-sm" :style="`background:${primary}`">
                                                {{ t('public.templateModal.confirmAttendance') }}
                                            </button>
                                            <p class="mt-4 text-[10px] text-stone-300">{{ t('public.templateModal.madeWithTheDay') }}</p>
                                        </div>
                                    </div>
                                </PhoneMockup>
                            </div>

                            <!-- Right: info panel — full width on mobile, sidebar on md+ -->
                            <div class="flex-1 flex flex-col md:border-l border-stone-100">
                                <div class="flex-1 p-5 md:p-6 space-y-5">

                                    <!-- Name + desc -->
                                    <div>
                                        <h2 class="text-xl font-bold text-stone-900 mb-1"
                                            :style="`font-family: '${fontTitle}', serif`">{{ tName }}</h2>
                                        <p class="text-sm text-stone-500 leading-relaxed">
                                            {{ tDesc ?? t('public.templateModal.defaultDescription') }}
                                        </p>
                                    </div>

                                    <!-- Color palette -->
                                    <div v-if="!isStorybook">
                                        <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-2">{{ t('public.templateModal.colorPalette') }}</p>
                                        <div class="flex items-center gap-2">
                                            <div v-for="([label, color]) in [[t('public.templateModal.colorPrimary'), primary], [t('public.templateModal.colorSecondary'), secondary], [t('public.templateModal.colorAccent'), accent]]"
                                                 :key="label" class="flex flex-col items-center gap-1">
                                                <div class="w-9 h-9 rounded-xl border border-stone-100 shadow-sm" :style="`background:${color}`"/>
                                                <span class="text-[10px] text-stone-400">{{ label }}</span>
                                            </div>
                                            <div class="ml-1 flex flex-col gap-0.5">
                                                <span class="text-[10px] text-stone-400">Font: <span class="text-stone-600 font-medium">{{ fontTitle }}</span></span>
                                                <span class="text-[10px] text-stone-400">Body: <span class="text-stone-600 font-medium">{{ fontBody }}</span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Features -->
                                    <div>
                                        <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-2">{{ t('public.templateModal.availableFeatures') }}</p>
                                        <ul class="space-y-1.5">
                                            <li v-for="f in features" :key="f.label" class="flex items-center gap-2 text-sm text-stone-600">
                                                <span class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0"
                                                      :style="`background:${primary}20`">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" :style="`color:${primary}`">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                                {{ f.label }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Actions — sticky at bottom -->
                                <div class="p-5 md:p-6 border-t border-stone-100 space-y-2.5 flex-shrink-0">
                                    <a :href="`/templates/${template.slug}/demo`" target="_blank"
                                       class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold border-2 transition-all hover:opacity-80"
                                       :style="`color:${primary}; border-color:${primary}40`">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        {{ t('public.templateModal.viewFullDemo') }}
                                    </a>
                                    <button v-if="canUseThis"
                                            @click="emit('use-template', template.id)"
                                            :disabled="isCreating"
                                            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 active:scale-[0.98] disabled:opacity-60"
                                            :style="`background:${primary}`">
                                        <svg v-if="isCreating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        {{ isCreating ? t('dashboard.templates.creating') : t('public.templateModal.useThisTemplate') }}
                                    </button>
                                    <button v-else
                                            @click="emit('upgrade', template)"
                                            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold bg-stone-800 text-[#C4D0C9] hover:bg-stone-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        {{ t('dashboard.templates.upgradeToPremium') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
