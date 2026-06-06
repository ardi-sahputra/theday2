import { onMounted } from 'vue';

/**
 * Deep-link highlight: reads ?focus=<id> from the URL on mount, scrolls the
 * matching [data-focus-id="<id>"] element into view and pulses a highlight ring.
 *
 * Usage:
 *   useFocusHighlight();                 // call in <script setup>
 *   <div :data-focus-id="item.id">       // mark focusable rows in template
 */
export function useFocusHighlight({ retries = 8, interval = 150 } = {}) {
    onMounted(() => {
        const id = new URLSearchParams(window.location.search).get('focus');
        if (!id) return;

        let tries = 0;
        const tick = () => {
            const el = document.querySelector(`[data-focus-id="${CSS.escape(id)}"]`);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('focus-pulse');
                setTimeout(() => el.classList.remove('focus-pulse'), 2400);
                // Clean the param so a refresh doesn't re-trigger.
                const url = new URL(window.location.href);
                url.searchParams.delete('focus');
                window.history.replaceState({}, '', url);
                return;
            }
            if (++tries < retries) setTimeout(tick, interval);
        };
        // Wait a frame for list data to render.
        setTimeout(tick, interval);
    });
}
