/**
 * Tests for PostalStamp.vue
 */
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import PostalStamp from '@/Components/invitation/templates/vintage-postal/PostalStamp.vue'

describe('PostalStamp', () => {
    it('mounts without errors', () => {
        const wrapper = mount(PostalStamp, { props: { theme: 'love' } })
        expect(wrapper.find('.vp-stamp').exists()).toBe(true)
    })

    it('uses city asset when city is known', () => {
        const wrapper = mount(PostalStamp, { props: { city: 'Paris' } })
        const img = wrapper.find('img')
        expect(img.attributes('src')).toContain('stamp-paris.png')
    })

    it('falls back to stamp-wedding.png for unknown city', () => {
        const wrapper = mount(PostalStamp, { props: { city: 'Shangri-La' } })
        const img = wrapper.find('img')
        expect(img.attributes('src')).toContain('stamp-wedding.png')
    })

    it('uses theme asset when theme is valid', () => {
        const wrapper = mount(PostalStamp, { props: { theme: 'forever' } })
        const img = wrapper.find('img')
        expect(img.attributes('src')).toContain('stamp-forever.png')
    })

    it('applies size class correctly', () => {
        const wrapper = mount(PostalStamp, { props: { theme: 'love', size: 'tiny' } })
        expect(wrapper.find('.vp-stamp--tiny').exists()).toBe(true)
    })

    it('has correct alt text for city', () => {
        const wrapper = mount(PostalStamp, { props: { city: 'TOKYO' } })
        expect(wrapper.find('img').attributes('alt')).toBe('Prangko TOKYO')
    })
})
