// tests/js/Components/editor/v2/SectionsPanelV2.test.js
//
// The Bagian tab only switches sections on and off — content lives on Konten.
import { describe, it, expect } from 'vitest'
import { reactive } from 'vue'
import { mount } from '@vue/test-utils'
import SectionsPanelV2 from '@/Components/editor/v2/panels/SectionsPanelV2.vue'

const mountPanel = (sections = {}, caps = {}) =>
  mount(SectionsPanelV2, { props: { sectionsData: reactive(sections), caps } })

describe('SectionsPanelV2', () => {
  it('lists sections the template supports and hides the rest', () => {
    const w = mountPanel({}, { liveStreaming: false, video: false, additionalInfo: false })
    expect(w.text()).toContain('Kisah Kami')
    expect(w.text()).toContain('Hadiah')
    expect(w.text()).not.toContain('Live Streaming')
    expect(w.text()).not.toContain('Video')
  })

  it('shows the opt-in sections when the template renders them', () => {
    const w = mountPanel({}, { liveStreaming: true, video: true, additionalInfo: true })
    expect(w.text()).toContain('Live Streaming')
    expect(w.text()).toContain('Video')
    expect(w.text()).toContain('Info Tambahan')
  })

  it('emits toggle-section for the clicked row', async () => {
    const w = mountPanel()
    await w.findAll('.toggle-sw')[0].trigger('click')
    expect(w.emitted('toggle-section')[0]).toEqual(['love_story'])
  })

  it('treats a section with no record as enabled', () => {
    const w = mountPanel({ gift: { data: {}, is_enabled: false } })
    const switches = w.findAll('.toggle-sw')
    expect(switches[0].classes()).toContain('on')            // love_story — no record
    expect(switches[3].classes()).not.toContain('on')        // gift — explicitly off
  })

  it('offers no content inputs — those live on the Konten tab', () => {
    const w = mountPanel()
    expect(w.findAll('input:not([role])').length).toBe(0)
    expect(w.findAll('textarea').length).toBe(0)
    expect(w.text()).toContain('Isinya diatur di tab Konten')
  })
})
