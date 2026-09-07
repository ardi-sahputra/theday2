// tests/js/Components/editor/v2/ContentPanelSectionsV2.test.js
//
// The Konten tab owns every content form. These cover the section cards added
// alongside the original Pasangan/Foto/Galeri/Tanggal ones.
import { describe, it, expect, vi } from 'vitest'
import { reactive, nextTick } from 'vue'
import { mount } from '@vue/test-utils'
import ContentPanelV2 from '@/Components/editor/v2/panels/ContentPanelV2.vue'

const details = () => ({
  groom_name: 'Rizki', bride_name: 'Ayu', groom_nickname: '', bride_nickname: '',
  groom_instagram: '', bride_instagram: '', groom_parent_names: '', bride_parent_names: '',
  groom_photo_url: null, bride_photo_url: null,
})

const mountPanel = (sections = {}, caps = {}, onUploadImage = null) => {
  const sectionsData = reactive(sections)
  const w = mount(ContentPanelV2, {
    props: {
      details: reactive(details()),
      sectionsData,
      events: [],
      galleries: [],
      caps: { loveStory: true, gift: true, liveStreaming: true, video: true, additionalInfo: true, ...caps },
      onUploadImage,
    },
    global: { stubs: { GalleryPanelV2: true, DateTimeField: true } },
  })
  return { w, sectionsData }
}

const openCard = async (w, name) => {
  const head = w.findAll('.acc-head').find(h => h.text().includes(name))
  await head.trigger('click')
  return head
}

describe('ContentPanelV2 — section cards', () => {
  it('lists the section cards the template supports', () => {
    const { w } = mountPanel()
    const text = w.text()
    expect(text).toContain('Kisah Kami')
    expect(text).toContain('Hadiah')
    expect(text).toContain('Live Streaming')
    expect(text).toContain('Info Tambahan')
  })

  it('hides cards the template cannot render', () => {
    const { w } = mountPanel({}, { loveStory: false, liveStreaming: false, video: false, additionalInfo: false })
    // Match card titles, not body copy — the quote help text also says "Kisah Kami".
    const titles = w.findAll('.acc-title').map(t => t.text())
    expect(titles).not.toContain('Kisah Kami')
    expect(titles).not.toContain('Live Streaming')
    expect(titles).toContain('Hadiah')
  })

  it('adds a moment under the `stories` key templates read', async () => {
    const { w, sectionsData } = mountPanel()
    await openCard(w, 'Kisah Kami')
    await w.findAll('.add').find(b => b.text().includes('Momen')).trigger('click')

    expect(sectionsData.love_story.data.stories).toHaveLength(1)
    expect(sectionsData.love_story.data.stories[0]).toEqual({ date: '', title: '', description: '', photo_url: '' })
    expect(w.emitted('save-section').at(-1)).toEqual(['love_story'])
  })

  it('saves moment text as it is typed', async () => {
    const { w, sectionsData } = mountPanel({
      love_story: { data: { stories: [{ date: '', title: '', description: '', photo_url: '' }] }, is_enabled: true },
    })
    await openCard(w, 'Kisah Kami')
    await w.find('.item-head').trigger('click')

    const title = w.findAll('.input').find(i => i.attributes('placeholder')?.includes('Pertama Bertemu'))
    await title.setValue('Lamaran')

    expect(sectionsData.love_story.data.stories[0].title).toBe('Lamaran')
    expect(w.emitted('save-section').at(-1)).toEqual(['love_story'])
  })

  it('reorders and removes moments', async () => {
    const { w, sectionsData } = mountPanel({
      love_story: { data: { stories: [{ title: 'A' }, { title: 'B' }] }, is_enabled: true },
    })
    await openCard(w, 'Kisah Kami')

    await w.findAll('.item')[0].findAll('.mini')[1].trigger('click')
    expect(sectionsData.love_story.data.stories.map(s => s.title)).toEqual(['B', 'A'])

    await w.findAll('.item')[0].findAll('.mini').at(-1).trigger('click')
    expect(sectionsData.love_story.data.stories.map(s => s.title)).toEqual(['A'])
  })

  it('stores an uploaded moment photo on the story', async () => {
    const onUploadImage = vi.fn().mockResolvedValue('/uploads/story.jpg')
    const { w, sectionsData } = mountPanel(
      { love_story: { data: { stories: [{ title: 'A', photo_url: '' }] }, is_enabled: true } },
      {},
      onUploadImage,
    )
    await openCard(w, 'Kisah Kami')
    await w.find('.item-head').trigger('click')

    const input = w.find('.upload input[type="file"]')
    Object.defineProperty(input.element, 'files', { value: [new File(['x'], 'a.jpg', { type: 'image/jpeg' })] })
    await input.trigger('change')
    await nextTick(); await nextTick()

    expect(onUploadImage).toHaveBeenCalled()
    expect(sectionsData.love_story.data.stories[0].photo_url).toBe('/uploads/story.jpg')
  })

  it('adds a gift account under the `accounts` key templates read', async () => {
    const { w, sectionsData } = mountPanel()
    await openCard(w, 'Hadiah')
    await w.findAll('.add').find(b => b.text().includes('Rekening')).trigger('click')

    expect(sectionsData.gift.data.accounts).toEqual([{ bank: '', account_number: '', account_name: '' }])
  })

  it('keeps live streaming as platform + url', async () => {
    const { w, sectionsData } = mountPanel()
    await openCard(w, 'Live Streaming')

    const url = w.findAll('.input').find(i => i.attributes('placeholder')?.includes('youtube.com/live'))
    await url.setValue('https://youtube.com/live/abc')

    expect(sectionsData.live_streaming.data).toEqual({ platform: 'youtube', url: 'https://youtube.com/live/abc' })
    expect(w.emitted('save-section').at(-1)).toEqual(['live_streaming'])
  })

  it('summarises filled sections in the card header', () => {
    const { w } = mountPanel({
      love_story: { data: { stories: [{ title: 'A' }, { title: 'B' }] }, is_enabled: true },
      gift: { data: { accounts: [] }, is_enabled: true },
    })
    const heads = w.findAll('.acc-head')
    expect(heads.find(h => h.text().includes('Kisah Kami')).text()).toContain('2 momen')
    expect(heads.find(h => h.text().includes('Hadiah')).text()).toContain('Belum ada rekening')
  })

  it('marks a disabled section instead of summarising it, and toggles it in place', async () => {
    const { w } = mountPanel({ love_story: { data: { stories: [{ title: 'A' }] }, is_enabled: false } })
    const head = w.findAll('.acc-head').find(h => h.text().includes('Kisah Kami'))
    expect(head.text()).toContain('Disembunyikan')

    await head.find('.toggle-sw').trigger('click')
    expect(w.emitted('toggle-section').at(-1)).toEqual(['love_story'])
  })
})
