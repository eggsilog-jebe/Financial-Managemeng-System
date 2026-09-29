---
name: hims-fms-security
description: >-
  Use this skill whenever implementing, auditing, or refactoring security protocols,
  authentication, 2FA TOTP, workstation device binding, session management, RBAC policies,
  financial idempotency, BIR CAS audit trails, or Philippine Data Privacy Act (RA 10173) compliance for FMS.
---

# Hospital Financial Management System (FMS) — Security Engineering Skill

This skill governs all aspects of application security, cryptographic auditing, authentication, terminal authorization, and data privacy compliance for the integrated **Hospital Information Management System (HIMS)** and **Financial Management System (FMS)**.

The security architecture complies with:
- **Philippine Data Privacy Act of 2012 (RA 10173)** for Patient Health & Financial Information (PHI).
- **Bureau of Internal Revenue (BIR) Computerized Accounting System (CAS)** regulations for immutable transaction trails.
- **RFC 6238** for Time-Based One-Time Passwords (TOTP 2FA).

---

## 1. Security Architecture & Threat Model

```
   ┌────────────────────────────────────────────────────────┐
   │            CLIENT / HOSPITAL WORKSTATION               │
   │      Hardware Device UUID • Cookie • Browser IP        │
   └──────────────────────────┬─────────────────────────────┘
                              │
   ┌──────────────────────────▼─────────────────────────────┐
   │             MIDDLEWARE SECURITY PIPELINE               │
   │  1. SecurityHeadersMiddleware (HSTS, CSP, X-Frame)     │
   │  2. IdleSessionTimeout (15-min auto-logout)            │
   │  3. EnforceSingleActiveSession (No concurrent logins)  │
   │  4. WorkstationBinding (Authorized terminal lock)      │
   │  5. EnsureTwoFactorAuthenticated (TOTP RFC 6238)       │
   │  6. MustChangePassword (Mandatory first-login rotate)  │
   │  7. EnsureIdempotency (X-Idempotency-Key lock)         │
   │  8. RoleAuthorization / Policy Gate (RBAC)             │
   └──────────────────────────┬─────────────────────────────┘
                              │
   ┌──────────────────────────▼─────────────────────────────┐
   │             DOMAIN SERVICE & DATA INTEGRITY            │
   │  • Pessimistic Locking (lockForUpdate)                 │
   │  • BCMath Scale 4 Precision                            │
   │  • CasAuditTrailService (SHA-256 Hash Chain)           │
   │  • Immutable Posted Records (booted hooks)             │
   └────────────────────────────────────────────────────────┘
```

---

## 2. The Seven Pillars of FMS Security

### Pillar 1: Workstation Hardware Binding (`UserWorkstation`)
To prevent unauthorized access outside hospital premises or from unapproved personal devices:
- Accounts are locked to **1–3 authorized physical workstations**.
- Workstations are identified via persistent hardware UUIDs stored in secure, HTTP-only cookies (`fms_workstation_token`) and request headers (`X-Workstation-UUID`).
- New or unapproved workstations trigger a pending status requiring **Super Admin authorization** before login is permitted.
- Service: `App\Services\Security\WorkstationBindingService`.

### Pillar 2: Single Active Session Enforcement
- **Rule:** A user account may only have **one** active authenticated session at any time.
- When a valid login occurs from an authorized terminal, any previous active session is immediately terminated (`is_terminated = true`).
- Middleware: `App\Http\Middleware\EnforceSingleActiveSession`.
- Service: `App\Services\Security\ActiveSessionManagerService`.

### Pillar 3: Idle Inactivity Expiration (RA 10173 Compliance)
- To prevent unattended terminal hijacking at cashier desks and nurse stations:
  - Sessions automatically expire after **15 minutes** of inactivity.
  - A client-side warning modal triggers at **5 minutes remaining**, displaying a countdown timer.
  - User activity (mouse movement, keypress) debounces a heartbeat ping to refresh the session window.
- Middleware: `App\Http\Middleware\IdleSessionTimeout`.

### Pillar 4: Two-Factor Authentication (TOTP 2FA)
- Time-Based One-Time Passwords adhering to **RFC 6238**.
- Implemented via `pragmarx/google2fa-laravel` and `bacon/bacon-qr-code`.
- 2FA secret keys are encrypted in the database at rest (`two_factor_secret`).
- Users receive 8 cryptographically secure single-use recovery codes upon enrollment.
- Middleware: `App\Http\Middleware\EnsureTwoFactorAuthenticated`.
- Service: `App\Services\Auth\TwoFactorAuthService`.

### Pillar 5: Financial Mutation Idempotency (`X-Idempotency-Key`)
To prevent catastrophic duplicate settlements, double check issuances, or duplicate journal postings caused by network lag or repeated button clicks:
- All financial mutation endpoints (`POST`, `PUT`, `PATCH`) support the `X-Idempotency-Key` header.
- Middleware acquires an atomic cache lock (`Cache::lock`) for 15 seconds.
- Successful responses are cached for 24 hours. Replayed requests return cached payloads with header: `X-Idempotency-Replay: true`.
- Middleware: `App\Http\Middleware\EnsureIdempotency`.

### Pillar 6: Cryptographic BIR CAS Audit Trail
- All financial mutations (GL entries, vendor bills, cashier receipts, period locks) must emit an immutable audit event via `CasAuditTrailService`.
- **SHA-256 Hash Chaining:** Each record incorporates the `record_hash` of the immediately preceding record, creating a tamper-evident blockchain-style log.
- Model: `App\Models\CasAuditTrail`.
- Service: `App\Services\Accounting\CasAuditTrailService`.

### Pillar 7: HTTP Security Headers & Hardening
Every response from the application passes through `SecurityHeadersMiddleware`:
- `X-Frame-Options: SAMEORIGIN` (prevents clickjacking)
- `X-Content-Type-Options: nosniff` (prevents MIME sniffing)
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` (enforces HTTPS)
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`

---

## 3. Production Security Code Blueprints

### Blueprint 1: Protecting a Financial Mutation with Idempotency & Policies
In Controllers, always authorize with Policies, validate with Form Requests, and enforce idempotency:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\DTOs\Accounting\CreateJournalEntryData;
use App\Http\Requests\Accounting\StoreJournalEntryRequest;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class StoreJournalEntryController
{
    public function __construct(
        private readonly JournalEntryService $service,
    ) {}

    public function __invoke(StoreJournalEntryRequest $request): JsonResponse
    {
        // 1. Authorize via Gate Policy
        $this->authorize('create', JournalEntry::class);

        // 2. Map typed, validated DTO
        $dto = CreateJournalEntryData::fromRequest($request);

        // 3. Execute atomic business transaction
        $entry = $this->service->createDraftEntry($dto);

        return response()->json([
            'message' => 'Journal entry draft created successfully.',
            'data'    => $entry,
        ], Response::HTTP_CREATED);
    }
}
```

---

### Blueprint 2: Strict Financial Resource Policy Pattern
Financial operations must strictly verify roles, period status, and record immutability:

```php
<?php

declare(strict_types=1);

namespace App\Policies\Accounting;

use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class JournalEntryPolicy
{
    /**
     * Determine whether the user can post the journal entry.
     */
    public function post(User $user, JournalEntry $entry): Response
    {
        if (! in_array($user->role, ['ADMIN', 'CHIEF_ACCOUNTANT', 'FINANCE_MANAGER'], true)) {
            return Response::deny('Only senior accounting officers may post journal entries.');
        }

        if ($entry->status !== 'DRAFT') {
            return Response::deny("Only draft entries can be posted. Current status: {$entry->status}.");
        }

        // Verify the fiscal period is not locked
        $isPeriodLocked = FiscalPeriod::where('year', $entry->entry_date->year)
            ->where('month', $entry->entry_date->month)
            ->where('is_locked', true)
            ->exists();

        if ($isPeriodLocked) {
            return Response::deny('The fiscal period for this entry date has been permanently locked by the CFO.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can reverse the journal entry.
     */
    public function reverse(User $user, JournalEntry $entry): Response
    {
        if ($user->role !== 'ADMIN' && $user->role !== 'CHIEF_ACCOUNTANT') {
            return Response::deny('Reversing posted ledger entries requires Chief Accountant authorization.');
        }

        if ($entry->status !== 'POSTED') {
            return Response::deny('Only posted journal entries can be reversed.');
        }

        return Response::allow();
    }
}
```

---

### Blueprint 3: Emitting BIR CAS Cryptographic Audit Logs
Whenever mutating financial state, invoke `CasAuditTrailService`:

```php
use App\Services\Accounting\CasAuditTrailService;

public function postEntry(int $entryId, int $userId): JournalEntry
{
    return DB::transaction(function () use ($entryId, $userId): JournalEntry {
        $entry = JournalEntry::where('id', $entryId)->lockForUpdate()->firstOrFail();

        $oldValues = $entry->toArray();

        $entry->update([
            'status'    => 'POSTED',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);

        // Cryptographic SHA-256 Chained Audit Trail
        $this->casAuditTrailService->logFinancialEvent(
            auditable:  $entry,
            action:     'POST',
            oldValues:  $oldValues,
            newValues:  $entry->fresh()->toArray(),
            userId:     $userId,
            userName:   auth()->user()?->name ?? 'System Officer',
            ipAddress:  request()->ip() ?? '127.0.0.1'
        );

        return $entry;
    });
}
```

---

### Blueprint 4: Workstation Binding Verification
How terminal verification interacts with login requests:

```php
use App\Services\Security\WorkstationBindingService;

public function authenticate(Request $request): Response
{
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages(['email' => __('auth.failed')]);
    }

    // Verify Terminal Hardware Binding
    $deviceUuid = $this->workstationService->resolveDeviceUuid($request);
    $binding    = $this->workstationService->validateDeviceAccess($user, $deviceUuid);

    if ($binding->isPendingApproval()) {
        throw ValidationException::withMessages([
            'workstation' => 'This workstation is not authorized. An approval request has been sent to the IT Administrator.',
        ]);
    }

    Auth::login($user);

    // Enforce Single Active Session
    $this->sessionManager->registerSession($user, session()->getId(), $deviceUuid, $request->ip());

    return redirect()->intended('/dashboard')
        ->withCookie($this->workstationService->createDeviceCookie($deviceUuid));
}
```

---

## 4. Security Implementation Checklist

Before deploying any new module, controller, or financial workflow:

- [ ] **RBAC Authorization:** Controller endpoints check `$this->authorize(...)` or Route middleware includes `can:...` / `role:...`.
- [ ] **Financial Mutation Idempotency:** Critical mutating endpoints accept `X-Idempotency-Key` and run through `EnsureIdempotency` middleware.
- [ ] **Audit Trail Recording:** All create, post, reverse, or cancel actions call `CasAuditTrailService->logFinancialEvent(...)`.
- [ ] **No Raw Credentials in Logs:** Ensure passwords, 2FA secrets, and credit card numbers are stripped from `ActivityLog` and application logs.
- [ ] **Concurrency Row Locks:** Balance mutations use `lockForUpdate()` inside `DB::transaction()` to prevent race conditions.
- [ ] **Zero Floating Point Math:** All financial math uses `bcmath` functions with scale `4`.
- [ ] **Data Privacy (RA 10173):** Patient medical record numbers (MRN), names, and diagnostic billing items are restricted to authorized medical-billing roles.
- [ ] **Workstation Enforced:** Administrative and cashier routes verify authorized terminal binding (`UserWorkstation`).
- [ ] **Automated Tests:** Feature tests cover unauthorized attempts (HTTP 403), concurrent duplicate submission prevention (HTTP 409), and 2FA challenge flows.
