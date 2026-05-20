// resources/js/utils/invitationMusic.js

/**
 * Whether the invitation should play background music.
 * Default ON when a track exists; only OFF when config.music_enabled === false.
 * @param {{ music?: { file_url?: string }, config?: { music_enabled?: boolean } }} invitation
 * @returns {boolean}
 */
export function isMusicEnabled(invitation) {
  const hasTrack = !!invitation?.music?.file_url
  if (!hasTrack) return false
  return invitation?.config?.music_enabled !== false
}
