import { reactive, ref, computed } from 'vue';
import axios from 'axios';

// ─── axios instance with CSRF cookie ─────────────────────────────────────────
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken   = true;

// ─── Section default data (for new sections without dedicated tables) ─────────

const SECTION_DATA_DEFAULTS = {
    cover:               { button_text: 'Buka Undangan' },
    konten_utama:        {},
    couple:              {},
    quote:               { text: '', source: '' },
    events:              {},
    countdown:           {},
    live_streaming:      { url: '', platform: '' },
    additional_info:     { text: '' },
    gallery:             {},
    video:               { url: '', caption: '' },
    love_story:          { stories: [] },
    rsvp:                { deadline: '' },
    wishes:              {},
    gift:                { accounts: [] },
    music:               {},
    theme_settings:      {},
    slug_settings:       {},
    password_protection: {},
    preview_and_publish: {},
};

// Build a keyed map from the sections array returned by the server
function buildSectionMap(sectionsArray) {
    const map = {};
    for (const s of sectionsArray ?? []) {
        map[s.section_key] = reactive({
            ...s,
            data_json: reactive({ ...(SECTION_DATA_DEFAULTS[s.section_key] ?? {}), ...(s.data_json ?? {}) }),
        });
    }
    // Fill in any section not yet in DB (e.g. old invitation opened before migration)
    const allKeys = Object.keys(SECTION_DATA_DEFAULTS);
    for (const key of allKeys) {
        if (!map[key]) {
            map[key] = reactive({
                section_key: key,
                is_enabled:  false,
                is_required: false,
                completion_status: 'empty',
                data_json: reactive({ ...(SECTION_DATA_DEFAULTS[key] ?? {}) }),
            });
        }
    }
    return map;
}

// ─── Default state factories ──────────────────────────────────────────────────

function defaultDetails() {
    return {
        groom_name:          '',
        groom_nickname:      '',
        groom_instagram:     '',
        bride_name:          '',
        bride_nickname:      '',
        bride_instagram:     '',
        groom_parent_names:  '',
        bride_parent_names:  '',
        groom_photo_url:     '',
        bride_photo_url:     '',
        opening_text:        '',
        closing_text:        '',
        cover_photo_url:     '',
    };
}

function defaultEvent() {
    return {
        _key:          Date.now() + Math.random(),
        event_name:    '',
        event_date:    '',
        start_time:    '',
        end_time:      '',
        venue_name:    '',
        venue_address: '',
        maps_url:      '',
        sort_order:    0,
    };
}

function defaultConfig() {
    return {
        primary_color: '#D4A373',
        font:          'Playfair Display',
    };
}

// ─── Composable ───────────────────────────────────────────────────────────────

export function useInvitationEditor(template, invitation = null) {

    // ── Core identifiers ─────────────────────────────────────────
    const invitationId  = ref(invitation?.id ?? null);
    const currentStep   = ref(1);
    const isSaving      = ref(false);
    const saveError     = ref(null);
    const lastSavedStep = ref(0);

    // ── Step 1 — Basic info ──────────────────────────────────────
    const basic = reactive({
        title:      invitation?.title ?? '',
        event_type: invitation?.event_type ?? 'pernikahan',
    });

    const details = reactive({
        ...defaultDetails(),
        ...(invitation?.details ?? {}),
    });

    // ── Step 2 — Events ──────────────────────────────────────────
    const events = ref(invitation?.events?.length
        ? invitation.events.map((e, i) => ({
            _key:          Date.now() + Math.random() + i,
            _serverId:     e.id,
            event_name:    e.event_name    ?? '',
            event_date:    e.event_date    ?? '',
            start_time:    e.start_time ? e.start_time.substring(0, 5) : '',
            end_time:      e.end_time   ? e.end_time.substring(0, 5)   : '',
            venue_name:    e.venue_name    ?? '',
            venue_address: e.venue_address ?? '',
            maps_url:      e.maps_url      ?? '',
            sort_order:    e.sort_order    ?? i,
        }))
        : [defaultEvent()]
    );

    // ── Step 3 — Gallery ─────────────────────────────────────────
    const galleries = ref(invitation?.galleries?.length
        ? invitation.galleries.map((g, i) => ({
            _key:      Date.now() + Math.random() + i,
            _serverId: g.id,
            image_url: g.image_url,
            caption:   g.caption ?? '',
        }))
        : []
    );

    // ── Step 4 — Music ───────────────────────────────────────────
    const selectedMusic = ref(invitation?.music?.[0]
        ? { title: invitation.music[0].title, file_url: invitation.music[0].file_url }
        : null
    );

    // ── Step 5 — Config ──────────────────────────────────────────
    const customConfig = reactive({
        ...defaultConfig(),
        ...(template?.default_config ?? {}),
        ...(invitation?.custom_config ?? {}),
    });

    // ── Step 6 — Publish ─────────────────────────────────────────
    const publish = reactive({
        slug:                  invitation?.slug                  ?? '',
        is_password_protected: invitation?.is_password_protected ?? false,
        password:              '',
        expires_at:            invitation?.expires_at            ?? '',
    });

    // ── Sections map (keyed by section_key) ───────────────────────
    const sections = reactive(buildSectionMap(invitation?.sections ?? []));

    // ── Restore step from DB ──────────────────────────────────────
    if (invitation?.id) {
        const saved = invitation.current_step ?? 0;
        lastSavedStep.value = saved;
        currentStep.value   = saved > 0 ? Math.min(saved + 1, 6) : 1;
    }

    // ── API helpers ───────────────────────────────────────────────

    function apiUrl(path) {
        return `/api${path}`;
    }

    async function apiCall(fn) {
        isSaving.value  = true;
        saveError.value = null;
        try {
            return await fn();
        } catch (e) {
            const msg = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
                ?? e.response?.data?.message
                ?? 'Terjadi kesalahan. Coba lagi.';
            saveError.value = msg;
            throw e;
        } finally {
            isSaving.value = false;
        }
    }

    // ── Pending photo queue (deferred until saveStep1) ─────────────────
    const pendingPhotoFiles = reactive({});

    // ── Upload helpers ────────────────────────────────────────────

    function uploadPhotoField(file, field) {
        if (pendingPhotoFiles[field] && details[`${field}_url`]?.startsWith('blob:')) {
            URL.revokeObjectURL(details[`${field}_url`]);
        }
        pendingPhotoFiles[field] = file;
        details[`${field}_url`] = URL.createObjectURL(file);
    }

    async function deletePhotoField(field) {
        const urlField = `${field}_url`;
        // Revoke blob if it's a pending unsaved upload
        if (pendingPhotoFiles[field]) {
            if (details[urlField]?.startsWith('blob:')) URL.revokeObjectURL(details[urlField]);
            delete pendingPhotoFiles[field];
        }
        details[urlField] = null;
        // If already saved to server, delete from DB too
        if (invitationId.value) {
            await axios.delete(apiUrl(`/invitations/${invitationId.value}/details/photos/${field}`));
        }
    }

    async function uploadAudio(file) {
        if (!invitationId.value) throw new Error('Simpan informasi dasar terlebih dahulu.');

        const form = new FormData();
        form.append('type', 'upload');
        form.append('file', file);

        const res = await axios.post(
            apiUrl(`/invitations/${invitationId.value}/music`),
            form,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        return { url: res.data.data.file_url, name: res.data.data.title };
    }

    // ── Section toggle ────────────────────────────────────────────

    async function toggleSection(sectionKey) {
        if (!invitationId.value) {
            // Optimistic local toggle before first save
            if (sections[sectionKey]) {
                sections[sectionKey].is_enabled = !sections[sectionKey].is_enabled;
                sections[sectionKey].completion_status = sections[sectionKey].is_enabled ? 'empty' : 'disabled';
            }
            return;
        }
        try {
            const res = await axios.patch(
                apiUrl(`/invitations/${invitationId.value}/sections/${sectionKey}/toggle`)
            );
            if (sections[sectionKey]) {
                sections[sectionKey].is_enabled        = res.data.is_enabled;
                sections[sectionKey].completion_status = res.data.completion_status;
            }
        } catch {
            // Best effort — revert on error is optional
        }
    }

    // Save section data_json for a given section key
    async function saveSectionData(sectionKey) {
        if (!invitationId.value || !sections[sectionKey]) return;
        await axios.patch(
            apiUrl(`/invitations/${invitationId.value}/sections/${sectionKey}`),
            {
                data:       sections[sectionKey].data_json,
                status:     sections[sectionKey].completion_status,
                is_enabled: sections[sectionKey].is_enabled,
            }
        );
    }

    // ── Completion helpers ────────────────────────────────────────

    function computeSectionStatus(key) {
        const s = sections[key];
        if (!s) return 'empty';
        if (!s.is_enabled) return 'disabled';

        switch (key) {
            case 'cover':
                return 'complete'; // foto cover opsional, section selalu dianggap lengkap
            case 'konten_utama':
                return basic.title ? 'complete' : 'incomplete';
            case 'couple':
                return (details.groom_name && details.bride_name) ? 'complete' : 'incomplete';
            case 'quote':
                return s.data_json?.text ? 'complete' : 'incomplete';
            case 'events':
                return events.value.some(e => e.event_name && e.event_date && e.venue_name) ? 'complete' : 'incomplete';
            case 'countdown':
            case 'rsvp':
            case 'wishes':
                return 'complete';
            case 'live_streaming':
                return s.data_json?.url ? 'complete' : 'incomplete';
            case 'additional_info':
                return s.data_json?.text ? 'complete' : 'incomplete';
            case 'gallery':
                return galleries.value.length > 0 ? 'complete' : 'empty';
            case 'video':
                return s.data_json?.url ? 'complete' : 'incomplete';
            case 'love_story':
                return (s.data_json?.stories?.length > 0) ? 'complete' : 'empty';
            case 'gift':
                return (s.data_json?.accounts?.length > 0) ? 'complete' : 'incomplete';
            case 'music':
                return selectedMusic.value ? 'complete' : 'incomplete';
            case 'theme_settings':
                return (customConfig.primary_color && customConfig.font) ? 'complete' : 'incomplete';
            case 'slug_settings':
                return publish.slug ? 'complete' : 'incomplete';
            case 'password_protection':
                return publish.password?.length >= 4 ? 'complete' : 'incomplete';
            case 'preview_and_publish':
                return 'empty';
            default:
                return 'empty';
        }
    }

    // Sync computed statuses into the sections map (called before saving)
    function syncSectionStatuses() {
        for (const key of Object.keys(sections)) {
            sections[key].completion_status = computeSectionStatus(key);
        }
    }

    // ── Save methods ──────────────────────────────────────────────

    function resolvedTitle() {
        const g = details.groom_name?.trim();
        const b = details.bride_name?.trim();
        if (g && b) return `${g} & ${b}`;
        if (g)      return `Pernikahan ${g}`;
        if (b)      return `Pernikahan ${b}`;
        return 'Undangan Pernikahan';
    }

    async function saveStep1() {
        return apiCall(async () => {
            const title = resolvedTitle();
            if (!invitationId.value) {
                const res = await axios.post(apiUrl('/invitations'), {
                    template_id: template.id,
                    title,
                    event_type:  basic.event_type,
                });
                invitationId.value = res.data.data.id;
                publish.slug       = res.data.data.slug;
                window.history.replaceState(
                    null, '',
                    `/dashboard/invitations/${invitationId.value}/customize-v2`
                );
                await axios.put(apiUrl(`/invitations/${invitationId.value}`), { current_step: 1 });
            } else {
                await axios.put(apiUrl(`/invitations/${invitationId.value}`), {
                    title,
                    event_type:   basic.event_type,
                    current_step: Math.max(lastSavedStep.value, 1),
                });
            }

            await axios.post(apiUrl(`/invitations/${invitationId.value}/details`), sanitizedDetailsPayload());

            for (const [field, file] of Object.entries(pendingPhotoFiles)) {
                const form = new FormData();
                form.append(field, file);
                const res = await axios.post(
                    apiUrl(`/invitations/${invitationId.value}/details`),
                    form,
                    { headers: { 'Content-Type': 'multipart/form-data' } }
                );
                const urlField = `${field}_url`;
                if (res.data.data?.[urlField]) {
                    URL.revokeObjectURL(details[urlField]);
                    details[urlField] = res.data.data[urlField];
                }
                delete pendingPhotoFiles[field];
            }

            syncSectionStatuses();
            await saveSectionData('couple');

            lastSavedStep.value = Math.max(lastSavedStep.value, 1);
        });
    }

    async function saveStep2() {
        if (!invitationId.value) return;
        return apiCall(async () => {
            await axios.put(apiUrl(`/invitations/${invitationId.value}`), {
                current_step: Math.max(lastSavedStep.value, 2),
            });
            lastSavedStep.value = Math.max(lastSavedStep.value, 2);
        });
    }

    async function saveStep3(action = 'draft') {
        if (!invitationId.value) return;
        return apiCall(async () => {
            if (action === 'publish') {
                const payload = {
                    slug:                  publish.slug,
                    is_password_protected: publish.is_password_protected,
                    expires_at:            publish.expires_at || null,
                };
                if (publish.is_password_protected && publish.password) {
                    payload.password = publish.password;
                }
                const res = await axios.put(
                    apiUrl(`/invitations/${invitationId.value}/publish`),
                    payload
                );
                lastSavedStep.value = Math.max(lastSavedStep.value, 6);
                return res.data;
            } else {
                await axios.put(apiUrl(`/invitations/${invitationId.value}`), {
                    slug:                  publish.slug,
                    expires_at:            publish.expires_at || null,
                    is_password_protected: publish.is_password_protected,
                    ...(publish.password ? { password: publish.password } : {}),
                    current_step:          Math.max(lastSavedStep.value, 6),
                });
                lastSavedStep.value = Math.max(lastSavedStep.value, 6);
                return { status: 'draft' };
            }
        });
    }

    // ── Event helpers ─────────────────────────────────────────────

    function addEvent() {
        events.value.push({ ...defaultEvent(), sort_order: events.value.length });
    }

    async function removeEvent(index) {
        const ev = events.value[index];
        if (ev._serverId && invitationId.value) {
            try {
                await axios.delete(
                    apiUrl(`/invitations/${invitationId.value}/events/${ev._serverId}`)
                );
            } catch { /* best-effort */ }
        }
        events.value.splice(index, 1);
    }

    function moveEvent(from, to) {
        const arr  = events.value;
        const item = arr.splice(from, 1)[0];
        arr.splice(to, 0, item);
        arr.forEach((e, i) => (e.sort_order = i));
    }

    // ── Gallery helpers ───────────────────────────────────────────

    async function removeGallery(index) {
        const item = galleries.value[index];
        if (item._serverId && invitationId.value) {
            try {
                await axios.delete(
                    apiUrl(`/invitations/${invitationId.value}/galleries/${item._serverId}`)
                );
            } catch { /* best-effort */ }
        }
        galleries.value.splice(index, 1);
    }

    function moveGallery(from, to) {
        const arr  = galleries.value;
        const item = arr.splice(from, 1)[0];
        arr.splice(to, 0, item);
        arr.forEach((g, i) => (g.sort_order = i));
    }

    async function uploadGalleryFile(file) {
        if (!invitationId.value) throw new Error('Simpan informasi dasar terlebih dahulu.');

        const form = new FormData();
        form.append('image', file);

        const res = await axios.post(
            apiUrl(`/invitations/${invitationId.value}/galleries`),
            form,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        const data = res.data.data;
        galleries.value.push({
            _key:       Date.now() + Math.random(),
            _serverId:  data.id,
            image_url:  data.image_url,
            caption:    data.caption ?? '',
            sort_order: data.sort_order,
        });

        return data.image_url;
    }

    // ── Internal helpers ──────────────────────────────────────────

    function sanitizedDetailsPayload() {
        return {
            groom_name:          details.groom_name          || null,
            groom_nickname:      details.groom_nickname      || null,
            groom_instagram:     details.groom_instagram     || null,
            bride_name:          details.bride_name          || null,
            bride_nickname:      details.bride_nickname      || null,
            bride_instagram:     details.bride_instagram     || null,
            groom_parent_names:  details.groom_parent_names  || null,
            bride_parent_names:  details.bride_parent_names  || null,
            opening_text:        details.opening_text        || null,
            closing_text:        details.closing_text        || null,
        };
    }

    return {
        // State
        invitationId,
        currentStep,
        isSaving,
        saveError,
        lastSavedStep,

        // Step data
        basic,
        details,
        events,
        galleries,
        selectedMusic,
        customConfig,
        publish,
        sections,

        // Save methods
        saveStep1,
        saveStep2,
        saveStep3,

        // Helpers
        addEvent,
        removeEvent,
        moveEvent,
        removeGallery,
        moveGallery,
        uploadPhotoField,
        deletePhotoField,
        uploadAudio,
        uploadGalleryFile,
        toggleSection,
        saveSectionData,
        syncSectionStatuses,
    };
}
