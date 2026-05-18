<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    accounts:        { type: Array,    default: () => [] },
    copyToClipboard: { type: Function, required: true },
    copiedAccount:   { type: [String, null], default: null },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-gift"
        data-slide-key="gift"
        data-slide-theme="light"
        :style="{
            '--sw-bg-from':       '#F4C430',
            '--sw-bg-to':         '#FFD700',
            '--sw-bg-direction':  '140deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header sw-slide-header--dark">
                <span class="sw-section-eyebrow">TIP THE ARTISTS</span>
                <span class="sw-slide-counter">08 / 10</span>
            </header>
            <h2 class="sw-slide-title sw-slide-title--dark">SUPPORT THE WEDDING</h2>
            <p class="sw-gift-sub">Doa restu kamu udah cukup. Tapi kalau berkenan&hellip;</p>

            <div class="sw-gift-cards">
                <article
                    v-for="(acc, idx) in accounts"
                    :key="acc.account_number ?? idx"
                    class="sw-gift-card"
                    :style="{ '--d': (idx * 0.12).toFixed(2) + 's' }"
                >
                    <p class="sw-gift-bank">{{ acc.bank }}</p>
                    <p class="sw-gift-name">{{ acc.account_name }}</p>
                    <p class="sw-gift-num">{{ acc.account_number }}</p>
                    <button
                        type="button"
                        class="sw-gift-copy-btn"
                        @click="copyToClipboard(acc.account_number, 'Nomor rekening disalin')"
                    >
                        {{ copiedAccount === acc.account_number ? 'COPIED' : 'COPY NUMBER' }}
                    </button>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-slide-gift { color: #191414; }
.sw-slide-header--dark, .sw-slide-title--dark { color: #191414; }
.sw-gift-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: rgba(25,20,20,0.85);
    margin: 8px 0 24px;
}
.sw-gift-cards { display: flex; flex-direction: column; gap: 16px; }
.sw-gift-card {
    background: rgba(25,20,20,0.08);
    border-radius: 16px;
    padding: 24px;
    display: flex; flex-direction: column; gap: 6px;
    opacity: 0;
    transform: translateY(30px);
    transition:
        opacity 0.6s ease-out var(--d, 0s),
        transform 0.6s ease-out var(--d, 0s);
}
:global(.sw-visible) .sw-gift-card { opacity: 1; transform: translateY(0); }
.sw-gift-bank {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.14em;
    color: #191414;
    margin: 0;
}
.sw-gift-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: #191414;
    margin: 0;
}
.sw-gift-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 24px;
    color: #191414;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.04em;
    margin: 0;
}
.sw-gift-copy-btn {
    align-self: flex-start;
    margin-top: 8px;
    padding: 10px 18px;
    background: #191414;
    color: #FFFFFF;
    border: none;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.1em;
    cursor: pointer;
    transition: background 0.2s ease;
}
.sw-gift-copy-btn:hover { background: #2a1f1f; }
@media (prefers-reduced-motion: reduce) {
    .sw-gift-card { opacity: 1; transform: none; transition: none; }
    .sw-gift-copy-btn { transition: none; }
}
</style>
