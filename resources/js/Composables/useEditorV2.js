// resources/js/Composables/useEditorV2.js
import { reactive, ref, computed } from 'vue'
import axios from 'axios'
import { isMusicEnabled } from '@/utils/invitationMusic'
import { compressImage } from '@/utils/imageCompress'

/**
 * v2 editor state + persistence. Reuses existing endpoints only.
 * @param {object} invitation  Inertia `invitation` prop
 * @param {{ http?: object }} [opts]  inject an axios-like client (tests)
 */
export function useEditorV2(invitation, { http = axios } = {}) {
  const id = invitation.id

  const state = reactive({
    template_id:       invitation.template_id ?? null,
    template_slug:     invitation.template_slug ?? null,
    template_name:     invitation.template_name ?? null,
    template_category: invitation.template_category ?? null,
    template_thumb:    invitation.template_thumbnail_url ?? null,
    music:             invitation.music ?? null,
    musicEnabled:      isMusicEnabled(invitation),
  })

  // ── Content state (Konten / Acara / Bagian / Bagikan tabs) ────────────────
  const details = reactive({
    groom_name:         invitation.details?.groom_name         ?? '',
    bride_name:         invitation.details?.bride_name         ?? '',
    groom_nickname:     invitation.details?.groom_nickname     ?? '',
    bride_nickname:     invitation.details?.bride_nickname     ?? '',
    groom_instagram:    invitation.details?.groom_instagram    ?? '',
    bride_instagram:    invitation.details?.bride_instagram    ?? '',
    groom_parent_names: invitation.details?.groom_parent_names ?? '',
    bride_parent_names: invitation.details?.bride_parent_names ?? '',
    groom_photo_url:    invitation.details?.groom_photo_url    ?? null,
    bride_photo_url:    invitation.details?.bride_photo_url    ?? null,
  })

  const events = ref([...(invitation.events ?? [])])
  const galleries = ref([...(invitation.galleries ?? [])])
  const slug = ref(invitation.slug ?? '')

  // Per-section { data, is_enabled }. Seed quote so the Konten tab can bind it.
  const sectionsData = reactive(JSON.parse(JSON.stringify({
    quote: { data: { text: '', source: '' }, is_enabled: true },
    ...(invitation.sections ?? {}),
  })))

  // Editable copy of custom_config (WA share template lives here).
  const config = reactive({ ...(invitation.config ?? {}) })

  const saveStatus = ref('saved') // 'saved' | 'saving' | 'error'

  async function run(fn) {
    saveStatus.value = 'saving'
    try { await fn(); saveStatus.value = 'saved' }
    catch (e) { saveStatus.value = 'error'; throw e }
  }

  // ── Music / template (existing) ───────────────────────────────────────────
  async function setMusicEnabled(val) {
    state.musicEnabled = val
    await run(() => http.patch(`/dashboard/invitations/${id}/config`, {
      custom_config: { music_enabled: val },
    }))
  }

  async function selectPresetMusic(preset) {
    await run(async () => {
      const res = await http.post(`/api/invitations/${id}/music`, {
        type: 'default', title: preset.title, file_url: preset.file_url,
      })
      state.music = { title: res.data.data.title, file_url: res.data.data.file_url }
    })
  }

  async function uploadMusic(file) {
    await run(async () => {
      const fd = new FormData()
      fd.append('type', 'upload')
      fd.append('file', file)
      const res = await http.post(`/api/invitations/${id}/music`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      state.music = { title: res.data.data.title, file_url: res.data.data.file_url }
    })
  }

  function applyTemplate(tpl) {
    state.template_id       = tpl.id
    state.template_slug     = tpl.slug
    state.template_name     = tpl.name
    state.template_category = tpl.category?.name ?? null
    state.template_thumb    = tpl.thumbnail_url ?? null
  }

  // ── Couple details (POST /details, accepts text + photo files) ────────────
  async function saveDetails() {
    await run(async () => {
      const fd = new FormData()
      const fields = ['groom_name', 'bride_name', 'groom_nickname', 'bride_nickname', 'groom_instagram', 'bride_instagram', 'groom_parent_names', 'bride_parent_names']
      fields.forEach(f => { if (details[f] != null) fd.append(f, details[f]) })
      const res = await http.post(`/api/invitations/${id}/details`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      Object.assign(details, res.data.data ?? {})
    })
  }

  async function uploadCouplePhoto(side, file) {
    await run(async () => {
      const compressed = await compressImage(file)
      const fd = new FormData()
      fd.append(`${side}_photo`, compressed) // 'groom' | 'bride'
      const res = await http.post(`/api/invitations/${id}/details`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      Object.assign(details, res.data.data ?? {})
    })
  }

  // ── Events (REST CRUD) ────────────────────────────────────────────────────
  function eventPayload(e) {
    return {
      event_name:    e.event_name    || '',
      event_date:    e.event_date    || null,
      start_time:    e.start_time    || null,
      end_time:      e.end_time      || null,
      venue_name:    e.venue_name    || '',
      venue_address: e.venue_address || null,
      maps_url:      e.maps_url       || null,
    }
  }

  async function addEvent() {
    await run(async () => {
      // event_name / event_date / venue_name are required by StoreEventRequest,
      // so seed sensible defaults; the user edits them in the card afterwards.
      const today = new Date().toISOString().slice(0, 10)
      const res = await http.post(`/api/invitations/${id}/events`, eventPayload({
        event_name: 'Acara Baru', venue_name: 'Lokasi acara', event_date: today,
      }))
      events.value = [...events.value, res.data.data]
    })
  }

  async function saveEvent(ev) {
    await run(async () => {
      await http.put(`/api/invitations/${id}/events/${ev.id}`, eventPayload(ev))
      events.value = events.value.map(e => (e.id === ev.id ? { ...ev } : e))
    })
  }

  async function deleteEvent(ev) {
    await run(async () => {
      await http.delete(`/api/invitations/${id}/events/${ev.id}`)
      events.value = events.value.filter(e => e.id !== ev.id)
    })
  }

  // ── Quote (lives in the `quote` section) ──────────────────────────────────
  async function saveQuote() {
    const data = sectionsData.quote?.data ?? {}
    await run(() => http.patch(`/api/invitations/${id}/sections/quote`, {
      data,
      status: data.text?.trim() ? 'complete' : 'empty',
      is_enabled: true,
    }))
  }

  // ── Section toggle (Bagian tab) ───────────────────────────────────────────
  async function toggleSection(key) {
    await run(async () => {
      const res = await http.patch(`/api/invitations/${id}/sections/${key}/toggle`)
      if (!sectionsData[key]) sectionsData[key] = { data: {}, is_enabled: false }
      sectionsData[key].is_enabled = res.data.is_enabled
    })
  }

  // ── Share / custom_config (WA template, etc.) ─────────────────────────────
  async function saveConfig(patch) {
    Object.assign(config, patch)
    await run(() => http.put(`/api/invitations/${id}`, { custom_config: patch }))
  }

  // ── Debounced autosave helper for free-text fields ────────────────────────
  const timers = {}
  function debounce(key, fn, ms = 1500) {
    clearTimeout(timers[key])
    timers[key] = setTimeout(fn, ms)
  }

  // ── Gallery (REST CRUD; photos compressed client-side before upload) ──────
  async function addGalleryPhoto(file) {
    return run(async () => {
      const compressed = await compressImage(file, { maxEdge: 1600, quality: 0.82 })
      const fd = new FormData()
      fd.append('image', compressed)
      const res = await http.post(`/api/invitations/${id}/galleries`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      galleries.value = [...galleries.value, res.data.data]
    })
  }
  async function deleteGalleryPhoto(g) {
    return run(async () => {
      await http.delete(`/api/invitations/${id}/galleries/${g.id}`)
      galleries.value = galleries.value.filter(x => x.id !== g.id)
    })
  }
  async function reorderGalleries(ids) {
    galleries.value = ids.map(i => galleries.value.find(g => g.id === i)).filter(Boolean)
    return run(() => http.put(`/api/invitations/${id}/galleries/reorder`, { ids }))
  }

  // ── Custom slug (validated + old slug kept as redirect alias) ─────────────
  async function updateSlug(newSlug) {
    const res = await http.put(`/api/invitations/${id}/slug`, { slug: newSlug })
    slug.value = res.data.slug
    return res.data.slug
  }

  // Data shape fed to the live template component in the preview.
  const previewInvitation = computed(() => ({
    ...invitation,
    slug: slug.value,
    template_slug: state.template_slug,
    music: state.musicEnabled ? state.music : null,
    config: { ...invitation.config, ...config, music_enabled: state.musicEnabled },
    details: { ...invitation.details, ...details },
    events: events.value,
    galleries: galleries.value,
    sections: { ...invitation.sections, ...sectionsData },
  }))

  return {
    // template + music
    state, saveStatus, setMusicEnabled, selectPresetMusic, uploadMusic, applyTemplate,
    // content
    details, events, galleries, slug, sectionsData, config,
    updateSlug,
    saveDetails, uploadCouplePhoto,
    addEvent, saveEvent, deleteEvent,
    addGalleryPhoto, deleteGalleryPhoto, reorderGalleries,
    saveQuote, toggleSection, saveConfig,
    debounce,
    // preview
    previewInvitation,
  }
}
