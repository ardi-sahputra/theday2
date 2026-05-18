// resources/js/Composables/useInvitationTemplate.js
import { ref, computed, onMounted, onUnmounted } from 'vue'

export function useInvitationTemplate(props, defaults = {}) {

    // ── Config / theme ────────────────────────────────────────────────────
    const cfg = computed(() => props.invitation.config ?? {})

    const primary      = computed(() => cfg.value.primary_color       ?? '#8B6914')
    const primaryLight = computed(() => cfg.value.primary_color_light ?? '#C9A84C')
    const darkBg       = computed(() => cfg.value.dark_bg             ?? '#2C1810')
    const bgColor      = computed(() => cfg.value.secondary_color     ?? '#F5F0E8')
    const accent       = computed(() => cfg.value.accent_color        ?? '#6B1D1D')
    const fontTitle    = computed(() => cfg.value.font_title          ?? 'Cinzel Decorative')
    const fontHeading  = computed(() => cfg.value.font_heading        ?? 'Cormorant Garamond')
    const fontBody     = computed(() => cfg.value.font_body           ?? 'Crimson Text')

    // ── Section style defaults (config override untuk future use) ─────────
    const galleryLayout = computed(() => cfg.value.gallery_layout ?? defaults.galleryLayout ?? 'vertical')
    const openingStyle  = computed(() => cfg.value.opening_style  ?? defaults.openingStyle  ?? 'gate')
    const revealClass   = defaults.revealClass ?? 'is-visible'

    // ── Invitation data ───────────────────────────────────────────────────
    const details   = computed(() => props.invitation.details   ?? {})
    const events    = computed(() => props.invitation.events    ?? [])
    const galleries = computed(() => props.invitation.galleries ?? [])

    const groomName = computed(() => details.value.groom_name ?? '—')
    const brideName = computed(() => details.value.bride_name ?? '—')
    const groomNick = computed(() => details.value.groom_nickname?.trim() || groomName.value)
    const brideNick = computed(() => details.value.bride_nickname?.trim() || brideName.value)

    const coverPhotoUrl  = computed(() => details.value.cover_photo_url ?? null)
    const coverTextColor = computed(() => coverPhotoUrl.value ? primaryLight.value : primary.value)

    const openingText = computed(() =>
        details.value.opening_text ??
        'Dengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir di hari istimewa kami.'
    )
    const closingText = computed(() =>
        details.value.closing_text ??
        'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir. Atas doa restu yang diberikan, kami ucapkan terima kasih.'
    )

    // ── Section visibility ────────────────────────────────────────────────
    const sectionsMap = computed(() => props.invitation.sections ?? null)

    function sectionEnabled(key) {
        if (!sectionsMap.value) return true
        const s = sectionsMap.value[key]
        if (s === undefined || s === null) return true
        if (typeof s === 'boolean') return s
        return s.enabled ?? true
    }

    function sectionData(key) {
        if (!sectionsMap.value) return {}
        const s = sectionsMap.value[key]
        if (!s || typeof s === 'boolean') return {}
        return s.data ?? {}
    }

    function sectionBg(key) {
        const userBg = cfg.value.section_backgrounds?.[key]
        if (userBg?.type && userBg?.value) return userBg
        return defaults.sectionBgDefaults?.[key] ?? null
    }

    function bgStyle(bg) {
        if (!bg) return {}
        if (bg.type === 'color') return { backgroundColor: bg.value }
        if (bg.type === 'image') return {
            backgroundImage: `url(${bg.value})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            opacity: String(bg.opacity ?? 0.7),
        }
        if (bg.type === 'video') return {
            opacity: String(bg.opacity ?? 0.7),
        }
        return {}
    }

    // ── Events ────────────────────────────────────────────────────────────
    const firstEvent     = computed(() => events.value[0] ?? null)
    const firstEventDate = computed(() => firstEvent.value?.event_date_formatted ?? '')

    // ── Countdown ─────────────────────────────────────────────────────────
    const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
    const targetDate = computed(() => {
        if (!firstEvent.value?.event_date) return null
        const t = firstEvent.value.start_time ? `T${firstEvent.value.start_time}` : 'T00:00'
        return new Date(firstEvent.value.event_date + t)
    })

    let cdTimer
    function updateCountdown() {
        if (!targetDate.value) return
        const diff = targetDate.value - Date.now()
        if (diff <= 0) {
            countdown.value = { days: 0, hours: 0, minutes: 0, seconds: 0 }
            return
        }
        countdown.value = {
            days:    Math.floor(diff / 86400000),
            hours:   Math.floor((diff % 86400000) / 3600000),
            minutes: Math.floor((diff % 3600000) / 60000),
            seconds: Math.floor((diff % 60000) / 1000),
        }
    }

    const pad = n => String(n).padStart(2, '0')

    let destroyed = false

    // ── Gate / opening animation ──────────────────────────────────────────
    const gateOpen      = ref(false)
    const contentOpen   = ref(false)
    const gateAnimating = ref(false)

    // ── Music ─────────────────────────────────────────────────────────────
    const audioEl      = ref(null)
    const musicPlaying = ref(false)

    async function triggerGate() {
        if (gateAnimating.value || gateOpen.value) return
        gateAnimating.value = true
        const delay = defaults.gateDelay ?? 1400
        if (delay > 0) await new Promise(r => setTimeout(r, delay))
        if (destroyed) return
        gateOpen.value      = true
        contentOpen.value   = true
        gateAnimating.value = false
        if (props.invitation.music?.file_url && audioEl.value) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
    }

    function toggleMusic() {
        if (!audioEl.value) return
        if (musicPlaying.value) {
            audioEl.value.pause()
            musicPlaying.value = false
        } else {
            audioEl.value.play().then(() => { musicPlaying.value = true }).catch(() => {})
        }
    }

    // ── Toast ─────────────────────────────────────────────────────────────
    const toastMsg     = ref('')
    const toastVisible = ref(false)
    let toastTimer

    function showToast(msg) {
        toastMsg.value     = msg
        toastVisible.value = true
        clearTimeout(toastTimer)
        toastTimer = setTimeout(() => { toastVisible.value = false }, 2500)
    }

    // ── Gift: copy rekening ───────────────────────────────────────────────
    const copiedAccount = ref(null)

    async function copyToClipboard(text) {
        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text)
            } else {
                const el = Object.assign(document.createElement('textarea'), {
                    value: text,
                    style: 'position:fixed;opacity:0;top:0;left:0',
                })
                document.body.appendChild(el)
                el.focus()
                el.select()
                document.execCommand('copy')
                document.body.removeChild(el)
            }
            copiedAccount.value = text
            showToast('Nomor rekening berhasil disalin ✓')
            setTimeout(() => { copiedAccount.value = null }, 2500)
        } catch {
            showToast('Gagal menyalin — salin manual ya')
        }
    }

    // ── Guest messages ────────────────────────────────────────────────────
    const localMessages = ref([...(props.messages ?? [])])
    const msgForm       = ref({ name: '', message: '' })
    const msgSubmitting = ref(false)
    const msgSuccess    = ref(false)
    const msgError      = ref('')

    async function submitMessage() {
        if (props.isDemo) { msgError.value = 'Form tidak aktif di halaman demo.'; return }
        if (!msgForm.value.name.trim() || !msgForm.value.message.trim()) {
            msgError.value = 'Nama dan ucapan wajib diisi.'
            return
        }
        msgSubmitting.value = true
        msgError.value = ''
        try {
            const res = await fetch(`/${props.invitation.slug}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(msgForm.value),
            })
            const data = await res.json()
            if (!res.ok) throw new Error(data.message ?? 'Gagal mengirim ucapan.')
            localMessages.value.unshift(data.data)
            msgForm.value = { name: '', message: '' }
            msgSuccess.value = true
            setTimeout(() => { msgSuccess.value = false }, 4000)
        } catch (e) {
            msgError.value = e.message
        } finally {
            msgSubmitting.value = false
        }
    }

    // ── RSVP ─────────────────────────────────────────────────────────────
    const rsvpForm = ref({ guest_name: '', attendance: '', guest_count: 1, notes: '' })
    const rsvpSubmitting = ref(false)
    const rsvpSuccess    = ref(false)
    const rsvpError      = ref('')

    async function submitRsvp() {
        if (props.isDemo) { rsvpError.value = 'Form tidak aktif di halaman demo.'; return }
        if (!rsvpForm.value.guest_name.trim() || !rsvpForm.value.attendance) {
            rsvpError.value = 'Nama dan konfirmasi kehadiran wajib diisi.'
            return
        }
        rsvpSubmitting.value = true
        rsvpError.value = ''
        try {
            const res = await fetch(`/${props.invitation.slug}/rsvp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(rsvpForm.value),
            })
            const data = await res.json()
            if (!res.ok) throw new Error(data.message ?? 'Gagal mengirim RSVP.')
            rsvpSuccess.value = true
        } catch (e) {
            rsvpError.value = e.message
        } finally {
            rsvpSubmitting.value = false
        }
    }

    // ── Video embed ───────────────────────────────────────────────────────
    function videoEmbedUrl(url) {
        if (!url) return null
        let m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?/\s]+)/)
        if (m) return `https://www.youtube.com/embed/${m[1]}?rel=0`
        m = url.match(/vimeo\.com\/(\d+)/)
        if (m) return `https://player.vimeo.com/video/${m[1]}`
        return null
    }

    // ── Intersection reveal ───────────────────────────────────────────────
    const revealObservers = new Set()

    function vReveal(el) {
        if (!el) return
        // Accept either a DOM Element or a Vue component instance (extract $el)
        const node = el instanceof Element ? el : el.$el
        if (!(node instanceof Element)) return
        const obs = new IntersectionObserver(([e]) => {
            if (e.isIntersecting) {
                node.classList.add(revealClass)
                obs.disconnect()
                revealObservers.delete(obs)
            }
        }, { threshold: 0.12 })
        obs.observe(node)
        revealObservers.add(obs)
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────
    onMounted(() => {
        if (props.autoOpen) {
            gateOpen.value    = true
            contentOpen.value = true
        }
        updateCountdown()
        cdTimer = setInterval(updateCountdown, 1000)
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            gateOpen.value    = true
            contentOpen.value = true
        }
    })

    onUnmounted(() => {
        destroyed = true
        clearInterval(cdTimer)
        clearTimeout(toastTimer)
        revealObservers.forEach(obs => obs.disconnect())
        revealObservers.clear()
    })

    return {
        // Theme
        cfg, primary, primaryLight, darkBg, bgColor, accent,
        fontTitle, fontHeading, fontBody,
        galleryLayout, openingStyle,
        // Data
        details, events, galleries,
        groomName, brideName, groomNick, brideNick,
        coverPhotoUrl, coverTextColor,
        openingText, closingText,
        firstEvent, firstEventDate,
        // Section
        sectionEnabled, sectionData, sectionBg, bgStyle,
        // Countdown
        countdown, targetDate, pad,
        // Gate
        gateOpen, contentOpen, gateAnimating, triggerGate,
        // Music
        audioEl, musicPlaying, toggleMusic,
        // Toast
        toastMsg, toastVisible, showToast,
        // Gift
        copiedAccount, copyToClipboard,
        // Messages
        localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
        // RSVP
        rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
        // Utils
        videoEmbedUrl, vReveal,
    }
}
