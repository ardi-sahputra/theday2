import { ref, onMounted, onBeforeUnmount } from 'vue';

// Module-level singleton — all components share the same isScrolling ref.
const isScrolling = ref(false);
let timer = null;
let mountCount = 0;

function onScroll() {
    isScrolling.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => { isScrolling.value = false; }, 350);
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
