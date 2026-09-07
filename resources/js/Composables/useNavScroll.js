import { ref, onMounted, onBeforeUnmount } from 'vue';

// Module-level singleton — all components share the same isScrolling ref.
const isScrolling = ref(false);
let timer = null;
let scrollStartAt = 0;
let mountCount = 0;

// Small scrolls (a nudge, a tap-scroll) shouldn't shrink the nav — only
// commit to pill mode once scrolling has actually continued for a beat.
// Checked against elapsed time on each event (not a blind timeout) so a
// short scroll that already stopped never flashes the pill late.
const SHRINK_DELAY = 200;

function onScroll() {
    clearTimeout(timer);
    if (!isScrolling.value) {
        if (!scrollStartAt) scrollStartAt = Date.now();
        if (Date.now() - scrollStartAt >= SHRINK_DELAY) {
            isScrolling.value = true;
        }
    }
    timer = setTimeout(() => {
        isScrolling.value = false;
        scrollStartAt = 0;
    }, 350);
}

export function useNavScroll() {
    onMounted(() => {
        if (mountCount === 0) window.addEventListener('scroll', onScroll, { passive: true });
        mountCount++;
    });
    onBeforeUnmount(() => {
        mountCount--;
        if (mountCount === 0) { window.removeEventListener('scroll', onScroll); clearTimeout(timer); }
    });
    return { isScrolling };
}
