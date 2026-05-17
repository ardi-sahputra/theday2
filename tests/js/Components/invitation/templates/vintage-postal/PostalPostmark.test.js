/**
 * Tests for PostalPostmark.vue
 */
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import PostalPostmark from '@/Components/invitation/templates/vintage-postal/PostalPostmark.vue'

describe('PostalPostmark', () => {
    it('mounts without errors', () => {
        const wrapper = mount(PostalPostmark, { props: { variant: 'circular' } })
        expect(wrapper.find('.vp-postmark').exists()).toBe(true)
    })

    it('uses correct SVG for variant', () => {
        const wrapper = mount(PostalPostmark, { props: { variant: 'par-avion' } })
        const img = wrapper.find('.vp-postmark-stamp')
        expect(img.attributes('src')).toContain('postmark-par-avion.svg')
    })

    it('falls back to circular for unknown variant', () => {
        const wrapper = mount(PostalPostmark, { props: { variant: 'unknown-variant' } })
        const img = wrapper.find('.vp-postmark-stamp')
        expect(img.attributes('src')).toContain('postmark-circular.svg')
    })

    it('formats date correctly', () => {
        const wrapper = mount(PostalPostmark, {
            props: { variant: 'circular', date: '2025-06-15' },
        })
        expect(wrapper.find('.vp-postmark-date').text()).toBe('15 JUN 2025')
    })

    it('has role=img for accessibility', () => {
        const wrapper = mount(PostalPostmark, { props: { variant: 'posted' } })
        expect(wrapper.find('.vp-postmark').attributes('role')).toBe('img')
    })
})
