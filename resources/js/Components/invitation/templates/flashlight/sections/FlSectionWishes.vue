<script setup>
defineProps({
    msgForm:       { type: Object,  required: true },
    msgSubmitting: { type: Boolean, default: false },
    msgSuccess:    { type: Boolean, default: false },
    msgError:      { type: String,  default: '' },
    localMessages: { type: Array,   default: () => [] },
})
defineEmits(['submit'])
</script>

<template>
    <div class="fl-section fl-section--wishes fl-reveal">
        <header class="fl-section-header">
            <h2 class="fl-section-title">UCAPAN</h2>
            <span class="fl-section-rule"/>
        </header>
        <form class="fl-wishes-form" @submit.prevent="$emit('submit')">
            <input v-model="msgForm.name"    type="text" placeholder="Nama"   required class="fl-input"/>
            <textarea v-model="msgForm.message" rows="3" placeholder="Tulis ucapan…" required class="fl-input"/>
            <button type="submit" class="fl-btn fl-btn--primary" :disabled="msgSubmitting">
                {{ msgSubmitting ? 'MENGIRIM…' : 'KIRIM' }}
            </button>
            <p v-if="msgSuccess" class="fl-form-ok">Ucapan terkirim.</p>
            <p v-if="msgError"   class="fl-form-err">{{ msgError }}</p>
        </form>
        <ul v-if="localMessages.length" class="fl-wishes-list">
            <li v-for="m in localMessages.slice(0, 3)" :key="m.id ?? m.created_at" class="fl-wish-item">
                <p class="fl-wish-name">{{ m.name }}</p>
                <p class="fl-wish-text">{{ m.message }}</p>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.fl-section { background: #0A0A0A; border: 1px solid rgba(201,169,97,0.15); border-radius: 4px; padding: 28px 24px; box-shadow: 0 8px 32px rgba(0,0,0,0.5); }
@media (min-width: 768px) { .fl-section { padding: 40px 36px; } }
.fl-section-header { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 20px; }
.fl-section-title { font-family: 'Cinzel', serif; color: #C9A961; font-size: 13px; letter-spacing: 0.3em; margin: 0; text-transform: uppercase; }
.fl-section-rule  { display: block; width: 40px; height: 1px; background: #C9A961; }
.fl-input { padding: 10px 12px; background: #0A0A0A; border: 1px solid rgba(201,169,97,0.3); color: #F5E6CC; font-family: 'EB Garamond', serif; font-size: 14px; border-radius: 2px; width: 100%; box-sizing: border-box; }
.fl-input:focus { outline: 1px solid #C9A961; outline-offset: 1px; }
.fl-wishes-form { display: flex; flex-direction: column; gap: 8px; }
.fl-btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 22px; min-height: 44px; min-width: 44px; background: transparent; color: #C9A961; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.25em; text-transform: uppercase; border: 1px solid #C9A961; border-radius: 2px; cursor: pointer; }
.fl-btn--primary { background: #C9A961; color: #000; }
.fl-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.fl-form-ok  { color: #C9A961; font-size: 13px; margin: 4px 0 0; }
.fl-form-err { color: #A02E1B; font-size: 13px; margin: 4px 0 0; }
.fl-wishes-list { list-style: none; padding: 0; margin: 16px 0 0; }
.fl-wish-item { border-top: 1px solid rgba(201,169,97,0.15); padding: 10px 0; }
.fl-wish-name { font-family: 'Cinzel', serif; color: #C9A961; font-size: 11px; letter-spacing: 0.2em; margin: 0; }
.fl-wish-text { font-family: 'EB Garamond', serif; color: #F5E6CC; font-size: 13px; margin: 4px 0 0; }
.fl-reveal { opacity: 0; transform: translateY(16px); transition: opacity 0.7s ease-out, transform 0.7s ease-out; }
@media (prefers-reduced-motion: reduce) { .fl-reveal { transition: none; transform: none; opacity: 1; } }
</style>
