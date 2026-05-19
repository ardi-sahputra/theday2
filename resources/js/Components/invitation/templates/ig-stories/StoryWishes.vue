<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import StoryFrame      from './StoryFrame.vue'
import QuestionSticker from './stickers/QuestionSticker.vue'

const props = defineProps({
    localMessages:  { type: Array,    default: () => [] },
    msgForm:        { type: Object,   required: true },
    msgSubmitting:  { type: Boolean,  default: false },
    msgSuccess:     { type: Boolean,  default: false },
    msgError:       { type: String,   default: '' },
    submitMessage:  { type: Function, required: true },
    guestName:      { type: String,   default: 'Tamu' },
})
const emit = defineEmits(['view-all'])

const inputOpen = ref(false)
const visibleMsgs = computed(() => props.localMessages.slice(0, 3))
const totalMore   = computed(() => Math.max(0, props.localMessages.length - 3))
const avatarInitial = computed(() => (props.msgForm.name || props.guestName || '?').slice(0, 1))

function openInput() {
    inputOpen.value = true
    if (!props.msgForm.name && props.guestName !== 'Tamu Undangan') {
        props.msgForm.name = props.guestName
    }
}
function onSubmit() { props.submitMessage() }
</script>

<template>
    <StoryFrame story-key="wishes" story-theme="dark">
        <template #backdrop>
            <div class="igs-wishes-bg"/>
        </template>
        <div class="igs-wishes-stack">
            <p class="igs-wishes-eye igs-stagger" style="--d: 0s">WISHES</p>
            <h2 class="igs-wishes-title igs-stagger" style="--d: 0.1s">LEAVE US A WISH</h2>
            <div class="igs-wishes-sticker igs-stagger" style="--d: 0.25s" v-if="!inputOpen">
                <QuestionSticker
                    placeholder="Send a wish to the couple…"
                    :avatar-initial="avatarInitial"
                    @tap="openInput"
                />
            </div>
            <form v-if="inputOpen && !msgSuccess" class="igs-wishes-form igs-stagger" style="--d: 0s" @submit.prevent="onSubmit">
                <input
                    v-model="msgForm.name"
                    type="text"
                    class="igs-wishes-input"
                    placeholder="Your name"
                    required
                    aria-label="Your name"
                />
                <textarea
                    v-model="msgForm.message"
                    class="igs-wishes-textarea"
                    placeholder="Your wish..."
                    required
                    rows="2"
                    aria-label="Your wish"
                />
                <button type="submit" class="igs-wishes-submit" :disabled="msgSubmitting">
                    {{ msgSubmitting ? 'SENDING…' : 'POST WISH' }}
                </button>
                <p v-if="msgError" class="igs-wishes-error">{{ msgError }}</p>
            </form>
            <div v-if="msgSuccess" class="igs-wishes-success">
                <span aria-hidden="true">✓</span>
                <p>Wish posted. Thank you!</p>
            </div>
            <div class="igs-wishes-feed igs-stagger" style="--d: 0.4s">
                <template v-if="visibleMsgs.length > 0">
                    <article
                        v-for="(m, i) in visibleMsgs"
                        :key="m.id ?? `msg-${i}`"
                        class="igs-wishes-item"
                    >
                        <p class="igs-wishes-name">{{ m.name }}</p>
                        <p class="igs-wishes-msg">{{ (m.message || '').slice(0, 100) }}{{ (m.message || '').length > 100 ? '…' : '' }}</p>
                    </article>
                </template>
                <p v-else class="igs-wishes-empty">Be the first to leave a wish.</p>
                <button v-if="totalMore > 0" type="button" class="igs-wishes-more" :aria-label="`View ${totalMore} more wishes`" @click="emit('view-all')">
                    + {{ totalMore }} MORE WISHES
                </button>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-wishes-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, #84fab0 0%, #8fd3f4 100%);
}
.igs-wishes-stack {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    flex: 1;
    justify-content: center;
}
.igs-wishes-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
    text-align: center;
}
.igs-wishes-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #FFFFFF;
    margin: 0;
    text-align: center;
    letter-spacing: -0.02em;
}
.igs-wishes-sticker {
    max-width: 320px;
    width: 100%;
    margin: 4px auto;
    transform: scale(0);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) 0.25s;
}
:global(.igs-reveal.igs-visible) .igs-wishes-sticker {
    transform: scale(1);
}
.igs-wishes-form {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 320px;
    width: 100%;
    margin: 0 auto;
}
.igs-wishes-input,
.igs-wishes-textarea {
    background: rgba(255,255,255,0.95);
    border: none;
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #191919;
    min-height: 44px;
    width: 100%;
}
.igs-wishes-input:focus-visible,
.igs-wishes-textarea:focus-visible {
    outline: 2px solid #FFFFFF;
    outline-offset: 2px;
}
.igs-wishes-submit {
    background: rgba(0,0,0,0.85);
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 10px 16px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.12em;
    min-height: 44px;
    cursor: pointer;
}
.igs-wishes-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.igs-wishes-error {
    font-size: 13px;
    color: #b91c1c;
    margin: 0;
    text-align: center;
}
.igs-wishes-success {
    background: rgba(255,255,255,0.92);
    color: #191919;
    border-radius: 12px;
    padding: 14px;
    text-align: center;
    max-width: 320px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
}
.igs-wishes-success span {
    font-size: 28px;
    color: #16a34a;
    display: block;
    margin-bottom: 4px;
}
.igs-wishes-success p { margin: 0; font-size: 14px; }
.igs-wishes-feed {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 320px;
    width: 100%;
    margin: 4px auto 0;
}
.igs-wishes-item {
    background: rgba(0,0,0,0.25);
    border-radius: 8px;
    padding: 10px 12px;
    color: #FFFFFF;
}
.igs-wishes-item + .igs-wishes-item {
    border-top: 1px solid rgba(255,255,255,0.18);
    border-radius: 0 0 8px 8px;
    margin-top: -1px;
}
.igs-wishes-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    margin: 0 0 2px;
}
.igs-wishes-msg {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: rgba(255,255,255,0.85);
    margin: 0;
    line-height: 1.4;
}
.igs-wishes-empty {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: rgba(255,255,255,0.85);
    text-align: center;
    margin: 0;
}
.igs-wishes-more {
    align-self: center;
    background: rgba(0,0,0,0.18);
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 6px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    min-height: 32px;
    cursor: pointer;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger, .igs-wishes-sticker { opacity: 1; transform: none; transition: none; }
}
</style>
