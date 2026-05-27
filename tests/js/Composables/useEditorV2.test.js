// tests/js/Composables/useEditorV2.test.js
import { describe, it, expect, vi } from 'vitest'
import { useEditorV2 } from '@/Composables/useEditorV2'

const invitation = {
  id: 'inv-1',
  slug: 'ayu-rizki',
  template_id: 'tpl-1',
  template_slug: 'botanical',
  config: {},
  music: { title: 'Mariposa', file_url: '/m.mp3' },
}

function fakeHttp() {
  return {
    patch: vi.fn().mockResolvedValue({ data: { success: true } }),
    post:  vi.fn().mockResolvedValue({ data: { data: { title: 'Perfect', file_url: '/p.mp3' } } }),
  }
}

describe('useEditorV2', () => {
  it('initialises music on (track present, no flag)', () => {
    const ed = useEditorV2(invitation, { http: fakeHttp() })
    expect(ed.state.musicEnabled).toBe(true)
    expect(ed.state.template_slug).toBe('botanical')
  })

  it('setMusicEnabled patches config and updates state', async () => {
    const http = fakeHttp()
    const ed = useEditorV2(invitation, { http })
    await ed.setMusicEnabled(false)
    expect(ed.state.musicEnabled).toBe(false)
    expect(http.patch).toHaveBeenCalledWith(
      '/dashboard/invitations/inv-1/config',
      { custom_config: { music_enabled: false } },
    )
    expect(ed.saveStatus.value).toBe('saved')
  })

  it('selectPresetMusic posts and stores returned track', async () => {
    const http = fakeHttp()
    const ed = useEditorV2(invitation, { http })
    await ed.selectPresetMusic({ title: 'Perfect', file_url: '/p.mp3' })
    expect(http.post).toHaveBeenCalledWith(
      '/api/invitations/inv-1/music',
      { type: 'default', title: 'Perfect', file_url: '/p.mp3' },
    )
    expect(ed.state.music).toEqual({ title: 'Perfect', file_url: '/p.mp3' })
  })

  it('applyTemplate updates template fields and preview slug', () => {
    const ed = useEditorV2(invitation, { http: fakeHttp() })
    ed.applyTemplate({ id: 'tpl-2', slug: 'netflix', name: 'Netflix', category: { name: 'Pop' }, thumbnail_url: '/t.png' })
    expect(ed.state.template_id).toBe('tpl-2')
    expect(ed.state.template_slug).toBe('netflix')
    expect(ed.previewInvitation.value.template_slug).toBe('netflix')
  })
})
