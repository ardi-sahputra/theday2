# chara.id — Competitor Analysis

**Date:** 2026-05-19
**Source:** https://chara.id (live homepage scan via gstack headless browser)
**Author:** Initial sweep — surface-level public-page intel only (no login, no internal admin/billing access)

---

## TL;DR

**chara.id is a Wedding Planning OS, NOT an invitation-first product.** Digital invitation is one module out of four (Plan → Pay → Invite → Track). Pricing is **one-time per event** (no subscription), with tiered packages (Wedding Pass / Journey / VIP) gated by guest count. Theme catalog is massive (~50+) and heavy on Gen Z pop culture / meme aesthetic. Direct overlap with TheDay only on the invitation module.

**TheDay is invitation-first** — deeper craft per template, premium curated aesthetic, subscription model. Different positioning, different defensibility.

---

## Product Snapshot

**Tagline:** "Undangan Digital Praktis & Gampang — Tema bebas ganti, kirim WhatsApp ke ratusan tamu, kelola RSVP — sekali bayar per acara."

**Positioning headlines on homepage:**
- "Sekali bayar per acara" (one-time payment per event)
- "Garansi refund 7 hari"
- "Tema bebas ganti-ganti"
- "Trial 7 Hari · Tanpa Kartu"
- "Dipercaya 1.000+ pasangan"

**Affiliate program:**
- 10% commission per sale
- 30-day cookie window
- Rp 100K minimum payout
- "Unlimited" volume
- Target: wedding vendors (MUA, fotografer, dekorasi), content creators, mempelai dengan follower

---

## Feature Modules (Wedding OS Scope)

### 1. Plan (Wedding Planner)
- Budget tracking
- Checklist
- Timeline
- Vendor management
- Synced calendar

### 2. Pay (Group Funding)
- "Patungan Rekening Transparan"
- Crowdfund wedding budget dari family/friends
- Transparent progress tracker ("Rp 50jt terkumpul dari target Rp 100jt")
- Multiple bank accounts target

### 3. Invite (Digital Invitation)
- 50+ template themes
- WhatsApp blast otomatis
- Bebas ganti tema selama paket aktif
- Customizable per-tamu pesan
- Custom theme service (Wedding VIP tier only)

### 4. Track (Analytics & Guest Mgmt)
- Daftar Tamu (guest list import via Excel/manual)
- Check-in QR di venue
- Ucapan Tamu (guest wishes)
- Activity feed real-time
- RSVP analytics
- Amplop digital (digital cash gift transfer tracking)

### 5. Vendor Marketplace (Database)
- Catering (e.g. "Catering Nusantara - Paket Premium Rp 4.500.000")
- Venue (e.g. "Grand Ballroom Rp 65jt")
- Verified vendor directory
- Pricing transparency

### 6. Support
- In-app chat bubble (bottom-right, dark circle)
- FAQ quick reply chips:
  - "Berapa harga paketnya?"
  - "Bisa custom tema sendiri?"
  - "Cara kirim via WhatsApp?"
  - "Berapa lama prosesnya?"
- Branded "Tim Chara"
- Greeting message: "Halo! Ada yang bisa kami bantu?"

---

## Pricing Model

| Tier | Guest limit | Notable feature |
|------|-------------|-----------------|
| Trial | 7 hari preview only | No card required, no blast WA |
| Wedding Pass | 300 tamu | Wedding Planner unlocked |
| Wedding Journey | 500 tamu | Wedding Planner included |
| Wedding VIP | Unlimited tamu | Custom theme design service |

**Critical model differentiator:**
- **chara.id:** Sekali bayar per acara (one-time payment, lifetime access for that event)
- **TheDay:** Free tier permanent + Premium subscription
- **Trade-off:** Chara model = lower commitment (no recurring billing), easier conversion. TheDay model = recurring revenue, but psychological friction (subscription fatigue).

---

## Theme Catalog (Observed 50+)

### Pop Culture / Meme (heavy)
- **Social media format:** IGaa (Instagram), Facebookaa, Threadsaa, Twitteraa, Tiktokaa, Netflixaa, YouTube Style, Whatsappaa
- **Music:** Wrapped (Spotify Wrapped 2025 story), Spotifyaa (playlist album), Mixtape (80s cassette), K-Pop (photocard)
- **Gaming:** MLBB Match (Mobile Legends lobby), Pixel (RPG GameBoy)
- **Dating apps:** Tinderaa (swipe match)
- **Travel:** Boarding (boarding pass), Karcis KAI (Indonesia railway ticket)
- **Media:** Comic (panel book), Newspapera (NYT-style editorial), Cinema (movie ticket), Festival (Coachella ticket), Polaroida (scrapbook)

### Indo Street Culture (signature differentiator)
- **Pecel Lele** — warung spanduk
- **Warkop** — Indo cafe nongkrong
- **Karcis KAI** — Indonesian railway ticket
- **Betawia** — Betawi modern
- **Sunda** — adat Sunda
- **Jawa** — adat Jawa (sogan, batik kawung, weton)

### Premium / Curated Aesthetic
- Pearl (champagne gold + pearl strands)
- Wisteria (Japanese garden hanami)
- Art Deco (Great Gatsby)
- Wabi-Sabi Zen (Japanese kintsugi)
- Mid-Century (Eames 60s)
- Vogue (high fashion editorial)
- Cottagecore (Pinterest garden)
- Dark Academia (Oxford library)
- Wes Anderson (Grand Budapest Hotel pastel)
- Apple Keynote (minimalist gradient)
- Synthwave 80s (Stranger Things neon)
- Coquette (TikTok aesthetic pink bows)
- Balletcore (ballet stage vintage)
- Disney (kerajaan dongeng)
- Ghibli (sakura watercolor)
- Y2K Frutiger (MSN messenger glass)
- Tarot (zodiac The Lovers)
- Constellation (zodiac star map)

### Natural / Lifestyle
- Coffee (third-wave cafe)
- Plant (garden party sage green)
- Scandinavian (Nordic minimalist hygge)

---

## Tech & UX Patterns Observed

### Navbar
- Logo "Chara" (custom mark, dark)
- Menu items: Fitur, Tema, Testimoni, Affiliate 10%
- Auth actions: Masuk (link), Register (dark button)
- Fixed top, scroll-blur background (similar pattern to landing.blade.php TheDay)

### Hero
- 2-line bold title ("Undangan Digital · Praktis & Gampang")
- Subhead 2-3 lines
- Dual CTA: primary "Mulai Bikin Undangan Trial 7 Hari" + secondary "Lihat Paket & Harga"
- 3-pill feature line ("Sekali bayar per acara · Garansi refund 7 hari · Tema bebas ganti-ganti")
- Avatar row social proof ("Dipercaya 1.000+ pasangan")

### Template Gallery
- Card grid with thumbnail + name + 1-line description + view/like counts (e.g. "4.8k views, 245 likes")
- Hover reveals likely "Pilih Tema" / "Kelola Undangan" CTAs

### CS Chat Widget
- Position: fixed bottom-right
- Collapsed: 64×64px dark circle (bg-gray-900) with icon
- Expanded: panel with "Tim Chara · Siap bantu kamu" header, bot greeting message, FAQ chip suggestions, text input
- Pattern: matches Intercom/Crisp-style widget — same pattern TheDay just implemented in support-chat batch

### Floating "TERBARU" Activity Notifications
- Bottom-left toasts: "Siti & Ahmad baru aja bikin undangan" / "Dewi & Andi baru aja bikin undangan"
- Social proof real-time feed (could be real or simulated)

---

## Side-by-Side Comparison: chara.id vs TheDay

| Dimension | chara.id | TheDay |
|-----------|----------|--------|
| **Product scope** | Wedding OS (plan + pay + invite + track + vendor marketplace) | Invitation-first |
| **Primary value** | End-to-end wedding event management | Premium digital invitation craft |
| **Pricing model** | Sekali bayar per acara (one-time) | Free tier + Premium subscription |
| **Tier structure** | Wedding Pass / Journey / VIP (by guest count) | Free / Premium (by feature) |
| **Trial** | 7 hari free, no card, preview-only | Free tier permanent |
| **Refund policy** | Garansi 7 hari | (unknown, likely none) |
| **Theme count** | 50+ | 32 (28 existing + 4 no-photo new) |
| **Theme philosophy** | Volume + pop culture meme | Curated quality + premium luxury + pop culture mix |
| **Pop culture themes** | Heavy (50+ memes covered) | Curated (~10 — Netflix, Spotify Wrapped, Pokemon TCG, Comic, IG Stories, Vinyl, etc.) |
| **Premium luxury** | Limited (Pearl, Art Deco, Vogue, Wabi-Sabi) | Strong (Onyx Noir, Velvet Burgundy, Belle Epoque, Tuscany, Astronomy custom, Vintage Postal) |
| **Religious/No-photo** | None visible | Yes (Islamic Geometric, Ayat & Hadits, Botanical, Letterpress) |
| **Indo regional adat** | Strong (Sunda, Betawi, Jawa) | Limited (Minang, Nusantara generic) |
| **Indo street culture** | Strong (Pecel Lele, Warkop, Karcis KAI) | None |
| **Sosmed format** | Strong (IG/FB/Threads/Twitter/TikTok/Netflix/YouTube native) | Limited (IG Stories template only) |
| **WhatsApp blast** | Built-in, customizable per-tamu | (unknown, need to check) |
| **QR Check-in** | YES | (unknown) |
| **Wedding planner** | YES (budget, checklist, timeline, vendor) | NO |
| **Crowdfund (Patungan)** | YES (transparent target tracker) | NO |
| **Vendor marketplace** | YES (catering, venue with prices) | NO |
| **Amplop digital** | YES (cash gift tracking + transfer) | (unknown, possibly via Gift premium feature) |
| **Affiliate program** | 10% komisi, 30-day cookie | NONE |
| **CS chat** | YES (Tim Chara, FAQ chips) | YES (just built — branded TheDay) |
| **Social proof** | "1.000+ pasangan" + activity toasts + testimonial grid | (unknown) |
| **Brand voice** | Casual + Gen Z meme aesthetic ("aja", "ngajak ngobrol langsung", "gampang") | Premium + classic + curated |

---

## Theme Overlap Map

### Overlapping themes (head-to-head)
| chara.id | TheDay |
|----------|--------|
| Tarot | tarot-reading |
| Wrapped / Spotifyaa | spotify-wrapped |
| Netflixaa | NetflixTemplate |
| Comic | comic-book |
| Wabi-Sabi Zen | japanese-ryokan |
| Art Deco | art-deco-gatsby |
| Constellation | astronomy-celestial |
| Vintage Newspaper (Newspapera) | (none direct, vintage-postal is closest) |
| Polaroida / Coffee scrapbook | photo-album |
| Pixel GameBoy | (none) |
| Mixtape | vinyl-record |

### chara has, TheDay doesn't
- **Sosmed native:** IGaa, Facebookaa, Threadsaa, Twitteraa, Tiktokaa, YouTube Style, Whatsappaa
- **Indo street:** Pecel Lele, Warkop, Karcis KAI
- **Indo adat:** Betawi modern, Sunda lengkap, Jawa lengkap (sogan + batik kawung + weton)
- **Gaming:** MLBB Match (Mobile Legends Indo Gen Z target)
- **Dating:** Tinderaa swipe
- **Travel:** Boarding pass
- **Media:** Cinema ticket, Festival ticket, Newspaper editorial
- **Aesthetic micro-trends:** Coquette, Y2K Frutiger, Dark Academia, Wes Anderson, Apple Keynote, Mid-Century Eames, Cottagecore, K-Pop photocard, Disney, Ghibli, Vogue, Synthwave, Balletcore, Plant, Scandinavian
- **Couple aesthetic:** Pearl (black-tie gala), Coffee third-wave

### TheDay has, chara doesn't
- **No-photo religi tier:** Islamic Geometric, Ayat & Hadits Scroll (just built — defensible niche)
- **No-photo secular:** Letterpress Monogram, Botanical Illustration
- **Premium luxury non-pop-culture:** Onyx Noir (dark marble + gold leaf), Velvet Burgundy (Victorian), Belle Epoque (Paris watercolor), Tuscany Vineyard
- **Custom data themes:** Astronomy Celestial (real star map at wedding date+location)
- **Storytelling experiential:** Vintage Postal, Photo Album, Pop-up Card, Snow Globe, Flashlight, Treasure Hunt, Silk Veil, Year Scrubber
- **Pokemon TCG:** card-collecting Gen Z

---

## Strategic Insights

### 1. Different Market Position
- **chara = wedding logistics platform** (breadth, workflow lock-in, vendor network defensibility)
- **TheDay = invitation craft studio** (depth per template, design taste defensibility)

These aren't direct apples-to-apples competitors. Overlap is on the invitation module only. User decides at acquisition stage:
- "I want a beautiful invitation" → TheDay
- "I want help running my wedding end-to-end" → chara

### 2. Pricing Model Trade-off
- **chara one-time-pay:** No subscription fatigue. User commits once, knows total cost. Easier conversion for users skeptical of monthly bills. But: no recurring revenue, must constantly acquire new couples (one-shot business).
- **TheDay subscription:** Recurring revenue. But: 99% of wedding users want feature for ~3 months max (planning to event), then never use again. Churn is built-in. Mismatch with use-case.
- **Hybrid possibility:** TheDay could test "Lifetime Wedding Plan" tier (one-time Rp 499K for 6 months access + permanent invitation hosting). Hedges the subscription model without abandoning recurring premium.

### 3. Theme Strategy
- chara's volume strategy (50+ themes) makes "find your aesthetic" easy. TheDay's curated 32 means higher avg quality but smaller match probability.
- **Gap to close (if TheDay wants to compete on volume):**
  - Indo street culture pack (Pecel Lele, Warkop, Karcis KAI) — viral on TikTok, accessible
  - Sosmed format pack (IG/Twitter/TikTok native UI as invitation layout)
  - Indo adat tier (Sunda, Betawi, Jawa expansion beyond Minang/Nusantara)
- **Gap to keep (TheDay defensible):**
  - No-photo religi (Islamic Geometric, Ayat & Hadits) — chara has none
  - Astronomy custom star map — defensible technical moat (no one can copy easily)
  - Premium luxury non-meme — chara skewed Gen Z meme, TheDay can own "mature couple, refined taste" segment

### 4. Affiliate Program Gap
- chara has 10% commission program. TheDay has none.
- Wedding vendor ecosystem (MUA, photographers, decoration) acts as natural referrer for chara.
- Recommendation: add affiliate to TheDay (e.g. 15% one-time, 30-day cookie) to attract same vendor referrers. Slightly higher commission to win share.

### 5. Wedding Planner Module Question
- chara's wedding planner is bundled with paid tier. Even if user comes for invitation, they end up using planner — creates lock-in.
- TheDay has no equivalent. Decision: build it? Or focus on invitation craft moat?
  - **Build it:** Long-tail retention, justify higher subscription price, compete head-on.
  - **Skip it:** Stay focused on invitation, accept chara wins on breadth, win on depth.
  - **Middle ground:** Add lightweight checklist + budget tracker (1-2 days build) as retention helper, skip vendor marketplace (large undertaking).

### 6. CS Chat Parity Achieved
- chara has CS bubble (Tim Chara). TheDay just built same pattern in support-chat batch.
- Feature parity. No catch-up needed on this dimension.

---

## Open Questions (Not Yet Researched)

1. **Pricing numbers** — Wedding Pass / Journey / VIP price points (need to access pricing page deeper).
2. **Custom theme service** — chara Wedding VIP includes "custom theme by design team". Volume and pricing?
3. **Vendor marketplace economics** — does chara take cut from vendor bookings, or just refer?
4. **Crowdfund (Patungan) mechanic** — how does the group funding work? Bank API integration? Manual transfer tracking? Fees?
5. **WA blast technical implementation** — official WhatsApp Business API or third-party gateway?
6. **Real activity feed** — are "Siti & Ahmad baru aja bikin undangan" toasts real-time or simulated for social proof?
7. **Template quality variance** — 50+ themes is volume play. Is each theme polished, or some are quick-and-dirty? (Would need to click-through demo to verify.)
8. **Mobile app** — does chara have native iOS/Android app, or web-only?

---

## Strategic Recommendation

**Don't pivot to compete on chara's turf.** Their lead is in wedding OS breadth + vendor network. Build moat where TheDay already has signal:

1. **Double-down on invitation craft excellence**
   - Continue no-photo religi tier (just shipped — unique to TheDay)
   - Add Indo street culture pack (Pecel Lele/Warkop/Karcis KAI) — quick win, viral potential
   - Add Indo adat expansion (Sunda, Jawa, Betawi as proper themes)
   - Sosmed format pack (Twitter/Threads/TikTok native UI) — Gen Z catnip, low effort

2. **Add affiliate program** — close the gap on vendor referral acquisition.

3. **Test hybrid pricing** — "Wedding Plan" one-time tier alongside subscription. Measure conversion delta.

4. **Add lightweight planning tools** — checklist + budget (not full wedding OS). Retention + justify Premium price.

5. **Don't build vendor marketplace** — too much overhead, low strategic value vs effort. Let chara win that lane.

6. **Marketing positioning:** "TheDay — undangan terbaik untuk couple yang nilai design taste premium" vs chara's "Wedding OS lengkap". Different segment, different message.

---

## References & Methodology

- Scan via `gstack browse` (headless Chromium) due to Cloudflare 403 blocking WebFetch.
- Surface-level public homepage + theme grid + FAQ + chat bubble inspected.
- No login, no pricing-page deep scan, no internal admin/billing data.
- Activity feed toasts may be simulated social proof — flagged as open question.
- Theme counts based on visible homepage grid; actual catalog may be larger via "/templates" deep dive.
