<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    username:   { type: String, default: 'thedaywedding' },
    avatarUrl:  { type: String, default: '/images/templates/ig-stories/avatar-default.svg' },
    ringStyle:  { type: String, default: 'gradient' }, // 'gradient' | 'solid'
    timestamp:  { type: String, default: 'now' },
})
const emit = defineEmits(['menu'])
</script>

<template>
    <div class="igs-profile">
        <div
            class="igs-avatar-ring"
            :class="ringStyle === 'gradient' ? 'igs-avatar-ring--gradient' : 'igs-avatar-ring--solid'"
        >
            <img class="igs-avatar-img" :src="avatarUrl" :alt="username" loading="eager"/>
        </div>
        <div class="igs-profile-meta">
            <span class="igs-profile-username">{{ username }}</span>
            <span class="igs-profile-timestamp">{{ timestamp }}</span>
        </div>
        <button
            type="button"
            class="igs-profile-menu"
            aria-label="Story options"
            @click="emit('menu')"
        >
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <circle cx="5"  cy="12" r="1.8" fill="currentColor"/>
                <circle cx="12" cy="12" r="1.8" fill="currentColor"/>
                <circle cx="19" cy="12" r="1.8" fill="currentColor"/>
            </svg>
        </button>
    </div>
</template>

<style scoped>
@property --igs-ring-angle {
    syntax: '<angle>';
    initial-value: 0deg;
    inherits: false;
}
.igs-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    color: #FFFFFF;
}
.igs-avatar-ring {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    padding: 2px;
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.igs-avatar-ring--gradient {
    background: conic-gradient(
        from var(--igs-ring-angle, 0deg),
        #833ab4 0%, #fd1d1d 25%, #fcb045 50%, #833ab4 75%, #fd1d1d 100%
    );
    animation: igs-ring-rotate 8s linear infinite;
}
.igs-avatar-ring--solid {
    background: #FFFFFF;
}
@keyframes igs-ring-rotate {
    from { --igs-ring-angle: 0deg; }
    to   { --igs-ring-angle: 360deg; }
}
.igs-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    background: #1a1a1a;
    display: block;
}
.igs-profile-meta {
    display: flex;
    flex-direction: column;
    gap: 1px;
    flex: 1;
    min-width: 0;
}
.igs-profile-username {
    font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif;
    font-weight: 700;
    font-size: 13px;
    color: #FFFFFF;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.igs-profile-timestamp {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 12px;
    color: rgba(255,255,255,0.72);
    line-height: 1.2;
}
.igs-profile-menu {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: #FFFFFF;
    cursor: pointer;
    margin-right: -8px;
}
.igs-profile-menu:focus-visible {
    outline: 2px solid #FFFFFF;
    outline-offset: 2px;
    border-radius: 8px;
}
@media (prefers-reduced-motion: reduce) {
    .igs-avatar-ring--gradient { animation: none; }
}
</style>
