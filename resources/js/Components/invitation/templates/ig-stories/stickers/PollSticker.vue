<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    question:  { type: String, required: true },
    option1:   { type: String, required: true },
    option2:   { type: String, required: true },
    selected:  { type: String, default: null }, // 'one' | 'two' | null
})
const emit = defineEmits(['vote'])
</script>

<template>
    <div class="igs-sticker igs-poll" role="group" :aria-label="question">
        <p class="igs-poll-question">{{ question }}</p>
        <div class="igs-poll-options">
            <button
                type="button"
                class="igs-poll-option"
                :class="{ 'igs-poll-option--selected': selected === 'one' }"
                @click="emit('vote', 'one')"
            >
                <span class="igs-poll-option-fill" v-if="selected === 'one'" aria-hidden="true"/>
                <span class="igs-poll-option-label">{{ option1 }}</span>
            </button>
            <button
                type="button"
                class="igs-poll-option"
                :class="{ 'igs-poll-option--selected': selected === 'two' }"
                @click="emit('vote', 'two')"
            >
                <span class="igs-poll-option-fill" v-if="selected === 'two'" aria-hidden="true"/>
                <span class="igs-poll-option-label">{{ option2 }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.igs-poll {
    background: rgba(255,255,255,0.92);
    border-radius: 12px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    color: #191919;
    box-shadow: 0 6px 24px rgba(0,0,0,0.18);
}
.igs-poll-question {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    margin: 0;
    color: #191919;
    text-align: center;
    letter-spacing: -0.01em;
}
.igs-poll-options {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.igs-poll-option {
    position: relative;
    overflow: hidden;
    background: #FFFFFF;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 9999px;
    padding: 12px 16px;
    min-height: 44px;
    color: #191919;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: transform 0.15s ease;
    width: 100%;
    text-align: center;
}
.igs-poll-option:hover  { transform: translateY(-1px); }
.igs-poll-option:active { transform: translateY(0); }
.igs-poll-option--selected {
    background: rgba(131,58,180,0.08);
    border-color: rgba(131,58,180,0.35);
}
.igs-poll-option-fill {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(131,58,180,0.18), rgba(253,29,29,0.12));
    transform-origin: left center;
    animation: igs-poll-fill 0.3s ease-out forwards;
    z-index: 0;
}
.igs-poll-option-label {
    position: relative;
    z-index: 1;
}
@keyframes igs-poll-fill {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-poll-option,
    .igs-poll-option:hover { transform: none; transition: none; }
    .igs-poll-option-fill { animation: none; transform: scaleX(1); }
}
</style>
