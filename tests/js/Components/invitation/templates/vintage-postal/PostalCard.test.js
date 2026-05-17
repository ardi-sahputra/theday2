/**
 * Tests for PostalCard.vue
 * Run with: npx vitest run tests/js/
 * Requires: vitest + @vue/test-utils (npm install --save-dev vitest @vue/test-utils)
 */
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import PostalCard from '@/Components/invitation/templates/vintage-postal/PostalCard.vue'

describe('PostalCard', () => {
    it('mounts without errors', () => {
        const wrapper = mount(PostalCard, {
            props: { paper: 'cream', rotation: 0 },
            global: {
                stubs: {
                    PostalStamp: true,
                    PostalPostmark: true,
                    PostalWashiTape: true,
                },
            },
        })
        expect(wrapper.find('.vp-card').exists()).toBe(true)
    })

    it('applies correct paper background based on prop', () => {
        const wrapper = mount(PostalCard, {
            props: { paper: 'aged-2', rotation: 0 },
            global: {
                stubs: {
                    PostalStamp: true,
                    PostalPostmark: true,
                    PostalWashiTape: true,
                },
            },
        })
        const style = wrapper.find('.vp-card').attributes('style')
        expect(style).toContain('paper-aged-2.webp')
    })

    it('renders stamps array', () => {
        const wrapper = mount(PostalCard, {
            props: {
                paper: 'cream',
                rotation: 0,
                stamps: [
                    { theme: 'love', position: 'tl', rotate: -5 },
                    { theme: 'wedding', position: 'tr', rotate: 5 },
                ],
            },
            global: {
                stubs: {
                    PostalStamp: true,
                    PostalPostmark: true,
                    PostalWashiTape: true,
                },
            },
        })
        expect(wrapper.findAllComponents({ name: 'PostalStamp' })).toHaveLength(2)
    })

    it('renders postmark when postmark prop provided', () => {
        const wrapper = mount(PostalCard, {
            props: {
                paper: 'cream',
                rotation: 0,
                postmark: { variant: 'circular', date: '2025-01-01', position: 'tr' },
            },
            global: {
                stubs: {
                    PostalStamp: true,
                    PostalPostmark: true,
                    PostalWashiTape: true,
                },
            },
        })
        expect(wrapper.findComponent({ name: 'PostalPostmark' }).exists()).toBe(true)
    })
})
