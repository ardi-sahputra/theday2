<template>
    <div class="isg-cartouche">
        <svg
            class="isg-cartouche-frame"
            viewBox="0 0 480 280"
            preserveAspectRatio="xMidYMid meet"
            aria-hidden="true"
        >
            <!-- top arch -->
            <path d="M 60 40 Q 240 0 420 40" fill="none" stroke="currentColor" stroke-width="1.5" />
            <!-- bottom arch (mirror) -->
            <path d="M 60 240 Q 240 280 420 240" fill="none" stroke="currentColor" stroke-width="1.5" />
            <!-- left vertical ornament -->
            <path d="M 60 40 L 60 240" fill="none" stroke="currentColor" stroke-width="1" />
            <circle cx="60" cy="100" r="2.5" fill="currentColor" />
            <circle cx="60" cy="180" r="2.5" fill="currentColor" />
            <!-- right vertical ornament -->
            <path d="M 420 40 L 420 240" fill="none" stroke="currentColor" stroke-width="1" />
            <circle cx="420" cy="100" r="2.5" fill="currentColor" />
            <circle cx="420" cy="180" r="2.5" fill="currentColor" />
            <!-- small decorative dots at arches -->
            <circle cx="240" cy="8"   r="2" fill="currentColor" />
            <circle cx="240" cy="272" r="2" fill="currentColor" />
        </svg>
        <div class="isg-cartouche-content">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.isg-cartouche {
    position: relative;
    display: inline-block;
    color: var(--isg-gold, #c9a961);
    padding: 56px 72px;
    max-width: 480px;
    width: 100%;
}
.isg-cartouche-frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}
.isg-cartouche-content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
</style>
