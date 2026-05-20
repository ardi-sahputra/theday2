// tests/js/utils/invitationMusic.test.js
import { describe, it, expect } from 'vitest'
import { isMusicEnabled } from '@/utils/invitationMusic'

describe('isMusicEnabled', () => {
  it('false when no track', () => {
    expect(isMusicEnabled({ music: null, config: {} })).toBe(false)
    expect(isMusicEnabled({})).toBe(false)
  })
  it('true when track present and flag absent (default on)', () => {
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' }, config: {} })).toBe(true)
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' } })).toBe(true)
  })
  it('false when track present but flag explicitly false', () => {
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' }, config: { music_enabled: false } })).toBe(false)
  })
  it('true when track present and flag explicitly true', () => {
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' }, config: { music_enabled: true } })).toBe(true)
  })
})
