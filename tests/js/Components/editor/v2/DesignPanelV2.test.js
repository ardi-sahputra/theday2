// tests/js/Components/editor/v2/DesignPanelV2.test.js
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import DesignPanelV2 from '@/Components/editor/v2/panels/DesignPanelV2.vue'

const baseProps = () => ({
  state: { template_slug: 'botanical', template_id: 'tpl-1', template_name: 'Botanical', template_category: 'Botanikal', template_thumb: null, music: { title: 'Mariposa', file_url: '/m.mp3' }, musicEnabled: true },
  templates: [{ id: 'tpl-1', name: 'Botanical', slug: 'botanical', tier: 'free', category: { name: 'Botanikal' } }],
  defaultMusic: [{ id: 'perfect', title: 'Perfect — Ed Sheeran', file_url: '/p.mp3' }],
  canUsePremium: true,
  invitationId: 'inv-1',
  invitationStatus: 'draft',
})

const stubs = { TemplatePicker: true }

describe('DesignPanelV2', () => {
  it('shows current template name + the two sections only', () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    expect(w.text()).toContain('Botanical')
    expect(w.text()).toContain('Template')
    expect(w.text()).toContain('Musik Latar')
    expect(w.text()).not.toContain('Palet Warna')
    expect(w.text()).not.toContain('Tipografi')
  })

  it('emits set-music-enabled when toggle clicked', async () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    await w.get('[data-test="music-toggle"]').trigger('click')
    expect(w.emitted('set-music-enabled')[0]).toEqual([false])
  })

  it('opens the template picker when Ganti clicked', async () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    expect(w.findComponent({ name: 'TemplatePicker' }).exists()).toBe(false)
    await w.get('[data-test="change-template"]').trigger('click')
    expect(w.findComponent({ name: 'TemplatePicker' }).exists()).toBe(true)
  })

  it('emits select-preset when a preset is chosen', async () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    await w.get('[data-test="preset-perfect"]').trigger('click')
    expect(w.emitted('select-preset')[0][0]).toMatchObject({ id: 'perfect' })
  })
})
