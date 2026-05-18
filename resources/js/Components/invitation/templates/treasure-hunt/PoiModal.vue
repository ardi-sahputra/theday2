<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <Teleport to="body">
        <Transition name="th-modal-backdrop">
            <div v-if="open" class="th-modal-backdrop" @click.self="$emit('close')"/>
        </Transition>
        <Transition name="th-modal">
            <div v-if="open" ref="modalRoot" class="th-modal" role="dialog" aria-modal="true"
                 :aria-labelledby="poi ? `th-modal-title-${poi.key}` : null"
                 @keydown.esc="$emit('close')" @keydown.tab="onTab">
                <header class="th-modal__head">
                    <span class="th-modal__roman" aria-hidden="true">{{ poi?.roman }}</span>
                    <button class="th-modal__close" type="button" aria-label="Tutup" @click="$emit('close')">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <line x1="5" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="19" y1="5" x2="5" y2="19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </header>
                <h2 v-if="poi" :id="`th-modal-title-${poi.key}`" ref="titleEl"
                    class="th-modal__title" tabindex="-1">{{ poi.name }}</h2>
                <div class="th-modal__rule" aria-hidden="true"/>
                <div class="th-modal__body"><slot/></div>
                <footer v-if="$slots.footer" class="th-modal__foot"><slot name="footer"/></footer>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue'
const props = defineProps({ open: { type: Boolean, default: false }, poi: { type: Object, default: null } })
defineEmits(['close'])
const modalRoot = ref(null), titleEl = ref(null)
watch(() => props.open, async (v) => { if (v) { await nextTick(); titleEl.value?.focus() } })
function onTab(e) {
    if (!modalRoot.value) return
    const f = modalRoot.value.querySelectorAll('button,[href],input,select,textarea,[tabindex]:not([tabindex="-1"])')
    if (!f.length) return
    const first = f[0], last = f[f.length - 1]
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus() }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus() }
}
</script>

<style scoped>
.th-modal-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,0.45);
    backdrop-filter: blur(2px); z-index: 80;
}
.th-modal {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: min(560px, calc(100vw - 32px)); max-height: min(720px, calc(100dvh - 32px));
    background: var(--th-parchment, #E8D5A0); color: var(--th-ink, #3D2817);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-ink-faded, #6B4F38), 0 12px 40px rgba(0,0,0,0.4);
    border-radius: 4px; z-index: 90; display: flex; flex-direction: column;
    padding: 24px 24px 28px;
}
@media (min-width: 768px) { .th-modal { padding: 32px 40px 36px; } }
.th-modal__head { display: flex; align-items: center; justify-content: space-between; }
.th-modal__roman { font-family: 'Cinzel', serif; font-size: 28px; color: var(--th-gold-flourish, #C9A961); }
.th-modal__close {
    width: 36px; height: 36px; background: transparent; border: 0;
    color: var(--th-ink, #3D2817); cursor: pointer; border-radius: 2px;
}
.th-modal__close:hover, .th-modal__close:focus-visible {
    background: rgba(168,138,79,0.18); outline: none;
}
.th-modal__title {
    margin: 8px 0 4px; text-align: center;
    font-family: 'IM Fell English', serif; font-size: 24px; color: var(--th-ink, #3D2817);
}
.th-modal__title:focus { outline: none; }
.th-modal__rule { width: 40px; height: 2px; background: var(--th-gold-flourish, #C9A961); margin: 8px auto 16px; }
.th-modal__body { overflow-y: auto; font-family: 'Crimson Text', serif; font-size: 16px; line-height: 1.7;
    padding-right: 4px; flex: 1 1 auto; }
.th-modal__foot { margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--th-ink-faded, #6B4F38); }
.th-modal-enter-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease-out; }
.th-modal-leave-active { transition: transform 0.3s ease-in, opacity 0.3s ease-in; }
.th-modal-enter-from { transform: translate(-50%, calc(-50% + 24px)) scale(0.95); opacity: 0; }
.th-modal-leave-to { transform: translate(-50%, calc(-50% + 12px)) scale(0.97); opacity: 0; }
.th-modal-backdrop-enter-active, .th-modal-backdrop-leave-active { transition: opacity 0.3s ease; }
.th-modal-backdrop-enter-from, .th-modal-backdrop-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .th-modal-enter-active, .th-modal-leave-active,
    .th-modal-backdrop-enter-active, .th-modal-backdrop-leave-active { transition: opacity 0.2s ease; }
    .th-modal-enter-from, .th-modal-leave-to { transform: translate(-50%, -50%); }
}
</style>
