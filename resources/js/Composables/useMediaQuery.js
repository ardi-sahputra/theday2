import { ref, onMounted, onBeforeUnmount } from 'vue';

export function useMediaQuery(query) {
    const matches = ref(false);
    let mql = null;

    function update() {
        matches.value = mql?.matches ?? false;
    }

    onMounted(() => {
        mql = window.matchMedia(query);
        update();
        mql.addEventListener('change', update);
    });

    onBeforeUnmount(() => {
        mql?.removeEventListener('change', update);
    });

    return matches;
}
