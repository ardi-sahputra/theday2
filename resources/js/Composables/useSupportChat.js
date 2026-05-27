import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';

const BASE_TITLE = 'TheDay';

// ── Module-level singleton state (shared across all useSupportChat consumers) ──
// Multiple components (e.g. SupportBubble desktop + SupportHeaderIcon mobile, or
// Pages/Dashboard/Support.vue + layout SupportHeaderIcon) call useSupportChat()
// and must share state to avoid double-polling and duplicate message lists.
const messages    = ref([]);
const unreadCount = ref(0);
const isOpen      = ref(false);
const isSending   = ref(false);
const sendError   = ref('');
const adminStatus = ref({ online: false, work_hours_open: false });

let pollTimer  = null;
let lastMsgId  = 0;
let lastInteractionAt = Date.now();
let mountedConsumers = 0;            // ref-count to know when last consumer unmounts
let watcherStop = null;

const idleMs = computed(() => Date.now() - lastInteractionAt);

function currentInterval() {
    if (typeof document === 'undefined') return null;
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
    } catch (_) {
        // silent retry on next interval
    }
}

function updateTabTitle(count) {
    if (typeof document === 'undefined') return;
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

/**
 * Seed messages + lastMsgId from server-rendered props (Inertia SSR).
 * Call this BEFORE polling starts to avoid `since=0` fetching all messages again
 * and pushing duplicates into the list.
 */
function seedMessages(initialMessages) {
    if (!Array.isArray(initialMessages)) return;
    messages.value = initialMessages.map(m => ({ ...m }));
    lastMsgId = initialMessages.length
        ? initialMessages[initialMessages.length - 1].id
        : 0;
}

/**
 * Reset state (for logout or test scenarios). Idempotent.
 */
function resetState() {
    stopPolling();
    messages.value = [];
    unreadCount.value = 0;
    isOpen.value = false;
    isSending.value = false;
    sendError.value = '';
    adminStatus.value = { online: false, work_hours_open: false };
    lastMsgId = 0;
    if (typeof document !== 'undefined') document.title = BASE_TITLE;
}

export function useSupportChat() {
    onMounted(() => {
        mountedConsumers += 1;
        if (mountedConsumers === 1) {
            // First consumer: start global polling + listeners
            startPolling();
            document.addEventListener('visibilitychange', visibilityHandler);
            ['mousemove', 'keydown', 'touchstart', 'click'].forEach(ev =>
                window.addEventListener(ev, activityHandler, { passive: true })
            );
            watcherStop = watch(isOpen, (open) => {
                if (open) markRead();
            });
        }
    });

    onBeforeUnmount(() => {
        mountedConsumers = Math.max(0, mountedConsumers - 1);
        if (mountedConsumers === 0) {
            // Last consumer: tear down listeners + polling
            stopPolling();
            document.removeEventListener('visibilitychange', visibilityHandler);
            ['mousemove', 'keydown', 'touchstart', 'click'].forEach(ev =>
                window.removeEventListener(ev, activityHandler)
            );
            if (watcherStop) { watcherStop(); watcherStop = null; }
            if (typeof document !== 'undefined') document.title = BASE_TITLE;
        }
    });

    return {
        messages, unreadCount, isOpen, isSending, sendError, adminStatus,
        sendMessage, markRead, fetchMessages, noteInteraction, seedMessages, resetState,
    };
}
