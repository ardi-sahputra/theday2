// tests/js/Components/editor/v2/MobilePreviewOverlay.test.js
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

// Mock the heavy PreviewPaneV2 (pulls in the template registry, which references
// runtime asset URLs that Vite's asset transform can't resolve under jsdom).
// The overlay only needs a named child it can render/find — exactly what the
// plan stubs at render time.
vi.mock('@/Components/editor/v2/PreviewPaneV2.vue', () => ({
  default: { name: 'PreviewPaneV2', render: () => null },
}))

import MobilePreviewOverlay from '@/Components/editor/v2/MobilePreviewOverlay.vue'

const baseProps = (open) => ({
  open,
  previewInvitation: { slug: 'ayu-rizki', template_slug: 'botanical', config: {} },
  slug: 'botanical',
  stats: null,
})
const global = { stubs: { PreviewPaneV2: true, teleport: true } }

describe('MobilePreviewOverlay', () => {
  it('renders nothing when closed', () => {
    const w = mount(MobilePreviewOverlay, { props: baseProps(false), global })
    expect(w.find('[data-test="preview-overlay"]').exists()).toBe(false)
  })
  it('renders overlay + preview when open', () => {
    const w = mount(MobilePreviewOverlay, { props: baseProps(true), global })
    expect(w.find('[data-test="preview-overlay"]').exists()).toBe(true)
    expect(w.findComponent({ name: 'PreviewPaneV2' }).exists()).toBe(true)
  })
  it('emits close from back button and from "Kembali ke Editor"', async () => {
    const w = mount(MobilePreviewOverlay, { props: baseProps(true), global })
    await w.get('[data-test="overlay-back"]').trigger('click')
    await w.get('[data-test="overlay-return"]').trigger('click')
    expect(w.emitted('close')).toHaveLength(2)
  })
})
