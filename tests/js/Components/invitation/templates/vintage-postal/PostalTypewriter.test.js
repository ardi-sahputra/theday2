/**
 * Tests for PostalTypewriter.vue
 * Key requirement: must respect prefers-reduced-motion (instant render).
 */
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import PostalTypewriter from '@/Components/invitation/templates/vintage-postal/PostalTypewriter.vue'

describe('PostalTypewriter', () => {
    it('mounts and renders all characters', () => {
        const wrapper = mount(PostalTypewriter, {
            props: { text: 'Hello', mode: 'typing' },
        })
        const chars = wrapper.findAll('.vp-typewriter-char')
        expect(chars).toHaveLength(5)
    })

    it('shows skip button when skippable=true', () => {
        const wrapper = mount(PostalTypewriter, {
            props: { text: 'Hello', skippable: true, mode: 'typing' },
        })
        expect(wrapper.find('.vp-typewriter-skip').exists()).toBe(true)
    })

    it('hides skip button when skippable=false', () => {
        const wrapper = mount(PostalTypewriter, {
            props: { text: 'Hello', skippable: false, mode: 'typing' },
        })
        expect(wrapper.find('.vp-typewriter-skip').exists()).toBe(false)
    })

    it('skips to final state immediately when prefers-reduced-motion is reduce', async () => {
        // Mock matchMedia to return reduce
        const originalMatchMedia = window.matchMedia
        window.matchMedia = vi.fn().mockImplementation(query => ({
            matches: query === '(prefers-reduced-motion: reduce)',
            media: query,
            addListener: vi.fn(),
            removeListener: vi.fn(),
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        }))

        const wrapper = mount(PostalTypewriter, {
            props: { text: 'Hello World', mode: 'typing', skippable: true },
        })

        await wrapper.vm.$nextTick()

        // After mounting with reduced motion, all chars should be visible
        expect(wrapper.find('.vp-typewriter--skipped').exists()).toBe(true)
        // Skip button should be hidden (already skipped)
        expect(wrapper.find('.vp-typewriter-skip').exists()).toBe(false)

        window.matchMedia = originalMatchMedia
    })

    it('has sr-only text for accessibility', () => {
        const wrapper = mount(PostalTypewriter, {
            props: { text: 'Hello', mode: 'typing' },
        })
        expect(wrapper.find('.sr-only').text()).toBe('Hello')
    })

    it('renders handwriting mode correctly', () => {
        const wrapper = mount(PostalTypewriter, {
            props: { text: 'My Name', mode: 'handwriting' },
        })
        expect(wrapper.find('.vp-typewriter-handwriting').exists()).toBe(true)
        expect(wrapper.find('.vp-typewriter-handwriting').text()).toBe('My Name')
    })
})
