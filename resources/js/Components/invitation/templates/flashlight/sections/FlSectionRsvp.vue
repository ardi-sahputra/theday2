<script setup>
defineProps({
    rsvpForm:       { type: Object,  required: true },
    rsvpSubmitting: { type: Boolean, default: false },
    rsvpSuccess:    { type: Boolean, default: false },
    rsvpError:      { type: String,  default: '' },
})
defineEmits(['submit'])
</script>

<template>
    <div class="fl-section fl-section--rsvp fl-reveal">
        <header class="fl-section-header">
            <h2 class="fl-section-title">KONFIRMASI</h2>
            <span class="fl-section-rule"/>
        </header>
        <form class="fl-rsvp-form" @submit.prevent="$emit('submit')">
            <label class="fl-field">
                <span class="fl-field-label">Nama</span>
                <input v-model="rsvpForm.guest_name" type="text" required class="fl-input"/>
            </label>
            <label class="fl-field">
                <span class="fl-field-label">Kehadiran</span>
                <select v-model="rsvpForm.attendance" class="fl-input" required>
                    <option value="">Pilih&hellip;</option>
                    <option value="yes">Hadir</option>
                    <option value="no">Tidak Hadir</option>
                    <option value="maybe">Mungkin</option>
                </select>
            </label>
            <label class="fl-field">
                <span class="fl-field-label">Jumlah Tamu</span>
                <input v-model.number="rsvpForm.guest_count" type="number" min="0" class="fl-input"/>
            </label>
            <button type="submit" class="fl-btn fl-btn--primary" :disabled="rsvpSubmitting">
                {{ rsvpSubmitting ? 'MENGIRIM…' : 'KIRIM' }}
            </button>
            <p v-if="rsvpSuccess" class="fl-form-ok">Terima kasih atas konfirmasinya.</p>
            <p v-if="rsvpError"   class="fl-form-err">{{ rsvpError }}</p>
        </form>
    </div>
</template>

<style scoped>
.fl-section {
    background: #0A0A0A;
    border: 1px solid rgba(201,169,97,0.15);
    border-radius: 4px;
    padding: 28px 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
@media (min-width: 768px) { .fl-section { padding: 40px 36px; } }
.fl-section-header { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 20px; }
.fl-section-title { font-family: 'Cinzel', serif; color: #C9A961; font-size: 13px; letter-spacing: 0.3em; margin: 0; text-transform: uppercase; }
.fl-section-rule  { display: block; width: 40px; height: 1px; background: #C9A961; }
.fl-field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px; }
.fl-field-label { font-family: 'Cinzel', serif; color: #C9A961; font-size: 11px; letter-spacing: 0.2em; }
.fl-input { padding: 10px 12px; background: #0A0A0A; border: 1px solid rgba(201,169,97,0.3); color: #F5E6CC; font-family: 'EB Garamond', serif; font-size: 14px; border-radius: 2px; width: 100%; box-sizing: border-box; }
.fl-input:focus { outline: 1px solid #C9A961; outline-offset: 1px; }
.fl-rsvp-form { display: flex; flex-direction: column; gap: 8px; }
.fl-btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 22px; min-height: 44px; min-width: 44px; background: transparent; color: #C9A961; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.25em; text-transform: uppercase; border: 1px solid #C9A961; border-radius: 2px; cursor: pointer; }
.fl-btn--primary { background: #C9A961; color: #000; }
.fl-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.fl-form-ok  { color: #C9A961; font-size: 13px; margin: 4px 0 0; }
.fl-form-err { color: #A02E1B; font-size: 13px; margin: 4px 0 0; }
.fl-reveal { opacity: 0; transform: translateY(16px); transition: opacity 0.7s ease-out, transform 0.7s ease-out; }
:global(.fl-visible.fl-reveal), .fl-reveal:global(.fl-visible) { opacity: 1; transform: none; }
@media (prefers-reduced-motion: reduce) { .fl-reveal { transition: none; transform: none; opacity: 1; } }
</style>
