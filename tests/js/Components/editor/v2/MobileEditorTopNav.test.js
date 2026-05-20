// tests/js/Components/editor/v2/MobileEditorTopNav.test.js
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MobileEditorTopNav from '@/Components/editor/v2/MobileEditorTopNav.vue'

const props = { title: 'Editor Undangan', subtitle: 'Live · tersimpan' }

describe('MobileEditorTopNav', () => {
  it('renders title and subtitle', () => {
    const w = mount(MobileEditorTopNav, { props })
    expect(w.text()).toContain('Editor Undangan')
    expect(w.text()).toContain('Live · tersimpan')
  })
  it('emits back / preview / publish on the respective buttons', async () => {
    const w = mount(MobileEditorTopNav, { props })
    await w.get('[data-test="topnav-back"]').trigger('click')
    await w.get('[data-test="topnav-preview"]').trigger('click')
    await w.get('[data-test="topnav-publish"]').trigger('click')
    expect(w.emitted('back')).toHaveLength(1)
    expect(w.emitted('preview')).toHaveLength(1)
    expect(w.emitted('publish')).toHaveLength(1)
  })
})
