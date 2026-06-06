# TheDay Mobile App (Capacitor + Ionic Vue)

## Build & run (Android, from Windows)
- Dev (live reload): `npm run dev` (Laravel + Vite) then `npx cap run android --livereload --external`
- One-off run: `npm run build:app && npx cap sync android && npx cap run android`
- Open in Android Studio: `npx cap open android`

## Architecture
- App SPA lives in `resources/js/app-mobile/` (Ionic Vue, client-only).
- It talks to Laravel only via JSON over `auth:sanctum` Bearer tokens — no server-rendered pages in the app.
- Couple features are shared modules in `resources/js/modules/<feature>/`, mounted by both the app shell and a thin Inertia web wrapper. Reference: `resources/js/modules/home-summary/`.
- Token stored via `@capacitor/preferences` (`lib/storage.js`). HTTP via `lib/http.js` (Bearer, retry, 401). Offline cache + optimistic writes via `composables/useResource.js`. Session via `composables/useAuth.js`.
- Push via FCM: client `native/push.js`; server `App\Services\Push\PushNotifier` + `device_tokens` table.

## Tests
- Backend: `php artisan test` (mobile coverage under `tests/Feature/Api/*`, `tests/Unit/Push/*`).
- Frontend units: `npm run test:unit` (mobile suites under `resources/js/app-mobile/**` + `resources/js/modules/**`).

## iOS
Not built yet — requires macOS + Xcode (impossible on Windows). The code is identical (same Capacitor + Ionic + Vue); only the build/sign/ship pipeline needs a Mac. Add via cloud-Mac CI (GitHub Actions macOS runner, Codemagic, Ionic Appflow) when ready: a CI step runs `npx cap add ios` + build. Push on iOS uses APNs (add an APNs channel alongside the FCM one in `PushNotifier`).

## Deploy boundary
`android/` source is committed; build outputs (`/app-dist`, `/android/app/build`, `/android/.gradle`, `/android/build`) are gitignored.
Hosting (Laravel) never serves `android/`. If a deploy rsyncs the repo, exclude it:
`rsync --exclude 'android/' --exclude 'app-dist/' ...`. App store binaries ship via a separate pipeline, not the web deploy.

## Env
- `FCM_SERVER_KEY` — server-side key for sending pushes (consumed by `App\Services\Push\FcmClient`).
- `android/app/google-services.json` — Firebase config (add per the Firebase console; not committed if it carries secrets).
