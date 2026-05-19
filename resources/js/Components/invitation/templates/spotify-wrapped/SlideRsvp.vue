<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    rsvpForm:       { type: Object,   required: true },
    rsvpSubmitting: { type: Boolean,  default: false },
    rsvpSuccess:    { type: Boolean,  default: false },
    rsvpError:      { type: [String, null], default: null },
    submitRsvp:     { type: Function, required: true },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-rsvp"
        data-slide-key="rsvp"
        :style="{
            '--sw-bg-from':       '#9BFF38',
            '--sw-bg-to':         '#1ED760',
            '--sw-bg-direction':  '155deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">ADD TO PLAYLIST</span>
                <span class="sw-slide-counter">07 / 10</span>
            </header>
            <h2 class="sw-slide-title">WILL YOU BE THERE?</h2>
            <p class="sw-rsvp-sub">Konfirmasi kehadiran kamu sekarang.</p>

            <form v-if="!rsvpSuccess" class="sw-rsvp-form" @submit.prevent="submitRsvp">
                <input
                    v-model="rsvpForm.guest_name"
                    class="sw-pill-input"
                    placeholder="Nama lengkap"
                    required
                />
                <div class="sw-attend-chips">
                    <label
                        v-for="opt in [{v:'hadir',l:'HADIR'},{v:'tidak_hadir',l:'TIDAK HADIR'},{v:'mungkin',l:'MUNGKIN'}]"
                        :key="opt.v"
                        class="sw-chip"
                        :class="{ 'sw-chip--active': rsvpForm.attendance === opt.v }"
                    >
                        <input type="radio" v-model="rsvpForm.attendance" :value="opt.v" required/>
                        <span>{{ opt.l }}</span>
                    </label>
                </div>
                <div class="sw-stepper">
                    <button type="button" class="sw-step-btn" @click="rsvpForm.guest_count = Math.max(1, (rsvpForm.guest_count ?? 1) - 1)" aria-label="Kurangi tamu">−</button>
                    <span class="sw-step-num">{{ rsvpForm.guest_count ?? 1 }} TAMU</span>
                    <button type="button" class="sw-step-btn" @click="rsvpForm.guest_count = Math.min(10, (rsvpForm.guest_count ?? 1) + 1)" aria-label="Tambah tamu">+</button>
                </div>
                <textarea
                    v-model="rsvpForm.notes"
                    class="sw-pill-input sw-pill-textarea"
                    placeholder="Catatan (opsional)"
                />
                <p v-if="rsvpError" class="sw-form-error">{{ rsvpError }}</p>
                <button type="submit" class="sw-cta-pill sw-cta-pill--filled" :disabled="rsvpSubmitting">
                    {{ rsvpSubmitting ? 'MENGIRIM…' : '+ ADD TO PLAYLIST' }}
                </button>
            </form>

            <div v-else class="sw-rsvp-success">
                <svg viewBox="0 0 64 64" width="64" height="64" aria-hidden="true" class="sw-success-check">
                    <circle cx="32" cy="32" r="30" fill="none" stroke="#FFFFFF" stroke-width="3"/>
                    <path d="M18 32 L28 42 L46 22" stroke="#FFFFFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h3 class="sw-rsvp-success-title">ADDED TO PLAYLIST</h3>
                <p class="sw-rsvp-success-sub">Thanks for the confirmation!</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-rsvp-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    opacity: 0.85;
    margin: 8px 0 24px;
}
.sw-rsvp-form { display: flex; flex-direction: column; gap: 14px; max-width: 480px; }
.sw-pill-input {
    background: rgba(0,0,0,0.25);
    color: #FFFFFF;
    border: 1px solid transparent;
    border-radius: 999px;
    padding: 14px 22px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.2s ease;
}
.sw-pill-input::placeholder { color: rgba(255,255,255,0.7); }
.sw-pill-input:focus { border-color: #FFFFFF; }
.sw-pill-textarea { border-radius: 16px; min-height: 88px; resize: vertical; font-family: 'Inter', sans-serif; }

.sw-attend-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.sw-chip {
    flex: 1 1 100px;
    text-align: center;
    padding: 10px 16px;
    background: rgba(0,0,0,0.25);
    color: #FFFFFF;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: background 0.2s ease;
    user-select: none;
}
.sw-chip input { position: absolute; opacity: 0; pointer-events: none; }
.sw-chip:hover { background: rgba(0,0,0,0.4); }
.sw-chip--active { background: #FFFFFF; color: #1ED760; }

.sw-stepper {
    display: inline-flex; align-items: center; gap: 12px;
    background: rgba(0,0,0,0.25);
    border-radius: 999px;
    padding: 6px 12px;
    align-self: flex-start;
}
.sw-step-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: #FFFFFF; color: #1ED760;
    border: none; cursor: pointer;
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 18px;
}
.sw-step-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 13px;
    letter-spacing: 0.08em;
}

.sw-cta-pill {
    align-self: flex-start;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 14px 28px;
    background: #FFFFFF; color: #1ED760;
    border: none; border-radius: 9999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 13px;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.sw-cta-pill:hover { transform: scale(1.03); }
.sw-cta-pill:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.sw-form-error { color: #FFE5E5; font-family: 'Inter', sans-serif; font-size: 13px; margin: 0; }

.sw-rsvp-success {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px;
    margin-top: 24px;
}
.sw-success-check {
    animation: sw-bounce-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes sw-bounce-in {
    0%   { transform: scale(0); opacity: 0; }
    60%  { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); }
}
.sw-rsvp-success-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 32px;
    letter-spacing: -0.02em;
    margin: 0;
}
.sw-rsvp-success-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    opacity: 0.85;
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .sw-cta-pill, .sw-cta-pill:hover, .sw-chip { transition: none; transform: none; }
    .sw-success-check { animation: none; }
}
</style>
