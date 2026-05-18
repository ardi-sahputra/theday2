<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { ref } from 'vue'

defineProps({
    localMessages: { type: Array,    default: () => [] },
    msgForm:       { type: Object,   required: true },
    msgSubmitting: { type: Boolean,  default: false },
    msgSuccess:    { type: Boolean,  default: false },
    msgError:      { type: [String, null], default: null },
    submitMessage: { type: Function, required: true },
})

const formOpen = ref(false)

function initialFor(name) {
    const n = (name || '?').trim()
    return n.charAt(0).toUpperCase() || '?'
}
function hueFor(name) {
    const s = (name || '').toLowerCase()
    let h = 0
    for (let i = 0; i < s.length; i++) h = (h + s.charCodeAt(i)) % 360
    return h
}
function displayTime(msg) {
    if (msg.created_at) {
        try { return new Date(msg.created_at).toLocaleString('id-ID') } catch { return '' }
    }
    return msg.time ?? ''
}
</script>

<template>
    <section
        class="sw-slide sw-slide-wishes"
        data-slide-key="wishes"
        :style="{
            '--sw-bg-from':       '#00C9A7',
            '--sw-bg-to':         '#4ECDC4',
            '--sw-bg-direction':  '150deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">COMMENTS</span>
                <span class="sw-slide-counter">09 / 10</span>
            </header>
            <h2 class="sw-slide-title">WHAT YOUR FANS ARE SAYING</h2>

            <button
                v-if="!formOpen"
                type="button"
                class="sw-comment-toggle"
                @click="formOpen = true"
            >+ ADD COMMENT</button>

            <form v-else class="sw-comment-form" @submit.prevent="submitMessage">
                <input v-model="msgForm.name" class="sw-pill-input" placeholder="Nama kamu" required/>
                <textarea
                    v-model="msgForm.message"
                    class="sw-pill-input sw-pill-textarea"
                    placeholder="Tulis ucapan..."
                    required
                />
                <p v-if="msgError" class="sw-form-error">{{ msgError }}</p>
                <p v-if="msgSuccess" class="sw-form-success">Ucapan terkirim.</p>
                <div class="sw-comment-form-row">
                    <button type="submit" class="sw-cta-pill" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'POSTING…' : 'POST COMMENT' }}
                    </button>
                    <button type="button" class="sw-comment-cancel" @click="formOpen = false">BATAL</button>
                </div>
            </form>

            <ul v-if="localMessages.length" class="sw-comment-list">
                <li
                    v-for="msg in localMessages"
                    :key="msg.id ?? (msg.name + msg.message)"
                    class="sw-comment-item"
                >
                    <span
                        class="sw-comment-avatar"
                        :style="{ background: `hsl(${hueFor(msg.name)}, 65%, 50%)` }"
                    >{{ initialFor(msg.name) }}</span>
                    <div class="sw-comment-body">
                        <p class="sw-comment-name">{{ msg.name }}</p>
                        <p class="sw-comment-msg">{{ msg.message }}</p>
                        <p class="sw-comment-time">{{ displayTime(msg) }}</p>
                    </div>
                </li>
            </ul>
            <p v-else class="sw-empty">Be the first to comment.</p>
        </div>
    </section>
</template>

<style scoped>
.sw-comment-toggle {
    margin: 16px 0;
    padding: 12px 24px;
    background: rgba(0,0,0,0.18);
    color: #FFFFFF;
    border: 1px solid rgba(255,255,255,0.32);
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.1em;
    cursor: pointer;
    align-self: flex-start;
    transition: background 0.2s ease;
}
.sw-comment-toggle:hover { background: rgba(0,0,0,0.32); }
.sw-comment-form { display: flex; flex-direction: column; gap: 12px; margin: 16px 0 24px; }
.sw-pill-input {
    background: rgba(0,0,0,0.22);
    color: #FFFFFF;
    border: 1px solid transparent;
    border-radius: 999px;
    padding: 12px 20px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.sw-pill-input::placeholder { color: rgba(255,255,255,0.65); }
.sw-pill-input:focus { border-color: #FFFFFF; }
.sw-pill-textarea { border-radius: 16px; min-height: 80px; resize: vertical; font-family: 'Inter', sans-serif; }
.sw-comment-form-row { display: flex; gap: 12px; align-items: center; }
.sw-cta-pill {
    padding: 12px 24px;
    background: #FFFFFF; color: #00A38C;
    border: none; border-radius: 999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 12px;
    letter-spacing: 0.1em; cursor: pointer;
    transition: transform 0.2s ease;
}
.sw-cta-pill:hover { transform: scale(1.03); }
.sw-cta-pill:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.sw-comment-cancel {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.4);
    color: #FFFFFF;
    padding: 12px 20px;
    border-radius: 999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 11px;
    letter-spacing: 0.1em; cursor: pointer;
}
.sw-form-error   { color: #FFE5E5; font-family: 'Inter', sans-serif; font-size: 13px; margin: 0; }
.sw-form-success { color: #E5FFEC; font-family: 'Inter', sans-serif; font-size: 13px; margin: 0; }

.sw-comment-list { list-style: none; padding: 0; margin: 16px 0 0; }
.sw-comment-item {
    display: grid;
    grid-template-columns: 40px 1fr;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.18);
}
.sw-comment-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 16px;
    color: #FFFFFF;
}
.sw-comment-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 15px;
    margin: 0; color: #FFFFFF;
}
.sw-comment-msg {
    font-family: 'Inter', sans-serif;
    font-weight: 400; font-size: 14px;
    color: rgba(255,255,255,0.85);
    margin: 2px 0 0; line-height: 1.5;
}
.sw-comment-time {
    font-family: 'Inter', sans-serif;
    font-weight: 400; font-size: 11px;
    color: rgba(255,255,255,0.55);
    margin: 4px 0 0;
}
.sw-empty {
    font-family: 'Inter', sans-serif;
    font-weight: 500; font-size: 16px;
    color: rgba(255,255,255,0.85);
    text-align: center;
    margin: 32px 0 0;
}
@media (prefers-reduced-motion: reduce) {
    .sw-cta-pill, .sw-cta-pill:hover, .sw-comment-toggle { transition: none; transform: none; }
}
</style>
