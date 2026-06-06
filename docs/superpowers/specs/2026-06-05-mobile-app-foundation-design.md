# Mobile App — SPEC 0: Foundation

**Date:** 2026-06-05
**Status:** Design (awaiting review)
**Program:** TheDay Mobile App (couple companion — fase persiapan + fase pasca-nikah)
**This spec:** Foundation only. Feature epics are separate specs (see Roadmap).

---

## 1. Context & Goal

TheDay is a Laravel 13 + Inertia + Vue 3 web product. The web stays the source of truth for
the public/SEO surface (landing, blog, public invitation pages). This program adds a **native
mobile app** targeting the **couple side** of the product — the two lifecycle phases:

- **Fase persiapan** (planning): checklist, budget, guest list, vendor, moodboard, documents, invitation management.
- **Fase pasca-nikah** (rawat cerita): memories, anniversary, shared story — net-new, later epic.

**Primary job-to-be-done driving "app, not responsive web":** push reminders + daily-habit
presence (home-screen icon, opened often). Push is the locked reason native is required —
PWA is rejected because iOS web-push is unreliable.

**Out of scope for the app entirely:** guest-facing public invitation pages, landing, blog —
these are server-rendered for SEO and live on web only. The app never ships them.

### Goal of SPEC 0

Stand up the foundation every feature epic depends on, with **zero feature screens** beyond a
minimal authenticated home. After SPEC 0, each feature epic (checklist, budget, …) plugs into a
ready-made shell, auth, API contract, push pipeline, offline layer, and the shared-module pattern.

---

## 2. Locked Architecture Decisions

These were settled during brainstorming and are **not** re-litigated in feature epics:

1. **Wrapper:** Capacitor + **Ionic Vue** (Ionic provides native-feel transitions, gestures,
   safe-area, ripple out of the box — kills most "feels like a webview" tells).
2. **Rendering:** **No server-rendered pages inside the app.** Every app screen is a
   **client-side SPA** consuming **JSON over the Sanctum API**. The in-editor invitation
   *preview* is a **client render** of template Vue components, never an iframe to the server.
3. **Code sharing — "shared module" pattern (opsi 3):** each couple feature is built **once** as
   an API-driven Vue module. The **web** mounts it inside a thin Inertia wrapper; the **app**
   mounts it inside the Capacitor shell. Edit the module once → both surfaces change.
   (Web updates on deploy; app requires an apk rebuild to ship UI changes — accepted.)
4. **Auth:** Sanctum **token** (Bearer) for the app; web keeps session-cookie. Same
   `auth:sanctum` guard already serves both.
5. **Platforms:** **Android-first** (buildable on the user's Windows machine). iOS deferred until
   a Mac / cloud-Mac CI is available (iOS build is impossible on Windows — Capacitor does not
   change Apple's rule).
6. **Billing:** in-app digital purchases use **native IAP** (Apple/Google), not Mayar. Mayar
   stays for web. (Billing is a late epic.)
7. **App navigation shell:** bottom tab bar — **Home · Undangan · Budget · Planner · More**
   (confirmed from user mockup).
8. **Invitation editing in-app:** keeps the existing editor UI/UX (same Vue components),
   re-wired from Inertia props to API. Preview is client-rendered. "View published invitation"
   opens the system browser externally (not embedded server render).

### What "no server render in app" reconciles with SEO

| Surface | Render | Shipped in app? |
|---|---|---|
| Landing, blog, public invitation (guest/SEO) | Server (Inertia/Blade) | No — web only |
| Couple dashboard (checklist, budget, editor, …) | Client SPA (API) | Yes — all |

The app is couple-only, so the server-rendered SEO surface is simply never part of it. No conflict.

---

## 3. Foundation Scope (the units SPEC 0 delivers)

Each unit has one purpose, a defined interface, and is independently testable.

### 3.1 Capacitor + Ionic scaffold
- Add Capacitor (`@capacitor/core`, `@capacitor/cli`), `@capacitor/android`, and Ionic Vue
  (`@ionic/vue`, `@ionic/vue-router`) to the existing repo (no new repo).
- `capacitor.config.ts` — appId, appName, **bundled web assets** (no `server.url` remote mode),
  Android scheme config.
- New folders: `android/` (generated). `ios/` deferred.
- A **separate Vite entry** for the app SPA (`resources/js/app-mobile/`) distinct from the
  Inertia web entry, so the app bundles only what it needs.
- **Interface:** `npm run build:app` produces the SPA bundle; `npx cap sync` copies it into `android/`.

### 3.2 App SPA shell
- Ionic app root with **bottom tab navigation**: Home · Undangan · Budget · Planner · More.
- `@ionic/vue-router` routes with **native push/pop transitions**.
- Native-feel concerns wired once: safe-area insets, `@capacitor/status-bar`,
  `@capacitor/splash-screen` (hide on ready, skeleton not spinner), `@capacitor/keyboard`
  (resize + scroll-into-view), Android hardware **back button** → router history (never exits
  mid-stack), `@capacitor/haptics` on primary taps.
- **Interface:** feature epics register a tab/route + screen component; shell handles chrome.

### 3.3 Auth (token) module
- App login/register/logout against `auth:sanctum` issuing a **personal access token**.
- Token stored in `@capacitor/preferences` (secure storage on device).
- Partner/couple linking reachable (reuses existing couple-account backend).
- Session bootstrap on cold start: load token → fetch `me` → route to Home or Login.
- **New backend:** `POST /api/auth/login`, `POST /api/auth/register`, `POST /api/auth/logout`,
  `GET /api/me` (token-issuing endpoints; web session flow untouched).
- **Interface:** `useAuth()` composable — `login`, `logout`, `user`, `isAuthenticated`.

### 3.4 HTTP client + offline layer
- Single Axios instance: base URL, Bearer token injection, 401 → re-auth, retry with backoff.
- **Read cache:** GET responses cached (`@capacitor/preferences`, later SQLite if volume grows)
  so screens open instantly and survive no-signal. Stale-while-revalidate.
- **Optimistic writes:** mutations apply locally, queue, sync, reconcile/rollback on failure.
- Network status via `@capacitor/network`; offline banner; queued-action flush on reconnect.
- **Interface:** `useApi()` / `useResource()` composables consumed by every feature module.

### 3.5 Push notification pipeline
- `@capacitor/push-notifications` + **Firebase Cloud Messaging** (Android).
- Register device → obtain FCM token → **store server-side** against the user (new
  `device_tokens` table + `POST /api/devices`).
- **Backend send path:** a notification service that targets a user's device tokens via FCM
  (reuses existing in-app `NotificationController` domain; adds a push channel).
- **Tap routing (deep link):** notification payload carries a route; tapping opens the exact
  screen (e.g. a due checklist task), not just Home.
- Permission request flow + graceful denial.
- **Interface:** backend `PushNotifier::send(user, title, body, route)`; client registers + routes.
- **Note:** APNs/iOS wiring is stubbed but not activated until iOS is built.

### 3.6 Shared-module convention
- Document and scaffold the pattern: a feature module is a self-contained API-driven Vue unit
  under `resources/js/modules/<feature>/`, with its **web wrapper** (Inertia page mounts it) and
  its **app screen** (Ionic page mounts it) as thin adapters.
- One reference module proves the pattern (a trivial "Home summary" widget), so feature epics
  copy a known-good shape rather than inventing one.
- **Interface:** a module exports a root component + a data composable; surfaces differ only in chrome.

### 3.7 Android build path
- Local build via Android Studio (SDK/emulator) and CLI (`npx cap run android`).
- Live-reload dev config (`npx cap run android --livereload --external`) pointing at Vite.
- Document the **deploy boundary**: `android/`/`ios/` are *not* served by hosting; if the whole
  repo is pulled to the server they are inert. GitHub-Actions deploy excludes them
  (`--exclude 'android/' --exclude 'ios/'`).
- App store binaries are a **separate pipeline** from the Laravel hosting deploy.

---

## 4. Data Flow

```
Cold start
  Capacitor loads bundled SPA (instant, no network)
    → useAuth bootstraps: read token (Preferences)
        token? → GET /api/me → Home tab
        none?  → Login screen
  Home/feature screen
    → useResource(key): show cached data instantly (skeleton if none)
        → revalidate via Axios (Bearer) → API JSON → update cache + UI
  Mutation
    → optimistic local update → queue → POST/PATCH (Bearer)
        success → reconcile ; failure → rollback + retry/queue
  Push
    FCM → device → tap → payload.route → router.push(screen)
```

Backend reuses existing domain logic. Where logic currently lives inside Inertia controllers, it
is extracted into **Service classes** so both the Inertia controller (web) and a new
`Api\*Controller` (JSON) call the same service — no duplicated business logic. (Per-feature
extraction happens in each feature epic, not SPEC 0; SPEC 0 only does this for auth/me/devices.)

---

## 5. Error Handling

- **No signal:** cached reads render; writes queue; offline banner; auto-flush on reconnect.
- **401 / expired token:** clear token, route to Login, preserve intended destination.
- **Push permission denied:** app fully usable; a Settings prompt explains lost reminders.
- **FCM token rotation:** re-register on app resume; server upserts by device id.
- **Cold-start API failure:** show cached Home if present, else a retry state — never a white screen.
- **Back button at tab root:** standard Android "confirm exit" rather than abrupt close.

---

## 6. Testing

- **Backend (PHPUnit):** token login/register/logout, `GET /api/me`, device registration,
  push-send service (FCM client mocked). Assert web session auth is unchanged.
- **Module unit (Vitest):** auth composable state machine; HTTP client token injection, retry,
  401 handling; cache stale-while-revalidate; optimistic write rollback.
- **Shell:** route transitions, hardware back-button navigation, deep-link route resolution.
- **Manual device matrix:** cold-start time, offline open, push receipt + tap routing, safe-area
  on a notch device, keyboard-over-input behavior — the "feels native" checklist.

---

## 7. Definition of Done (SPEC 0)

- [ ] App installs on an Android device/emulator from a locally built apk.
- [ ] Login with a real account via token; cold start restores session; logout clears it.
- [ ] Bottom tab shell with native transitions, safe-area, splash→skeleton, working back button.
- [ ] A screen opens instantly from cache and survives airplane mode (cached read).
- [ ] A test push is received, and tapping it deep-links to a target screen.
- [ ] Reference shared-module renders identically inside both the web wrapper and the app shell.
- [ ] Documented build + deploy-exclusion steps; web session auth provably unaffected.

---

## 8. Program Roadmap (future specs — not part of SPEC 0)

Each is its own spec → plan → implementation cycle. Order reflects daily-habit value first.

| Spec | Epic | Notes |
|---|---|---|
| **0** | **Foundation** | this doc |
| 1 | Checklist | hero daily feature; subtasks, AI draft, .ics, reminders push |
| 2 | Notifications + Push center | in-app inbox + preferences; pairs with habit loop |
| 3 | Budget Planner | categories, items, payments, insights |
| 4 | Guest List + RSVP + Buku Tamu | import/WhatsApp, summaries |
| 5 | Vendor + Moodboard | |
| 6 | Dokumen Nikah | document status + file storage |
| 7 | Invitation management | full editor as SPA module (same UI), client preview, external "view" |
| 8 | Billing / Paket / Gift | **native IAP** (Apple/Google), not Mayar |
| 9 | Support + Profile + Couple account | |
| 10 | Fase pasca-nikah (rawat cerita) | net-new: memories, anniversary, shared story |

### Known program-level risks (flagged, decided later)

- **IAP tax (Spec 8):** Apple/Google take 15–30% on in-app digital subscriptions and require
  their billing for digital goods. Decision locked: use native IAP in-app; Mayar stays web.
- **Editor on small screens (Spec 7):** the 6-step wizard + customize is heavy on mobile; UI is
  reused as-is per user decision, re-wired to API; revisit ergonomics during that epic.
- **iOS gap:** everything ships Android-first; iOS activation requires Mac/cloud-Mac CI.
```
