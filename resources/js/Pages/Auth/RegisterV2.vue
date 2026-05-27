<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const { locale, toggleLocale, t } = useLocale();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPass    = ref(false);
const showConfirm = ref(false);
const agreed      = ref(false);

// Password strength (0–4) — mirrors the mock's 4-segment bar.
const pwScore = computed(() => {
    const v = form.password || '';
    if (!v) return 0;
    let s = 0;
    if (v.length >= 8) s++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
    if (/\d/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    return Math.min(s, 4);
});
const pwColor = computed(() => ['#D9B5B0', '#D9A24A', '#9CAB8E', '#4A5A4C'][Math.max(0, pwScore.value - 1)]);
const pwLabel = computed(() => [
    '', t('auth.register_v2_pw_weak'), t('auth.register_v2_pw_fair'),
    t('auth.register_v2_pw_good'), t('auth.register_v2_pw_strong'),
][pwScore.value]);

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head :title="t('auth.register_title')" />

    <div class="rv2-page">

        <!-- ── FORM SIDE (right) ─────────────────────────────── -->
        <div class="rv2-form">
            <div class="rv2-fheader">
                <a href="/" class="rv2-logo" aria-label="TheDay">
                    <img src="/image/logo.svg" alt="TheDay" />
                </a>
                <button type="button" class="rv2-lang" @click="toggleLocale">
                    <span>{{ locale === 'en' ? '🇬🇧' : '🇮🇩' }}</span>
                    {{ locale === 'en' ? 'EN' : 'ID' }}
                </button>
            </div>

            <div class="rv2-fbody">
                <div class="rv2-eyebrow">{{ t('auth.register_v2_eyebrow') }}</div>
                <h1 class="rv2-h1">{{ t('auth.register_v2_headline') }}</h1>
                <p class="rv2-intro">{{ t('auth.register_v2_sub') }}</p>

                <!-- Google -->
                <a :href="route('auth.google')" class="rv2-soc-btn">
                    <svg width="18" height="18" viewBox="0 0 18 18">
                        <path d="M17.64 9.2c0-.64-.06-1.25-.16-1.83H9v3.47h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.61z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26a5.4 5.4 0 0 1-3.04.85 5.4 5.4 0 0 1-5.07-3.74H.96v2.34A9 9 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.96 10.71a5.41 5.41 0 0 1 0-3.42V4.95H.96a9 9 0 0 0 0 8.1l3-2.34z" fill="#FBBC05"/>
                        <path d="M9 3.58a4.86 4.86 0 0 1 3.44 1.34l2.58-2.59A9 9 0 0 0 9 0 9 9 0 0 0 .96 4.96l3 2.33A5.4 5.4 0 0 1 9 3.58z" fill="#EA4335"/>
                    </svg>
                    {{ t('auth.login_with_google') }}
                </a>

                <div class="rv2-divider">{{ t('auth.register_v2_or') }}</div>

                <form @submit.prevent="submit">
                    <!-- Name (required by backend; styled to match) -->
                    <div class="rv2-field">
                        <label for="name">{{ t('auth.full_name') }}</label>
                        <div class="rv2-input-wrap">
                            <span class="rv2-input-glyph">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input
                                id="name" v-model="form.name"
                                class="rv2-input rv2-input-icon"
                                :class="{ 'rv2-input-error': form.errors.name }"
                                type="text" required autofocus autocomplete="name"
                                :placeholder="t('auth.name_placeholder')"
                            />
                        </div>
                        <p v-if="form.errors.name" class="rv2-err">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div class="rv2-field">
                        <label for="email">{{ t('auth.email') }}</label>
                        <div class="rv2-input-wrap">
                            <span class="rv2-input-glyph">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                            </span>
                            <input
                                id="email" v-model="form.email"
                                class="rv2-input rv2-input-icon"
                                :class="{ 'rv2-input-error': form.errors.email }"
                                type="email" required autocomplete="username"
                                placeholder="nama@email.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="rv2-err">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="rv2-field rv2-field-tight">
                        <label for="password">{{ t('auth.password') }}</label>
                        <div class="rv2-input-wrap">
                            <span class="rv2-input-glyph">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                id="password" v-model="form.password"
                                class="rv2-input rv2-input-icon"
                                :class="{ 'rv2-input-error': form.errors.password }"
                                style="padding-right: 42px"
                                :type="showPass ? 'text' : 'password'"
                                required autocomplete="new-password"
                                :placeholder="t('auth.password_placeholder')"
                            />
                            <button type="button" class="rv2-input-toggle" tabindex="-1" @click="showPass = !showPass">
                                <svg v-if="!showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="rv2-err">{{ form.errors.password }}</p>
                    </div>

                    <!-- Strength bar -->
                    <div class="rv2-strength">
                        <span v-for="i in 4" :key="i" class="rv2-seg"
                              :style="{ background: i <= pwScore ? pwColor : 'var(--line-2)' }"></span>
                    </div>
                    <div class="rv2-strength-hint">
                        <template v-if="pwScore">{{ pwLabel }}</template>
                        <template v-else>{{ t('auth.password_placeholder') }}</template>
                    </div>

                    <!-- Confirm Password -->
                    <div class="rv2-field">
                        <label for="password_confirmation">{{ t('auth.confirm_password') }}</label>
                        <div class="rv2-input-wrap">
                            <span class="rv2-input-glyph">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                id="password_confirmation" v-model="form.password_confirmation"
                                class="rv2-input rv2-input-icon"
                                :class="{ 'rv2-input-error': form.errors.password_confirmation }"
                                style="padding-right: 42px"
                                :type="showConfirm ? 'text' : 'password'"
                                required autocomplete="new-password"
                                :placeholder="t('auth.confirm_placeholder')"
                            />
                            <button type="button" class="rv2-input-toggle" tabindex="-1" @click="showConfirm = !showConfirm">
                                <svg v-if="!showConfirm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="rv2-err">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Terms -->
                    <label class="rv2-terms">
                        <input v-model="agreed" type="checkbox" required/>
                        <span class="rv2-cbox"></span>
                        <span class="rv2-terms-txt">
                            {{ t('auth.register_v2_terms_agree') }}
                            <a :href="route('legal.terms')" target="_blank">{{ t('auth.register_v2_tos') }}</a>
                            {{ t('auth.register_v2_terms_and') }}
                            <a :href="route('legal.privacy')" target="_blank">{{ t('auth.register_v2_privacy') }}</a>.
                        </span>
                    </label>

                    <button type="submit" class="rv2-btn-primary" :disabled="form.processing || !agreed">
                        <template v-if="form.processing">
                            <svg class="rv2-spin" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
                                <path fill="currentColor" opacity="0.75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ t('auth.register_submitting') }}
                        </template>
                        <template v-else>
                            {{ t('auth.register_v2_submit') }}
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </template>
                    </button>
                </form>

                <div class="rv2-fsignup">
                    {{ t('auth.have_account') }}
                    <Link :href="route('login')">{{ t('auth.login_here') }} →</Link>
                </div>
            </div>

            <div class="rv2-ffooter">
                © 2026 Theday Indonesia · <a :href="route('legal.terms')">{{ locale === 'en' ? 'Terms' : 'Syarat' }}</a> · <a :href="route('legal.privacy')">{{ locale === 'en' ? 'Privacy' : 'Privasi' }}</a>
            </div>
        </div>

        <!-- ── ART SIDE (left, desktop only) ─────────────────────────────── -->
        <div class="rv2-art">
            <div class="rv2-art-center">
                <h2 class="rv2-art-heading">{{ t('auth.login_v2_art_h1') }}<br/><span class="rv2-it">{{ t('auth.login_v2_art_h2') }}</span></h2>
                <p class="rv2-art-tag">{{ t('auth.login_v2_art_tag') }}</p>

                <svg class="rv2-art-svg" viewBox="0 0 460 200" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 20 160 C 100 160, 130 100, 230 100 S 360 60, 440 60" stroke="rgba(251,252,249,0.45)" stroke-width="6" stroke-linecap="round" fill="none"/>
                    <path d="M 20 160 C 100 160, 130 100, 230 100 S 360 60, 440 60" stroke="rgba(251,252,249,0.9)" stroke-width="1.5" stroke-linecap="round" fill="none" stroke-dasharray="2 8"/>
                    <g transform="translate(20, 160)"><circle r="10" fill="#FBFCF9"/><circle r="5" fill="#4A5A4C"/></g>
                    <g transform="translate(160, 130)"><circle r="14" fill="#FBFCF9"/><path d="M -5 0 L -2 3 L 5 -4" stroke="#4A5A4C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></g>
                    <g transform="translate(280, 90)"><circle r="18" fill="#FBFCF9"/><path d="M -8 0 c 0 -8 16 -8 16 0 c 0 6 -8 12 -8 12 c 0 0 -8 -6 -8 -12 z" fill="#D9B5B0"/></g>
                    <g transform="translate(440, 60)"><circle r="14" fill="#FBFCF9"/><path d="M 0 -6 L -6 -2 L -6 5 L 6 5 L 6 -2 Z" fill="#C19089"/><path d="M -6 -2 L 0 -8 L 6 -2" stroke="#C19089" stroke-width="1.5" fill="none" stroke-linecap="round"/></g>
                    <text x="20" y="190" text-anchor="middle" font-family="JetBrains Mono" font-size="9" fill="rgba(251,252,249,0.7)" letter-spacing="1">DAFTAR</text>
                    <text x="160" y="160" text-anchor="middle" font-family="JetBrains Mono" font-size="9" fill="rgba(251,252,249,0.7)" letter-spacing="1">UNDANGAN</text>
                    <text x="280" y="120" text-anchor="middle" font-family="JetBrains Mono" font-size="9" fill="rgba(251,252,249,0.7)" letter-spacing="1">HARI H</text>
                    <text x="440" y="90" text-anchor="middle" font-family="JetBrains Mono" font-size="9" fill="rgba(251,252,249,0.7)" letter-spacing="1">BEYOND</text>
                    <g fill="rgba(251,252,249,0.5)">
                        <circle cx="80" cy="40" r="2"/><circle cx="150" cy="30" r="2"/><circle cx="350" cy="20" r="2"/><circle cx="380" cy="120" r="2"/><circle cx="60" cy="100" r="2"/>
                    </g>
                </svg>
            </div>

            <div class="rv2-art-bottom">
                <div class="rv2-art-meta">VOL. I · 2026</div>
                <div class="rv2-art-meta">DIBUAT DENGAN ♡ DI JAKARTA</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.rv2-page {
    --bg: #EEF2EA;
    --surface: #F6F8F3;
    --paper: #FBFCF9;
    --sage: #9CAB8E;
    --sage-dark: #6F8270;
    --sage-deep: #4A5A4C;
    --sage-tint: #C7D3BC;
    --ink: #1F2A2E;
    --ink-2: #3D4A4D;
    --muted: #6C7A75;
    --line: #D8DFD2;
    --line-2: #C7D0BE;
    --cream: #F4EDDC;
    --blush: #D9B5B0;
    --blush-deep: #C19089;
    --gold: #C9A45B;

    min-height: 100vh;
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    color: var(--ink);
    font-size: 14.5px;
    line-height: 1.5;
    background:
        radial-gradient(900px 700px at 0% 0%, rgba(199,211,188,0.18), transparent 60%),
        radial-gradient(700px 600px at 110% 110%, rgba(217,181,176,0.10), transparent 65%),
        var(--bg);
    position: relative;
}
.rv2-page::before {
    content: ''; position: fixed; inset: 0; pointer-events: none; z-index: 0; opacity: 0.4;
    background-image: radial-gradient(rgba(31,42,46,0.05) 1px, transparent 1px);
    background-size: 24px 24px;
}

/* ── FORM SIDE ── */
.rv2-form { position: relative; z-index: 1; display: flex; flex-direction: column; padding: 36px 56px; min-height: 100vh; }
.rv2-fheader { display: flex; align-items: center; justify-content: space-between; }
.rv2-logo img { height: 28px; width: auto; display: block; }
.rv2-lang { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: var(--paper); border: 1px solid var(--line); border-radius: 999px; font-size: 12.5px; color: var(--ink-2); font-weight: 600; }
.rv2-lang:hover { border-color: var(--sage); }

.rv2-fbody { flex: 1; display: flex; flex-direction: column; justify-content: center; max-width: 420px; width: 100%; margin: 0 auto; padding: 24px 0; }
.rv2-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-family: 'Cormorant', serif; font-style: italic; font-size: 17px; color: var(--blush-deep); margin-bottom: 12px; }
.rv2-eyebrow::before { content: ''; width: 22px; height: 1px; background: var(--blush); }
.rv2-h1 { font-family: 'Cormorant', serif; font-weight: 500; font-size: clamp(36px, 5vw, 50px); line-height: 1; letter-spacing: -0.02em; color: var(--ink); margin: 0; }
.rv2-intro { font-size: 14.5px; color: var(--ink-2); line-height: 1.6; margin: 14px 0 0; max-width: 360px; }

.rv2-soc-btn { margin-top: 26px; background: var(--paper); border: 1px solid var(--line); border-radius: 12px; padding: 12px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 10px; font-size: 13.5px; font-weight: 600; color: var(--ink); text-decoration: none; transition: border-color .12s, background .12s; }
.rv2-soc-btn:hover { border-color: var(--sage); background: var(--surface); }

.rv2-divider { display: flex; align-items: center; gap: 14px; margin: 22px 0 18px; font-family: 'Cormorant', serif; font-style: italic; color: var(--muted); font-size: 14px; }
.rv2-divider::before, .rv2-divider::after { content: ''; flex: 1; height: 1px; background: var(--line); }

.rv2-field { margin-bottom: 14px; }
.rv2-field-tight { margin-bottom: 8px; }
.rv2-field label { display: block; font-size: 11.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; letter-spacing: 0.02em; }
.rv2-input-wrap { position: relative; }
.rv2-input { width: 100%; background: var(--paper); border: 1px solid var(--line); border-radius: 12px; padding: 13px 14px; font-size: 14.5px; color: var(--ink); outline: none; transition: border-color .12s, box-shadow .12s; font-family: inherit; }
.rv2-input:focus { border-color: var(--sage); box-shadow: 0 0 0 3px rgba(156,171,142,0.15); }
.rv2-input::placeholder { color: var(--muted); }
.rv2-input-error { border-color: var(--blush); }
.rv2-input-error:focus { border-color: var(--blush-deep); box-shadow: 0 0 0 3px rgba(217,181,176,0.2); }
.rv2-input-icon { padding-left: 42px; }
.rv2-input-glyph { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); display: inline-flex; }
.rv2-input-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; padding: 4px; color: var(--muted); display: inline-flex; }
.rv2-input-toggle:hover { color: var(--ink-2); }
.rv2-err { margin: 6px 0 0; font-size: 12px; color: var(--blush-deep); }

/* strength */
.rv2-strength { display: flex; gap: 4px; margin-bottom: 8px; }
.rv2-seg { flex: 1; height: 3px; border-radius: 999px; transition: background .15s; }
.rv2-strength-hint { font-family: 'Cormorant', serif; font-style: italic; font-size: 13.5px; color: var(--muted); margin-bottom: 18px; }

/* terms */
.rv2-terms { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 20px; font-size: 12.5px; color: var(--ink-2); line-height: 1.5; cursor: pointer; }
.rv2-terms input { display: none; }
.rv2-cbox { width: 18px; height: 18px; border-radius: 5px; border: 1.5px solid var(--line-2); background: var(--paper); display: grid; place-items: center; transition: all .12s; flex-shrink: 0; margin-top: 1px; }
.rv2-terms input:checked + .rv2-cbox { background: var(--sage-deep); border-color: var(--sage-deep); }
.rv2-terms input:checked + .rv2-cbox::after { content: '✓'; color: #fff; font-size: 11px; font-weight: 700; }
.rv2-terms-txt a { color: var(--ink); font-weight: 700; text-decoration: underline; }
.rv2-terms-txt a:hover { color: var(--sage-deep); }

.rv2-btn-primary { width: 100%; background: var(--ink); color: var(--paper); border: none; border-radius: 12px; padding: 14px; font-size: 14.5px; font-weight: 600; letter-spacing: 0.02em; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background .15s, transform .12s; box-shadow: 0 12px 28px -12px rgba(31,42,46,0.4); }
.rv2-btn-primary:hover:not(:disabled) { background: #0F1618; transform: translateY(-1px); }
.rv2-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.rv2-spin { animation: rv2-spin 0.7s linear infinite; }
@keyframes rv2-spin { to { transform: rotate(360deg); } }

.rv2-fsignup { text-align: center; margin-top: 28px; padding-top: 22px; border-top: 1px solid var(--line); font-size: 14px; color: var(--ink-2); }
.rv2-fsignup a { color: var(--ink); font-weight: 700; text-decoration: none; border-bottom: 1px solid var(--sage); padding-bottom: 1px; }
.rv2-fsignup a:hover { color: var(--sage-deep); border-color: var(--sage-deep); }

.rv2-ffooter { font-size: 11.5px; color: var(--muted); text-align: center; }
.rv2-ffooter a { color: var(--muted); text-decoration: none; }
.rv2-ffooter a:hover { color: var(--ink-2); }

/* ── ART SIDE (left) ── */
.rv2-art {
    order: -1; position: relative; z-index: 1; overflow: hidden; padding: 56px;
    display: flex; flex-direction: column;
    background:
        radial-gradient(700px 500px at 0% 0%, rgba(217,181,176,0.22), transparent 60%),
        radial-gradient(600px 500px at 100% 100%, rgba(156,171,142,0.25), transparent 65%),
        linear-gradient(150deg, #2E4A3C 0%, #243830 55%, #1A2720 100%);
}
.rv2-art-center { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; text-align: center; }
.rv2-art-heading { font-family: 'Cormorant', serif; font-weight: 500; font-size: clamp(38px, 4.5vw, 52px); line-height: 1.05; letter-spacing: -0.02em; color: var(--paper); margin: 0; text-shadow: 0 2px 14px rgba(31,42,46,0.2); }
.rv2-art-heading .rv2-it { font-style: italic; color: var(--cream); font-weight: 400; }
.rv2-art-tag { font-family: 'Cormorant', serif; font-style: italic; font-size: 20px; color: rgba(251,252,249,0.85); margin: 18px auto 0; max-width: 360px; line-height: 1.4; }
.rv2-art-svg { margin-top: 32px; max-width: 460px; width: 100%; }
.rv2-art-bottom { display: flex; justify-content: space-between; align-items: flex-end; }
.rv2-art-meta { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.18em; color: rgba(251,252,249,0.55); text-transform: uppercase; }

/* ── RESPONSIVE ── */
@media (max-width: 980px) {
    .rv2-page { grid-template-columns: 1fr; }
    .rv2-art { display: none; }
    .rv2-form { padding: 28px 24px 24px; }
    .rv2-fbody { padding: 12px 0; }
}
</style>
