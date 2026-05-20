// resources/js/Composables/useEditorV2.js
import { reactive, ref, computed } from 'vue'
import axios from 'axios'
import { isMusicEnabled } from '@/utils/invitationMusic'

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

  const saveStatus = ref('saved') // 'saved' | 'saving' | 'error'

  async function run(fn) {
    saveStatus.value = 'saving'
    try { await fn(); saveStatus.value = 'saved' }
    catch (e) { saveStatus.value = 'error'; throw e }
  }

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

  // Data shape fed to the live template component in the preview.
  const previewInvitation = computed(() => ({
    ...invitation,
    template_slug: state.template_slug,
    music: state.musicEnabled ? state.music : null,
    config: { ...invitation.config, music_enabled: state.musicEnabled },
  }))

  return { state, saveStatus, setMusicEnabled, selectPresetMusic, uploadMusic, applyTemplate, previewInvitation }
}
