/**
 * Tests for PostalRoute.vue
 * Key requirements:
 * - Unknown city in vp_travel_cities MUST NOT crash (cluster fallback)
 * - NO geocoding API calls ever
 */
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import PostalRoute from '@/Components/invitation/templates/vintage-postal/PostalRoute.vue'

describe('PostalRoute', () => {
    it('mounts without errors with empty cities', () => {
        const wrapper = mount(PostalRoute, {
            props: { cities: [] },
            global: { stubs: { PostalStamp: true } },
        })
        expect(wrapper.find('.vp-route').exists()).toBe(true)
    })

    it('renders correct number of pins for known cities', () => {
        const wrapper = mount(PostalRoute, {
            props: { cities: ['JAKARTA', 'BALI', 'PARIS'] },
            global: { stubs: { PostalStamp: true } },
        })
        const pins = wrapper.findAll('.vp-route-pin')
        expect(pins).toHaveLength(3)
    })

    it('does NOT crash for unknown city (cluster fallback)', () => {
        // This test verifies spec §14 VP-2: unknown cities must never throw
        expect(() => {
            mount(PostalRoute, {
                props: { cities: ['JAKARTA', 'SHANGRILA', 'PARIS'] },
                global: { stubs: { PostalStamp: true } },
            })
        }).not.toThrow()
    })

    it('renders 3 pins even with an unknown city', () => {
        const wrapper = mount(PostalRoute, {
            props: { cities: ['JAKARTA', 'SHANGRILA', 'PARIS'] },
            global: { stubs: { PostalStamp: true } },
        })
        expect(wrapper.findAll('.vp-route-pin')).toHaveLength(3)
    })

    it('unknown city pin label still shows the city name', () => {
        const wrapper = mount(PostalRoute, {
            props: { cities: ['SHANGRILA'] },
            global: { stubs: { PostalStamp: true } },
        })
        expect(wrapper.find('.vp-route-pin-label').text()).toBe('SHANGRILA')
    })

    it('renders empty polyline path when no cities provided', () => {
        const wrapper = mount(PostalRoute, {
            props: { cities: [] },
            global: { stubs: { PostalStamp: true } },
        })
        const path = wrapper.find('.vp-route-line')
        expect(path.attributes('d')).toBe('')
    })

    it('generates non-empty polyline path for multiple cities', () => {
        const wrapper = mount(PostalRoute, {
            props: { cities: ['JAKARTA', 'TOKYO'] },
            global: { stubs: { PostalStamp: true } },
        })
        const path = wrapper.find('.vp-route-line')
        expect(path.attributes('d')).toMatch(/M \d+ \d+ L \d+ \d+/)
    })
})
