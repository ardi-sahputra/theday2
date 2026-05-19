<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import StoryFrame   from './StoryFrame.vue'
import PollSticker  from './stickers/PollSticker.vue'

const props = defineProps({
    rsvpForm:        { type: Object,  required: true },
    rsvpSubmitting:  { type: Boolean, default: false },
    rsvpSuccess:     { type: Boolean, default: false },
    rsvpError:       { type: String,  default: '' },
    submitRsvp:      { type: Function, required: true },
})

const showMaybe = ref(false)

const selected = computed(() => {
    if (props.rsvpForm.attendance === 'attending')     return 'one'
    if (props.rsvpForm.attendance === 'not_attending') return 'two'
    return null
})

function vote(option) {
    if (option === 'one') props.rsvpForm.attendance = 'attending'
    if (option === 'two') props.rsvpForm.attendance = 'not_attending'
}
function selectMaybe() {
    props.rsvpForm.attendance = 'maybe'
    showMaybe.value = true
}
function onSubmit() {
    props.submitRsvp()
}
</script>

<template>
    <StoryFrame story-key="rsvp" story-theme="light">
        <template #backdrop>
            <div class="igs-rsvp-bg"/>
        </template>
        <div class="igs-rsvp-stack">
            <p class="igs-rsvp-eye igs-stagger" style="--d: 0s">RSVP</p>
            <h2 class="igs-rsvp-title igs-stagger" style="--d: 0.1s">WILL YOU BE THERE?</h2>
            <p class="igs-rsvp-sub igs-stagger" style="--d: 0.2s">Confirm your attendance below</p>
            <div class="igs-rsvp-poll igs-stagger" style="--d: 0.3s" v-if="!rsvpSuccess">
                <PollSticker
                    question="WILL YOU BE THERE?"
                    option1="YES, CAN'T WAIT ✨"
                    option2="SORRY, CAN'T MAKE IT"
                    :selected="selected"
                    @vote="vote"
                />
                <button v-if="!showMaybe && selected !== null" type="button" class="igs-rsvp-maybe-link" @click="selectMaybe">
                    Tap to see "maybe" option
                </button>
                <span v-if="showMaybe" class="igs-rsvp-maybe-pill" :class="{ 'igs-rsvp-maybe-pill--on': rsvpForm.attendance === 'maybe' }">
                    MAYBE 🤔
                </span>
            </div>
            <form v-if="rsvpForm.attendance && !rsvpSuccess" class="igs-rsvp-form igs-stagger" style="--d: 0.45s" @submit.prevent="onSubmit">
                <input
                    v-model="rsvpForm.guest_name"
                    type="text"
                    class="igs-rsvp-input"
                    placeholder="Your name"
                    required
                    aria-label="Your name"
                />
                <div class="igs-rsvp-stepper" role="group" aria-label="Guest count">
                    <button type="button" aria-label="Decrease guest count" @click="rsvpForm.guest_count = Math.max(1, (Number(rsvpForm.guest_count) || 1) - 1)">−</button>
                    <span>{{ rsvpForm.guest_count || 1 }}</span>
                    <button type="button" aria-label="Increase guest count" @click="rsvpForm.guest_count = Math.min(20, (Number(rsvpForm.guest_count) || 1) + 1)">+</button>
                </div>
                <textarea
                    v-model="rsvpForm.notes"
                    class="igs-rsvp-textarea"
                    placeholder="Notes (optional)"
                    rows="2"
                    aria-label="Notes"
                />
                <button type="submit" class="igs-rsvp-submit" :disabled="rsvpSubmitting">
                    {{ rsvpSubmitting ? 'SENDING…' : 'CONFIRM RSVP' }}
                </button>
                <p v-if="rsvpError" class="igs-rsvp-error">{{ rsvpError }}</p>
            </form>
            <div v-if="rsvpSuccess" class="igs-rsvp-success igs-stagger" style="--d: 0s">
                <span class="igs-rsvp-check" aria-hidden="true">✓</span>
                <p><strong>RSVP RECEIVED</strong></p>
                <p>Thanks for confirming!</p>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-rsvp-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}
.igs-rsvp-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
    flex: 1;
    justify-content: center;
    color: #191919;
}
.igs-rsvp-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #191919;
    margin: 0;
}
.igs-rsvp-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #191919;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-rsvp-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: rgba(25,25,25,0.7);
    margin: 0;
}
.igs-rsvp-poll {
    width: 100%;
    max-width: 320px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
}
.igs-rsvp-maybe-link {
    background: transparent;
    border: none;
    color: #4a4a4a;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 12px;
    text-decoration: underline;
    cursor: pointer;
    min-height: 32px;
}
.igs-rsvp-maybe-pill {
    background: rgba(255,255,255,0.85);
    color: #191919;
    border-radius: 9999px;
    padding: 8px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.04em;
}
.igs-rsvp-maybe-pill--on { background: #FFFFFF; }
.igs-rsvp-form {
    width: 100%;
    max-width: 320px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.igs-rsvp-input,
.igs-rsvp-textarea {
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(25,25,25,0.1);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #191919;
    min-height: 44px;
    width: 100%;
}
.igs-rsvp-input:focus-visible,
.igs-rsvp-textarea:focus-visible {
    outline: 2px solid #833ab4;
    outline-offset: 2px;
}
.igs-rsvp-stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: rgba(255,255,255,0.85);
    border-radius: 9999px;
    padding: 4px 12px;
    min-height: 44px;
    width: 100%;
}
.igs-rsvp-stepper button {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 1px solid rgba(25,25,25,0.1);
    color: #191919;
    font-weight: 800;
    font-size: 18px;
    cursor: pointer;
}
.igs-rsvp-stepper span {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 18px;
    min-width: 32px;
    text-align: center;
    color: #191919;
}
.igs-rsvp-submit {
    background: #191919;
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 12px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.12em;
    min-height: 44px;
    cursor: pointer;
}
.igs-rsvp-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.igs-rsvp-error {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #b91c1c;
    margin: 0;
}
.igs-rsvp-success {
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: #191919;
    font-family: 'Inter', sans-serif;
}
.igs-rsvp-success p { margin: 0; font-size: 14px; }
.igs-rsvp-check {
    font-size: 36px;
    font-weight: 900;
    color: #16a34a;
}
.igs-stagger {
    opacity: 0;
    transform: scale(0.95) translateY(6px);
    transition: opacity 0.5s ease-out var(--d, 0s), transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: scale(1) translateY(0);
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
}
</style>
