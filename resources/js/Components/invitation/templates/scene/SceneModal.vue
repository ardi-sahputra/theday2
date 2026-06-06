<!-- resources/js/Components/invitation/templates/scene/SceneModal.vue -->
<script setup>
const props = defineProps({
    modelValue: { type: Boolean, default: false },
    title:      { type: String,  default: '' },
    theme:      { type: String,  default: 'parchment' },
})
const emit = defineEmits(['update:modelValue'])

function close() {
    emit('update:modelValue', false)
}

</script>

<template>
    <!-- Positioned within the scene viewport (not teleported) so it centers on
         the scene frame — correct on a real device AND in the editor preview. -->
    <div>
        <!-- Backdrop -->
        <Transition name="backdrop-fade">
            <div
                v-if="modelValue"
                class="modal-backdrop"
                @click="close"
            />
        </Transition>

        <!-- Modal -->
        <Transition name="slide-up">
            <div
                v-if="modelValue"
                class="modal-sheet"
                :class="`theme-${theme}`"
            >
                <!-- Browser chrome (dekoratif) -->
                <div class="browser-chrome">
                    <span class="dot dot-red" />
                    <span class="dot dot-yellow" />
                    <span class="dot dot-green" />
                    <span class="url-bar">theday.id</span>
                </div>

                <!-- Nav bar -->
                <div class="nav-bar">
                    <span class="modal-title">{{ title }}</span>
                    <button class="close-btn" @click="close" aria-label="Tutup">✕</button>
                </div>

                <!-- Scrollable content -->
                <div class="modal-content">
                    <slot />
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position:   absolute;
    inset:      0;
    background: rgba(30, 15, 5, 0.6);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    z-index:    40;
}

.modal-sheet {
    position:       absolute;
    top:            50%;
    left:           50%;
    transform:      translate(-50%, -50%);
    width:          calc(100% - 32px);
    max-width:      420px;
    max-height:     82%;
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E"),
        linear-gradient(170deg, #fdf6e3 0%, #f7ead0 40%, #f2e0c8 70%, #f5e6d3 100%);
    border-radius:  20px;
    border:         1px solid rgba(185, 140, 80, 0.35);
    z-index:        50;
    display:        flex;
    flex-direction: column;
    overflow:       hidden;
    box-shadow:
        0 0 0 3px rgba(185, 140, 80, 0.12),
        0 4px 12px rgba(100, 60, 20, 0.15),
        0 20px 50px rgba(60, 30, 10, 0.35);
}

/* Decorative top border */
.modal-sheet::before {
    content:    '';
    position:   absolute;
    top:        0; left: 0; right: 0;
    height:     3px;
    background: linear-gradient(90deg,
        transparent 0%,
        #c8973a 20%,
        #e8c97a 50%,
        #c8973a 80%,
        transparent 100%
    );
    z-index: 3;
}

/* Decorative corner ornaments */
.modal-sheet::after {
    content:    '✦';
    position:   absolute;
    top:        10px;
    right:      14px;
    font-size:  10px;
    color:      rgba(185, 140, 80, 0.4);
    z-index:    3;
    pointer-events: none;
}

/* Browser chrome */
.browser-chrome {
    display:       flex;
    align-items:   center;
    gap:           6px;
    padding:       10px 14px 8px;
    background:    rgba(185, 140, 80, 0.1);
    border-bottom: 1px solid rgba(185, 140, 80, 0.2);
    flex-shrink:   0;
    position:      relative;
    z-index:       2;
}

.dot {
    width:         10px;
    height:        10px;
    border-radius: 50%;
    display:       inline-block;
    opacity:       0.9;
}
.dot-red    { background: #e07060; }
.dot-yellow { background: #d4a840; }
.dot-green  { background: #70a868; }

.url-bar {
    margin-left:    8px;
    font-size:      10px;
    color:          #8a6a3a;
    background:     rgba(255, 255, 255, 0.45);
    border:         1px solid rgba(185, 140, 80, 0.3);
    border-radius:  4px;
    padding:        2px 10px;
    flex:           1;
    text-align:     center;
    letter-spacing: 0.06em;
    font-style:     italic;
}

/* Nav bar */
.nav-bar {
    display:         flex;
    align-items:     center;
    justify-content: space-between;
    padding:         12px 16px 10px;
    border-bottom:   1px solid rgba(185, 140, 80, 0.18);
    flex-shrink:     0;
    position:        relative;
    z-index:         2;
}

/* Divider ornament */
.nav-bar::after {
    content:    '';
    position:   absolute;
    bottom:     -1px; left: 16px; right: 16px;
    height:     1px;
    background: linear-gradient(90deg,
        transparent,
        rgba(185, 140, 80, 0.5) 30%,
        rgba(185, 140, 80, 0.5) 70%,
        transparent
    );
}

.modal-title {
    font-size:      15px;
    font-weight:    700;
    color:          #6b4423;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.close-btn {
    width:           26px;
    height:          26px;
    display:         flex;
    align-items:     center;
    justify-content: center;
    background:      rgba(185, 140, 80, 0.15);
    border:          1px solid rgba(185, 140, 80, 0.35);
    border-radius:   50%;
    font-size:       11px;
    color:           #8a6030;
    cursor:          pointer;
    transition:      background 0.2s ease, transform 0.15s ease;
}
.close-btn:hover {
    background: rgba(185, 140, 80, 0.3);
    transform:  scale(1.1);
}

/* Scrollable content */
.modal-content {
    overflow-y:  auto;
    flex:        1;
    padding:     16px;
    -webkit-overflow-scrolling: touch;
    color:       #4a3015;
    position:    relative;
    z-index:     2;
}

.modal-content::-webkit-scrollbar { width: 4px; }
.modal-content::-webkit-scrollbar-track { background: rgba(185, 140, 80, 0.05); }
.modal-content::-webkit-scrollbar-thumb {
    background:    rgba(185, 140, 80, 0.35);
    border-radius: 99px;
}

/* Transitions */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active { transition: opacity 0.3s ease; }
.backdrop-fade-enter-from,
.backdrop-fade-leave-to     { opacity: 0; }

.slide-up-enter-active { transition: opacity 0.3s ease, transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.slide-up-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.slide-up-enter-from,
.slide-up-leave-to     { opacity: 0; transform: translate(-50%, -44%) scale(0.93); }

/* ══════════════════════════════════
   THEME: BEACH (warm golden sunset)
   ══════════════════════════════════ */
.theme-beach {
    background:
        linear-gradient(160deg, rgba(255, 248, 225, 0.9) 0%, rgba(255, 225, 170, 0.85) 50%, rgba(255, 200, 120, 0.88) 100%);
    backdrop-filter: blur(28px) saturate(1.6);
    -webkit-backdrop-filter: blur(28px) saturate(1.6);
    border: 1px solid rgba(255, 200, 100, 0.45);
    box-shadow:
        0 0 0 3px rgba(255, 180, 60, 0.1),
        0 20px 50px rgba(120, 60, 10, 0.35),
        0 0 80px rgba(255, 180, 80, 0.1) inset;
}
.theme-beach::before {
    background: linear-gradient(90deg, transparent, rgba(255, 210, 80, 0.9) 30%, rgba(255, 255, 200, 0.95) 50%, rgba(255, 210, 80, 0.9) 70%, transparent);
}
.theme-beach .browser-chrome {
    background:    rgba(255, 200, 100, 0.2);
    border-bottom: 1px solid rgba(255, 180, 60, 0.3);
}
.theme-beach .url-bar {
    color:      rgba(140, 80, 20, 0.75);
    background: rgba(255, 255, 200, 0.45);
    border:     1px solid rgba(255, 180, 60, 0.35);
}
.theme-beach .nav-bar {
    border-bottom: 1px solid rgba(255, 180, 60, 0.25);
}
.theme-beach .nav-bar::after {
    background: linear-gradient(90deg, transparent, rgba(255, 180, 60, 0.6) 30%, rgba(255, 180, 60, 0.6) 70%, transparent);
}
.theme-beach .modal-title {
    color:       #7a4010;
    text-shadow: 0 1px 8px rgba(255, 160, 60, 0.4);
}
.theme-beach .close-btn {
    background: rgba(255, 180, 60, 0.2);
    border:     1px solid rgba(255, 180, 60, 0.45);
    color:      #8a5010;
}
.theme-beach .close-btn:hover { background: rgba(255, 180, 60, 0.4); }
.theme-beach .modal-content { color: #5a3010; }
.theme-beach .modal-content::-webkit-scrollbar-thumb { background: rgba(255, 160, 60, 0.4); }

/* ══════════════════════════════════
   THEME: NIGHT (dark glass magical)
   ══════════════════════════════════ */
.theme-night {
    background:     rgba(8, 6, 22, 0.78);
    backdrop-filter: blur(32px) saturate(1.5);
    -webkit-backdrop-filter: blur(32px) saturate(1.5);
    border: 1px solid rgba(140, 100, 255, 0.2);
    box-shadow:
        0 0 0 1px rgba(100, 60, 200, 0.1),
        0 24px 64px rgba(0, 0, 0, 0.6),
        0 0 100px rgba(100, 60, 200, 0.08) inset;
}
.theme-night::before {
    background: linear-gradient(90deg, transparent, rgba(160, 100, 255, 0.7) 30%, rgba(200, 160, 255, 0.85) 50%, rgba(160, 100, 255, 0.7) 70%, transparent);
}
.theme-night::after { color: rgba(160, 120, 255, 0.5); }
.theme-night .browser-chrome {
    background:    rgba(255, 255, 255, 0.04);
    border-bottom: 1px solid rgba(140, 100, 255, 0.15);
}
.theme-night .dot-red    { background: #FF5F57; box-shadow: 0 0 6px rgba(255,95,87,0.6); }
.theme-night .dot-yellow { background: #FEBC2E; box-shadow: 0 0 6px rgba(254,188,46,0.6); }
.theme-night .dot-green  { background: #28C840; box-shadow: 0 0 6px rgba(40,200,64,0.6); }
.theme-night .url-bar {
    color:      rgba(200, 170, 255, 0.5);
    background: rgba(255, 255, 255, 0.05);
    border:     1px solid rgba(140, 100, 255, 0.2);
}
.theme-night .nav-bar {
    border-bottom: 1px solid rgba(140, 100, 255, 0.12);
    background:    rgba(255, 255, 255, 0.02);
}
.theme-night .nav-bar::after {
    background: linear-gradient(90deg, transparent, rgba(140, 100, 255, 0.4) 30%, rgba(140, 100, 255, 0.4) 70%, transparent);
}
.theme-night .modal-title {
    color:       rgba(220, 200, 255, 0.95);
    text-shadow: 0 0 20px rgba(160, 100, 255, 0.5);
}
.theme-night .close-btn {
    background: rgba(140, 100, 255, 0.12);
    border:     1px solid rgba(140, 100, 255, 0.3);
    color:      rgba(200, 170, 255, 0.7);
}
.theme-night .close-btn:hover { background: rgba(140, 100, 255, 0.25); color: rgba(220, 200, 255, 0.95); }
.theme-night .modal-content { color: rgba(220, 200, 255, 0.85); }
.theme-night .modal-content::-webkit-scrollbar-thumb { background: rgba(140, 100, 255, 0.35); }
</style>
