import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';

const BASE_TITLE = 'TheDay';

export function useSupportChat() {
    const messages    = ref([]);
    const unreadCount = ref(0);
    const isOpen      = ref(false);
    const isSending   = ref(false);
    const sendError   = ref('');
    const adminStatus = ref({ online: false, work_hours_open: false });

    let pollTimer  = null;
    let lastMsgId  = 0;
    let lastInteractionAt = Date.now();

    const idleMs = computed(() => Date.now() - lastInteractionAt);

    function currentInterval() {
        if (document.visibilityState !== 'visible') return null;
        if (idleMs.value > 5 * 60 * 1000) return 60000;
        if (isOpen.value) return 10000;
        return 30000;
    }

    async function fetchMessages() {
        try {
            const { data } = await axios.get('/dashboard/support/messages', {
                params: { since: lastMsgId },
            });

            if (Array.isArray(data.messages) && data.messages.length) {
                messages.value.push(...data.messages);
                lastMsgId = data.messages[data.messages.length - 1].id;
            }

            unreadCount.value = data.unread_count ?? 0;
            adminStatus.value = data.admin_status ?? adminStatus.value;
            updateTabTitle(unreadCount.value);
        } catch (e) {
            // silent retry on next interval
        }
    }

    function updateTabTitle(count) {
        if (!isOpen.value && count > 0) {
            document.title = `(${count}) ${BASE_TITLE} — Pesan baru`;
        } else {
            document.title = BASE_TITLE;
        }
    }

    async function sendMessage(body, imageFile = null) {
        if (!body && !imageFile) return;
        isSending.value = true;
        sendError.value = '';
        try {
            const form = new FormData();
            if (body) form.append('body', body);
            if (imageFile) form.append('image', imageFile);

            const { data } = await axios.post('/dashboard/support/messages', form, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            messages.value.push(data.message);
            lastMsgId = data.message.id;
            lastInteractionAt = Date.now();
        } catch (e) {
            sendError.value = e.response?.data?.message ?? 'Gagal mengirim pesan';
        } finally {
            isSending.value = false;
        }
    }

    async function markRead() {
        if (unreadCount.value === 0) return;
        try {
            await axios.post('/dashboard/support/mark-read');
            unreadCount.value = 0;
            updateTabTitle(0);
        } catch (_) {}
    }

    function noteInteraction() { lastInteractionAt = Date.now(); }

    function scheduleNextPoll() {
        clearTimeout(pollTimer);
        const ms = currentInterval();
        if (ms === null) return;
        pollTimer = setTimeout(async () => {
            await fetchMessages();
            scheduleNextPoll();
        }, ms);
    }

    function startPolling() {
        scheduleNextPoll();
        fetchMessages();
    }

    function stopPolling() {
        clearTimeout(pollTimer);
        pollTimer = null;
    }

    function visibilityHandler() {
        if (document.visibilityState === 'visible') {
            fetchMessages();
            scheduleNextPoll();
        } else {
            stopPolling();
        }
    }

    function activityHandler() { noteInteraction(); }

    onMounted(() => {
        startPolling();
        document.addEventListener('visibilitychange', visibilityHandler);
        ['mousemove', 'keydown', 'touchstart', 'click'].forEach(ev =>
            window.addEventListener(ev, activityHandler, { passive: true })
        );
    });

    onBeforeUnmount(() => {
        stopPolling();
        document.removeEventListener('visibilitychange', visibilityHandler);
        ['mousemove', 'keydown', 'touchstart', 'click'].forEach(ev =>
            window.removeEventListener(ev, activityHandler)
        );
        document.title = BASE_TITLE;
    });

    watch(isOpen, (open) => {
        if (open) markRead();
    });

    return {
        messages, unreadCount, isOpen, isSending, sendError, adminStatus,
        sendMessage, markRead, fetchMessages, noteInteraction,
    };
}
