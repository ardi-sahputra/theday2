# Couple Account Design Spec — Review

**Date:** 2026-05-19
**Reviewer:** Claude (Opus 4.7)
**Target spec:** [2026-05-19-couple-account-design.md](2026-05-19-couple-account-design.md)
**Status:** Gap analysis — spec foundation solid, gaps below should be closed before implementation

---

## Summary

Spec choice (Couple Link over Shared Workspace) is sound: minimal schema change, reversible, fits 1-partner constraint. Below are gaps that risk data leakage, broken flows, or trust issues if shipped as-is.

Priority order: **Critical → Important → Nice-to-have**

---

## Critical

### 1. Affected model list incomplete (section 2)

Spec lists 9 models for `auth()->id()` → `effectiveUser()->id()` swap:
> `Invitation`, `WeddingPlan`, `CoupleProfile`, `ChecklistTask`, `WeddingBudget`, `Subscription`, `InvitationAddon`, `Transaction`, `Gift`

Codebase scan (`app/Models/`) shows additional user-scoped or invitation-scoped models likely missing:

- `GuestList`
- `Rsvp`
- `InvitationDetail`
- `InvitationSection`
- `InvitationEvent`
- `InvitationGallery`
- `InvitationMusic`
- `GuestMessage` / `GuestMessageLog`
- `WhatsAppMessageTemplate`
- `UserNotification` / `NotificationPreference`
- `InvitationView` (analytics)

**Risk:** If any model is missed, partner cannot see/edit that data slice. Worst case: silent data divergence (partner edits their own empty row instead of owner's).

**Action:** Audit every model with `user_id` or `invitation_id`. Add to spec, or refactor to a global query scope (`BelongsToEffectiveUser` trait) that's applied once and enforced via static analysis.

---

### 2. Token security not specified

Spec defines token as `string(64) UNIQUE` but does not specify:

- Entropy source (must be CSPRNG — `random_bytes`, not `Str::random` if not crypto-safe in this Laravel version)
- Length in bits (64-char base64 = ~48 bytes entropy; 64-char hex = 32 bytes — clarify)
- Hashed at rest? Storing plaintext token in DB means a leaked backup/SQLi exposes valid invite links.

**Risk:** Token enumeration, replay from leaked DB dump.

**Action:**
- Generate 32 bytes via `random_bytes(32)` → hex encode for URL
- Store `hash('sha256', token)` in `couple_links.token`
- Lookup by hashing incoming token, never store/log plaintext

---

### 3. Race condition on accept

Two concurrent `POST /couple/accept/{token}` requests (mobile + desktop tabs) can both pass the `status = pending` check and both write `partner_id`.

**Risk:** Inconsistent state, possible UNIQUE violation crash on one side.

**Action:** Wrap accept in DB transaction with `lockForUpdate()`:

```php
DB::transaction(function () use ($token) {
    $link = CoupleLink::where('token_hash', hash('sha256', $token))
        ->where('status', 'pending')
        ->lockForUpdate()
        ->firstOrFail();
    // ...mutate
});
```

Plus rely on `UNIQUE(partner_id)` as last line of defense.

---

### 4. Queue / job context loss

`effectiveUser()` reads `auth()->user()`. Inside queued jobs (`ShouldQueue`), there is no authenticated user — `auth()->user()` returns null.

**Affected paths:**
- Queued mail (`Mail::queue(...)`)
- Notification dispatch (`$user->notify(...)` if queued)
- Background exports, PDF generation, analytics aggregation

**Risk:** Job crashes or operates on null user, sending mail to wrong recipient or silently skipping work.

**Action:** Jobs must accept explicit `effective_user_id` in constructor. Spec section 2 should mandate: "Any job dispatched from a request context must receive `effectiveUser()->id` as a constructor arg, not resolve it at handle-time."

---

### 5. Billing scope undefined

Spec states partner has "full access to all features including billing" and "owner's subscription covers partner."

Unanswered:
- Can partner **cancel** owner's subscription?
- Can partner **upgrade/downgrade** plan (charges owner's card)?
- Can partner **see** owner's payment method / transaction history (PII)?
- Refund attribution — who initiated, who receives?
- Chargeback risk if partner makes a purchase owner disputes

**Risk:** Trust violation, potential PCI / financial dispute.

**Action:** Either:
- (a) Explicit allowlist of billing actions partner can perform (recommend: view-only on billing, no mutate)
- (b) Move billing to "Out of Scope" for partner and document clearly

---

## Important

### 6. Re-invite / resend flow missing

Edge case table says "owner can resend from settings" but no route, controller method, or cooldown is specified. Without cooldown → spam vector.

**Action:** Define `POST /couple/invite/resend`, cooldown 5 min between resends, max 3 resends per 24h.

### 7. Rate limiting

No mention of throttling on:
- `POST /couple/invite` (spam mass-invites to enumerate emails)
- `GET /couple/accept/{token}` (token brute force, especially if token < 256 bits)
- `POST /couple/accept/{token}` (race exploit amplifier)

**Action:** Apply Laravel `throttle:` middleware. Suggested limits: invite 5/hour per owner, accept 10/min per IP.

### 8. Email verification edge cases

- Owner invites email X. X registers fresh account during invite window. Then owner changes their own profile email or revokes — what happens to in-flight token?
- Owner invites email X. Owner edits invite to email Y. Both tokens valid?
- Email case sensitivity (`User@example.com` vs `user@example.com`) — normalize?

**Action:** Document: only one active invite per owner; new invite revokes prior. Normalize emails to lowercase before comparison.

### 9. Mid-session revoke UX

Partner is mid-edit (e.g., filling RSVP form). Owner clicks "Cabut Akses." Next partner request:
- 403 hard fail with no context?
- Silent redirect to login?
- Banner saying "access removed, your draft is lost"?

**Action:** Spec the failure mode. Recommend: friendly page explaining access ended, with link back to partner's own dashboard. Drafts not preserved (acceptable trade-off, document it).

### 10. No audit trail — re-evaluate

Spec lists audit log as out-of-scope. But partner can:
- Delete invitation (destructive)
- Cancel subscription (financial)
- Delete guest list rows
- Edit checklist marked done by owner

Zero accountability erodes trust between couple — exact opposite of feature intent.

**Action:** Minimum viable audit: add `last_modified_by_user_id` column to high-value tables (`Invitation`, `Subscription`, `Transaction`, `Gift`, `WeddingBudget`). Cheap, no separate log table needed.

### 11. Owner account deletion warning

Edge case table: "Owner deletes account → cascade delete couple_links, partner loses access."

**Missing:** Owner's "delete account" confirmation modal must explicitly warn about partner impact (and offer to revoke first vs cascade).

### 12. Re-link cooldown

After revoke, owner can immediately invite same partner again. Abuse pattern: gaslighting / coercive re-linking.

**Action:** 24-hour cooldown between revoke and re-invite of same email. Partner side: same cooldown to accept new invite from same owner.

### 13. CoupleProfile auto-fill

Model `CoupleProfile` exists (likely groom/bride identity for the invitation card). When partner accepts:
- Auto-populate partner as the second person in CoupleProfile?
- Or leave manual?
- What if CoupleProfile already filled with different name (e.g., owner filled placeholder)?

**Action:** Decide explicitly. Recommend: no auto-fill, but show a "Use my profile here?" prompt on partner's first dashboard visit.

### 14. Notification routing

If owner gets a paid RSVP notification, does partner also get pinged? On which channels (email, push, in-app)?

**Action:** Define. Recommend: in-app notifications shared (both see same feed when in partner context); email/push only to the user whose `users.email` is on the row (no duplicate sends).

---

## Nice-to-have

### 15. Performance — cache effectiveUser

Every dashboard request runs an extra `CoupleLink` query. Cache the resolution on the request lifecycle (memoize) or session.

```php
// Helper memo per request
function effectiveUser(): User
{
    return app()->make('effective_user', fn () => /* resolve */);
}
```

### 16. Testing strategy

Spec has no testing section. Minimum:
- Factory state: `User::factory()->linkedAsPartnerOf($owner)`
- Feature tests: middleware injects correct ID, partner sees owner's data, revoke immediately blocks access, queued job carries correct user
- Regression suite for every model on the affected list

### 17. Telemetry

Track funnel: invites sent, accepted, expired, revoked. Time-to-accept. Drives product decisions (e.g., is 7-day TTL right?).

### 18. Privacy disclosure on accept page

Accept page should list what partner is about to gain access to (financial data, guest PII, photos). Explicit consent checkbox before linking. Lower legal/UX risk.

### 19. Admin impersonation interaction

If admin tools support "login as user," `effectiveUser()` must be impersonation-aware to avoid resolving to the impersonated user's partner unexpectedly.

### 20. i18n consistency

All UI text in spec is Bahasa Indonesia. Confirm project uses translation files (`__('couple.banner')`) or hardcoded strings — match existing convention.

---

## Recommended Action Plan

1. **Before implementation:** close items 1, 2, 3, 4, 5 (critical) in spec revision
2. **During implementation:** wire 6–14 (important) as TODOs with tickets
3. **Polish phase:** 15–20

Once critical items resolved, spec is ready to execute.
