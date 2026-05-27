<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

defineProps({
    canResetPassword: { type: Boolean, default: true },
    status:           { type: String,  default: null  },
});

const { locale, toggleLocale, t } = useLocale();

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

const showPass = ref(false);

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head :title="t('auth.login_title')" />

    <div class="lv2-page">

        <!-- ── FORM SIDE ─────────────────────────────── -->
        <div class="lv2-form">
            <div class="lv2-fheader">
                <a href="/" class="lv2-logo" aria-label="TheDay">
                    <img src="/image/logo.svg" alt="TheDay" />
                </a>
                <button type="button" class="lv2-lang" @click="toggleLocale">
                    <span>{{ locale === 'en' ? '🇬🇧' : '🇮🇩' }}</span>
                    {{ locale === 'en' ? 'EN' : 'ID' }}
                </button>
            </div>

            <div class="lv2-fbody">
                <div class="lv2-eyebrow">{{ t('auth.login_v2_eyebrow') }}</div>
                <h1 class="lv2-h1">{{ t('auth.login_v2_headline') }}</h1>
                <p class="lv2-intro">{{ t('auth.login_v2_sub') }}</p>

                <!-- Status (e.g. after password reset) -->
                <div v-if="status" class="lv2-status">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ status }}
                </div>

                <!-- Google -->
                <a :href="route('auth.google')" class="lv2-soc-btn">
                    <svg width="18" height="18" viewBox="0 0 18 18">
                        <path d="M17.64 9.2c0-.64-.06-1.25-.16-1.83H9v3.47h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.61z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26a5.4 5.4 0 0 1-3.04.85 5.4 5.4 0 0 1-5.07-3.74H.96v2.34A9 9 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.96 10.71a5.41 5.41 0 0 1 0-3.42V4.95H.96a9 9 0 0 0 0 8.1l3-2.34z" fill="#FBBC05"/>
                        <path d="M9 3.58a4.86 4.86 0 0 1 3.44 1.34l2.58-2.59A9 9 0 0 0 9 0 9 9 0 0 0 .96 4.96l3 2.33A5.4 5.4 0 0 1 9 3.58z" fill="#EA4335"/>
                    </svg>
                    {{ t('auth.login_with_google') }}
                </a>

                <div class="lv2-divider">{{ t('auth.or_email') }}</div>

                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div class="lv2-field">
                        <label for="email">{{ t('auth.email') }}</label>
                        <div class="lv2-input-wrap">
                            <span class="lv2-input-glyph">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                            </span>
                            <input
                                id="email"
                                v-model="form.email"
                                class="lv2-input lv2-input-icon"
                                :class="{ 'lv2-input-error': form.errors.email }"
                                type="email" required autofocus autocomplete="username"
                                placeholder="nama@email.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="lv2-err">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="lv2-field">
                        <label for="password">{{ t('auth.password') }}</label>
                        <div class="lv2-input-wrap">
                            <span class="lv2-input-glyph">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                id="password"
                                v-model="form.password"
                                class="lv2-input lv2-input-icon"
                                :class="{ 'lv2-input-error': form.errors.password }"
                                style="padding-right: 42px"
                                :type="showPass ? 'text' : 'password'"
                                required autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <button type="button" class="lv2-input-toggle" tabindex="-1" @click="showPass = !showPass">
                                <svg v-if="!showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="lv2-err">{{ form.errors.password }}</p>
                    </div>

                    <div class="lv2-row-between">
                        <label class="lv2-checkbox">
                            <input v-model="form.remember" type="checkbox"/>
                            <span class="lv2-cbox"></span>
                            {{ t('auth.remember_me') }}
                        </label>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="lv2-forgot">
                            {{ t('auth.forgot_password') }}
                        </Link>
                    </div>

                    <button type="submit" class="lv2-btn-primary" :disabled="form.processing">
                        <template v-if="form.processing">
                            <svg class="lv2-spin" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
                                <path fill="currentColor" opacity="0.75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ t('auth.login_submitting') }}
                        </template>
                        <template v-else>
                            {{ t('auth.login_v2_submit') }}
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </template>
                    </button>
                </form>

                <div class="lv2-fsignup">
                    {{ t('auth.login_v2_footer') }}
                    <Link :href="route('register')">{{ t('auth.login_v2_create') }} →</Link>
                </div>
            </div>

            <div class="lv2-ffooter">
                © 2026 Theday Indonesia · <a :href="route('legal.terms')">{{ locale === 'en' ? 'Terms' : 'Syarat' }}</a> · <a :href="route('legal.privacy')">{{ locale === 'en' ? 'Privacy' : 'Privasi' }}</a>
            </div>
        </div>

        <!-- ── ART SIDE (desktop only) ─────────────────────────────── -->
        <div class="lv2-art">
            <div class="lv2-art-center">
                <h2 class="lv2-art-heading">{{ t('auth.login_v2_art_h1') }}<br/><span class="lv2-it">{{ t('auth.login_v2_art_h2') }}</span></h2>
                <p class="lv2-art-tag">{{ t('auth.login_v2_art_tag') }}</p>

                <!-- Journey illustration -->
                <svg class="lv2-art-svg" viewBox="0 0 460 200" xmlns="http://www.w3.org/2000/svg">
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

            <div class="lv2-art-bottom">
                <div class="lv2-art-meta">VOL. I · 2026</div>
                <div class="lv2-art-meta">DIBUAT DENGAN ♡ DI JAKARTA</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.lv2-page {
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
.lv2-page::before {
    content: ''; position: fixed; inset: 0; pointer-events: none; z-index: 0; opacity: 0.4;
    background-image: radial-gradient(rgba(31,42,46,0.05) 1px, transparent 1px);
    background-size: 24px 24px;
}

/* ── FORM SIDE ── */
.lv2-form {
    position: relative; z-index: 1;
    display: flex; flex-direction: column;
    padding: 36px 56px;
    min-height: 100vh;
}
.lv2-fheader { display: flex; align-items: center; justify-content: space-between; }
.lv2-logo img { height: 28px; width: auto; display: block; }
.lv2-lang {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; background: var(--paper); border: 1px solid var(--line);
    border-radius: 999px; font-size: 12.5px; color: var(--ink-2); font-weight: 600;
}
.lv2-lang:hover { border-color: var(--sage); }

.lv2-fbody {
    flex: 1; display: flex; flex-direction: column; justify-content: center;
    max-width: 420px; width: 100%; margin: 0 auto; padding: 32px 0;
}
.lv2-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: 'Cormorant', serif; font-style: italic; font-size: 17px;
    color: var(--sage-dark); margin-bottom: 14px;
}
.lv2-eyebrow::before { content: ''; width: 22px; height: 1px; background: var(--sage); }
.lv2-h1 {
    font-family: 'Cormorant', serif; font-weight: 500;
    font-size: clamp(38px, 5vw, 54px); line-height: 1; letter-spacing: -0.02em;
    color: var(--ink); margin: 0;
}
.lv2-intro { font-size: 15px; color: var(--ink-2); line-height: 1.65; margin: 18px 0 0; max-width: 360px; }

.lv2-status {
    margin-top: 22px; display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; border-radius: 12px;
    background: var(--sage-soft, #DCE4D3); border: 1px solid var(--sage-tint); color: var(--sage-deep);
    font-size: 13.5px;
}

.lv2-soc-btn {
    margin-top: 28px;
    background: var(--paper); border: 1px solid var(--line); border-radius: 12px;
    padding: 12px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    font-size: 13.5px; font-weight: 600; color: var(--ink); text-decoration: none;
    transition: border-color .12s, background .12s;
}
.lv2-soc-btn:hover { border-color: var(--sage); background: var(--surface); }

.lv2-divider {
    display: flex; align-items: center; gap: 14px; margin: 24px 0 20px;
    font-family: 'Cormorant', serif; font-style: italic; color: var(--muted); font-size: 14px;
}
.lv2-divider::before, .lv2-divider::after { content: ''; flex: 1; height: 1px; background: var(--line); }

.lv2-field { margin-bottom: 14px; }
.lv2-field label { display: block; font-size: 11.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; letter-spacing: 0.02em; }
.lv2-input-wrap { position: relative; }
.lv2-input {
    width: 100%; background: var(--paper); border: 1px solid var(--line);
    border-radius: 12px; padding: 13px 14px; font-size: 14.5px; color: var(--ink);
    outline: none; transition: border-color .12s, box-shadow .12s; font-family: inherit;
}
.lv2-input:focus { border-color: var(--sage); box-shadow: 0 0 0 3px rgba(156,171,142,0.15); }
.lv2-input::placeholder { color: var(--muted); }
.lv2-input-error { border-color: var(--blush); }
.lv2-input-error:focus { border-color: var(--blush-deep); box-shadow: 0 0 0 3px rgba(217,181,176,0.2); }
.lv2-input-icon { padding-left: 42px; }
.lv2-input-glyph { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); display: inline-flex; }
.lv2-input-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; padding: 4px; color: var(--muted); display: inline-flex; }
.lv2-input-toggle:hover { color: var(--ink-2); }
.lv2-err { margin: 6px 0 0; font-size: 12px; color: var(--blush-deep); }

.lv2-row-between { display: flex; justify-content: space-between; align-items: center; margin: 4px 0 22px; font-size: 13px; }
.lv2-checkbox { display: inline-flex; align-items: center; gap: 8px; color: var(--ink-2); cursor: pointer; user-select: none; }
.lv2-checkbox input { display: none; }
.lv2-cbox { width: 18px; height: 18px; border-radius: 5px; border: 1.5px solid var(--line-2); background: var(--paper); display: grid; place-items: center; transition: all .12s; }
.lv2-checkbox input:checked + .lv2-cbox { background: var(--sage-deep); border-color: var(--sage-deep); }
.lv2-checkbox input:checked + .lv2-cbox::after { content: '✓'; color: #fff; font-size: 11px; font-weight: 700; }
.lv2-forgot { color: var(--sage-deep); text-decoration: none; font-weight: 600; }
.lv2-forgot:hover { text-decoration: underline; }

.lv2-btn-primary {
    width: 100%; background: var(--ink); color: var(--paper); border: none; border-radius: 12px;
    padding: 14px; font-size: 14.5px; font-weight: 600; letter-spacing: 0.02em;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .15s, transform .12s; box-shadow: 0 12px 28px -12px rgba(31,42,46,0.4);
}
.lv2-btn-primary:hover:not(:disabled) { background: #0F1618; transform: translateY(-1px); }
.lv2-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.lv2-spin { animation: lv2-spin 0.7s linear infinite; }
@keyframes lv2-spin { to { transform: rotate(360deg); } }

.lv2-fsignup { text-align: center; margin-top: 34px; padding-top: 24px; border-top: 1px solid var(--line); font-size: 14px; color: var(--ink-2); }
.lv2-fsignup a { color: var(--ink); font-weight: 700; text-decoration: none; border-bottom: 1px solid var(--sage); padding-bottom: 1px; }
.lv2-fsignup a:hover { color: var(--sage-deep); border-color: var(--sage-deep); }

.lv2-ffooter { font-size: 11.5px; color: var(--muted); text-align: center; }
.lv2-ffooter a { color: var(--muted); text-decoration: none; }
.lv2-ffooter a:hover { color: var(--ink-2); }

/* ── ART SIDE ── */
.lv2-art {
    order: -1; /* art on the left, form on the right */
    position: relative; z-index: 1; overflow: hidden; padding: 56px;
    display: flex; flex-direction: column;
    background:
        radial-gradient(700px 500px at 0% 0%, rgba(217,181,176,0.22), transparent 60%),
        radial-gradient(600px 500px at 100% 100%, rgba(156,171,142,0.25), transparent 65%),
        linear-gradient(150deg, #2E4A3C 0%, #243830 55%, #1A2720 100%);
}
.lv2-art-center { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; text-align: center; position: relative; }

.lv2-art-heading {
    font-family: 'Cormorant', serif; font-weight: 500;
    font-size: clamp(38px, 4.5vw, 52px); line-height: 1.05; letter-spacing: -0.02em;
    color: var(--paper); margin: 0; text-shadow: 0 2px 14px rgba(31,42,46,0.2);
}
.lv2-art-heading .lv2-it { font-style: italic; color: var(--cream); font-weight: 400; }
.lv2-art-tag { font-family: 'Cormorant', serif; font-style: italic; font-size: 20px; color: rgba(251,252,249,0.85); margin: 18px auto 0; max-width: 360px; line-height: 1.4; }
.lv2-art-svg { margin-top: 32px; max-width: 460px; width: 100%; }

.lv2-art-bottom { display: flex; justify-content: space-between; align-items: flex-end; }
.lv2-art-meta { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.18em; color: rgba(251,252,249,0.55); text-transform: uppercase; }

/* ── RESPONSIVE: collapse to single column, hide art ── */
@media (max-width: 980px) {
    .lv2-page { grid-template-columns: 1fr; }
    .lv2-art { display: none; }
    .lv2-form { padding: 28px 24px 24px; }
    .lv2-fbody { padding: 16px 0; }
}
</style>
