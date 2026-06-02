<script setup>
import { computed } from 'vue';

/**
 * "theday" brand wordmark for non-Netflix templates. Renders as text so it
 * inherits `color` from the host element — each template tints its own
 * watermark via its `.xxx-watermark { color: ... }` rule (the tint the
 * templates already declared but never got, because the old TheDayLogo was a
 * fixed red PNG). Netflix keeps its own red TheDayLogo; this never goes there.
 */
const props = defineProps({
  height: { type: [Number, String], default: 20 }, // → font-size
  muted:  { type: Boolean, default: false },
});

const fontSize = computed(() =>
  typeof props.height === 'number' ? `${props.height}px` : props.height,
);
</script>

<template>
  <span
    class="brand-watermark"
    :class="{ 'brand-watermark--muted': muted }"
    :style="{ fontSize }"
    role="img"
    aria-label="Theday"
  ><span class="bw-the">the</span><span class="bw-day">day</span></span>
</template>

<style scoped>
.brand-watermark {
  /* No `color` here on purpose — inherit so the host class can tint it. */
  display: inline-block;
  font-family: 'Fraunces', 'Cormorant Garamond', 'Cormorant', Georgia, serif;
  line-height: 1;
  white-space: nowrap;
  user-select: none;
}
.brand-watermark--muted { opacity: 0.4; }
.bw-the { font-style: italic; font-weight: 300; }
.bw-day { font-weight: 600; letter-spacing: -0.02em; }
</style>
