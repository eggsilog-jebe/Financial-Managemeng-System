---
name: hims-fms-database
description: >-
  Use this skill whenever designing, migrating, indexing, querying, optimizing, or refactoring
  the database, Eloquent models, migrations, or query pipelines for the Hospital Financial
  Management System (FMS) and HIMS.
---

# Hospital Financial Management System (FMS) — Database Engineering Skill

This skill governs all database schema design, migrations, indexing strategies, Eloquent models, concurrency control, and query optimization for the integrated **Hospital Information Management System (HIMS)** and **Financial Management System (FMS)** on Laravel and MySQL/PostgreSQL.

---

## 1. Non-Negotiable Financial Data Integrity Rules

### A. Zero Floats — Strict Decimal(15, 4) Invariance
1. **Database Schema:** Every monetary, rate, and currency balance column **MUST** be defined as `DECIMAL(15, 4)`:
   ```php
   $table->decimal('total_amount', 15, 4)->default(0.0000);
   $table->decimal('debit', 15, 4)->default(0.0000);
   $table->decimal('credit', 15, 4)->default(0.0000);
   ```
2. **Eloquent Model Casts:** Every monetary attribute **MUST** be cast as `'decimal:4'`:
   ```php
   protected function casts(): array
   {
       return [
           'total_amount'    => 'decimal:4',
           'patient_payable' => 'decimal:4',
           'paid_amount'     => 'decimal:4',
       ];
   }
   ```
3. **Arithmetic Engine:** NEVER use PHP native float operators (`+`, `-`, `*`, `/`) for currency. Strictly use PHP's `bcmath` extension (`bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp`) with scale `4`:
   ```php
   $vatExempt = bcdiv($gross, '1.1200', 4);
   $discount  = bcmul($vatExempt, '0.2000', 4);
   $balance   = bcsub($gross, bcadd($discount, $coverage, 4), 4);
   ```

### B. Immutability of Posted Financial Records
- Once a `JournalEntry`, `Invoice`, `OfficialReceipt`, or `DisbursementVoucher` is marked `POSTED` or `SETTLED`, it **CANNOT** be updated or deleted.
- Adjustments and corrections **MUST** be executed through compensating reversal entries (`REVERSED` status + new reversing transaction).
- Protect models with Eloquent lifecycle guards in `booted()`:
  ```php
  protected static function booted(): void
  {
      static::updating(function (JournalEntry $entry): void {
          if ($entry->getOriginal('status') === 'POSTED') {
              $isReversal = $entry->isDirty('status') && $entry->status === 'REVERSED';
              if (! $isReversal) {
                  throw new DomainException("Posted record [{$entry->reference_number}] is immutable and cannot be updated.");
              }
          }
      });

      static::deleting(function (JournalEntry $entry): void {
          if ($entry->status === 'POSTED') {
              throw new DomainException("Posted record [{$entry->reference_number}] cannot be deleted. Issue a reversal instead.");
          }
      });
  }
  ```

---

## 2. Core Schema Domains & Entity Mappings

```
  ┌────────────────────────────────────────────────────────┐
  │                   CHART OF ACCOUNTS                    │
  │                     `accounts`                         │
  └──────────────────────────┬─────────────────────────────┘
                             │ 1:N
  ┌──────────────────────────▼─────────────────────────────┐
  │                  GENERAL LEDGER                        │
  │    `journal_entries` 1:N `journal_entry_lines`         │
  └───────────────▲──────────────────────────▲─────────────┘
                  │                          │
        ┌─────────┴─────────┐      ┌─────────┴─────────┐
        │ ACCOUNTS PAYABLE  │      │ACCOUNTS RECEIVABLE│
        │ `vendors`         │      │ `patient_accounts`│
        │ `purchase_bills`  │      │ `invoices`        │
        │ `three_way_matches│      │ `invoice_items`   │
        └─────────▲─────────┘      └─────────▲─────────┘
                  │                          │
        ┌─────────┴─────────┐      ┌─────────┴─────────┐
        │   DISBURSEMENT    │      │    COLLECTION     │
        │ `disbursement_v`  │      │ `cashier_shifts`  │
        │ `check_registers` │      │ `payments`        │
        │ `bank_accounts`   │      │ `official_receipts│
        └───────────────────┘      └───────────────────┘
```

### Table Reference Map
| Domain | Primary Tables | Foreign Key Strategy |
| :--- | :--- | :--- |
| **General Ledger** | `accounts`, `journal_entries`, `journal_entry_lines`, `fiscal_periods` | `restrictOnDelete()` on `account_id` to protect audit trails |
| **Accounts Payable** | `vendors`, `purchase_bills`, `bill_items`, `three_way_matches` | `cascadeOnDelete()` on items, `restrictOnDelete()` on vendors |
| **Accounts Receivable** | `patient_accounts`, `invoices`, `invoice_items`, `credit_notes` | `restrictOnDelete()` on patient accounts and posted invoices |
| **Healthcare Public Payers** | `philhealth_claims`, `guarantee_letters`, `statutory_discounts` | Indexed by `patient_account_id` and `invoice_id` |
| **Cashier & Collections** | `cashier_shifts`, `payments`, `official_receipts`, `bank_deposits` | Unique sequential indexes on `or_number` and `shift_id` |
| **Banking & Treasury** | `bank_accounts`, `bank_reconciliations`, `bank_statement_lines`, `fund_transfers` | B-tree index on `account_number` and statement dates |
| **Budgets & Encumbrances** | `budget_allocations`, `budget_encumbrances`, `budget_reallocations` | Pessimistic locking on balance updates |
| **Statutory & Audit** | `cas_audit_trails`, `activity_logs`, `bir2307_certificates` | Cryptographic SHA-256 hash chaining, immutable append-only |

---

## 3. Migration Blueprint Standard

Every migration file must strictly declare `strict_types=1`, use foreign key constraints with sensible deletion rules, and add composite B-Tree indexes for high-frequency queries.

### Production Migration Template:
```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('patient_account_id')
                ->constrained('patient_accounts')
                ->restrictOnDelete(); // Never orphan financial histories
            
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            
            // Monetary values in DECIMAL(15, 4)
            $table->decimal('gross_amount', 15, 4)->default(0.0000);
            $table->decimal('statutory_discount', 15, 4)->default(0.0000); // RA 9994 / RA 10754
            $table->decimal('insurance_covered', 15, 4)->default(0.0000);  // PhilHealth + HMO
            $table->decimal('patient_payable', 15, 4)->default(0.0000);    // Out-of-pocket balance
            $table->decimal('paid_amount', 15, 4)->default(0.0000);
            
            // Status with default and index
            $table->enum('status', ['DRAFT', 'UNPAID', 'PARTIAL', 'SETTLED', 'CANCELLED'])
                ->default('UNPAID')
                ->index();
                
            $table->string('department', 50)->nullable()->index();
            $table->timestamps();

            // Covering index for AR Aging & Patient Balance queries
            $table->index(['patient_account_id', 'status'], 'idx_inv_patient_status');
            $table->index(['status', 'invoice_date', 'patient_payable'], 'idx_inv_status_date_payable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_invoices');
    }
};
```

---

## 4. Eloquent Model Architecture Pattern

All FMS models must be declared `final`, use typed properties, protected `$fillable`, explicit `$casts`, and typed Eloquent relationships.

### Production Model Blueprint:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'patient_account_id',
        'invoice_date',
        'due_date',
        'gross_amount',
        'statutory_discount',
        'insurance_covered',
        'patient_payable',
        'paid_amount',
        'status',
        'department',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date'       => 'date',
            'due_date'           => 'date',
            'gross_amount'       => 'decimal:4',
            'statutory_discount' => 'decimal:4',
            'insurance_covered'  => 'decimal:4',
            'patient_payable'    => 'decimal:4',
            'paid_amount'        => 'decimal:4',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Invoice $invoice): void {
            if ($invoice->getOriginal('status') === 'SETTLED' && $invoice->isDirty(['gross_amount', 'patient_payable'])) {
                throw new DomainException("Settled invoice [{$invoice->invoice_number}] cannot modify financial amounts.");
            }
        });
    }

    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @param Builder<Invoice> $query */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['UNPAID', 'PARTIAL']);
    }

    /** @param Builder<Invoice> $query */
    public function scopeSettled(Builder $query): Builder
    {
        return $query->where('status', 'SETTLED');
    }
}
```

---

## 5. Concurrency, Pessimistic Locking & Atomic Transactions

In high-concurrency healthcare environments (e.g. multiple cashier windows settling bills for the same patient simultaneously), race conditions cause double-crediting or ledger desync.

### Mandatory Concurrency Pattern:
1. Always wrap multi-row mutations inside `DB::transaction()`.
2. Always apply `lockForUpdate()` on balance-sensitive or state-sensitive rows.

```php
use App\Models\CashierShift;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

public function processPayment(PaymentDto $dto): Payment
{
    return DB::transaction(function () use ($dto): Payment {
        // 1. Lock invoice row to prevent concurrent settlements
        $invoice = Invoice::where('id', $dto->invoiceId)
            ->lockForUpdate()
            ->firstOrFail();

        // 2. Lock cashier shift to prevent drawer balance collisions
        $shift = CashierShift::where('id', $dto->shiftId)
            ->lockForUpdate()
            ->firstOrFail();

        // 3. Precision balance validation using BCMath
        if (bccomp($dto->amount, $invoice->patient_payable, 4) > 0) {
            throw new DomainException("Payment amount exceeds remaining patient payable balance.");
        }

        // 4. Record payment mutation
        $payment = Payment::create([
            'invoice_id'       => $invoice->id,
            'cashier_shift_id' => $shift->id,
            'amount'           => $dto->amount,
            'payment_method'   => $dto->paymentMethod,
            'or_number'        => $dto->orNumber,
            'payment_date'     => now(),
        ]);

        // 5. Update invoice state
        $newPaidAmount = bcadd((string) $invoice->paid_amount, (string) $dto->amount, 4);
        $remaining     = bcsub((string) $invoice->patient_payable, (string) $dto->amount, 4);
        
        $invoice->update([
            'paid_amount'     => $newPaidAmount,
            'patient_payable' => $remaining,
            'status'          => bccomp($remaining, '0.0000', 4) === 0 ? 'SETTLED' : 'PARTIAL',
        ]);

        // 6. Update shift drawer totals
        $shift->incrementDrawer((string) $dto->amount);

        return $payment;
    });
}
```

### Deadlock Prevention Rules:
- **Lock Ordering:** If acquiring multiple locks, always acquire in deterministic alphabetical/entity order (e.g., `PatientAccount` -> `Invoice` -> `CashierShift`).
- **Short Transactions:** Keep third-party HTTP calls, PDF rendering, or email dispatching **OUTSIDE** `DB::transaction()`. Only execute database reads and writes inside the transaction block.

---

## 6. Query Optimization & Preventing N+1 Queries

### A. Eager Loading
Never access relationships in loops or Blade views without eager loading:
```php
// WRONG: Triggers N+1 database queries
$invoices = Invoice::latest()->take(50)->get();
foreach ($invoices as $inv) {
    echo $inv->patientAccount->full_name; // Query on every iteration!
}

// CORRECT: Exactly 2 optimized queries
$invoices = Invoice::with(['patientAccount', 'items'])
    ->latest('invoice_date')
    ->take(50)
    ->get();
```

### B. High-Volume Batch Chunking
When calculating AR aging buckets, vendor balance recalculations, or closing fiscal periods, never load thousands of records into memory with `->get()`:
```php
// WRONG: Exhausts PHP memory limit
$allEntries = JournalEntryLine::where('account_id', $accountId)->get();

// CORRECT: Streams memory-efficient 250-row chunks
JournalEntryLine::where('account_id', $accountId)
    ->chunkById(250, function ($lines): void {
        foreach ($lines as $line) {
            // Process aggregation
        }
    });
```

### C. Covering & Composite Indexes
Always include composite indexes for columns frequently filtered or sorted together:
```php
// 1. Account balance aggregation (avoids table heap scans)
$table->index(['account_id', 'journal_entry_id', 'debit', 'credit'], 'idx_jel_acc_entry_amounts');

// 2. Journal browser filtering
$table->index(['status', 'entry_date', 'id'], 'idx_je_status_date_id');

// 3. Cashier desk active shifts
$table->index(['user_id', 'status', 'opened_at'], 'idx_cs_user_status_opened');
```

---

## 7. Bureau of Internal Revenue (BIR) CAS Audit Trail Standard

Under Philippine BIR Computerized Accounting System (CAS) regulations, all financial transactions must be recorded with tamper-evident cryptographic hash chaining.

Whenever a service mutates a financial table, invoke `CasAuditTrailService`:
```php
$auditTrailService->logFinancialEvent(
    auditable: $journalEntry,
    action: 'POST',
    oldValues: ['status' => 'DRAFT'],
    newValues: ['status' => 'POSTED', 'posted_by' => auth()->id()],
    userId: auth()->id(),
    userName: auth()->user()?->name ?? 'System',
    ipAddress: request()->ip()
);
```

### Hash Chaining Schema Reference:
```php
Schema::create('cas_audit_trails', function (Blueprint $table): void {
    $table->id();
    $table->uuid('event_uuid')->unique();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('user_name', 100);
    $table->string('ip_address', 45);
    $table->string('auditable_type');
    $table->unsignedBigInteger('auditable_id');
    $table->string('action', 30); // INSERT, UPDATE, POST, REVERSE, VOID
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->char('previous_hash', 64); // SHA-256 link to prior record
    $table->char('record_hash', 64);   // SHA-256 of current payload + previous_hash
    $table->timestamp('created_at')->index();
});
```

---

## 8. Database Engineering Checklist

Before submitting any database-related changes:
- [ ] Every financial amount column is defined as `DECIMAL(15, 4)` in migration.
- [ ] Every monetary model attribute is cast as `'decimal:4'`.
- [ ] No native PHP floats are used for arithmetic; `bcmath` functions used with scale `4`.
- [ ] Foreign keys on financial masters use `restrictOnDelete()` to prevent accidental ledger deletion.
- [ ] Multi-table ledger writes and balance adjustments are wrapped in `DB::transaction()`.
- [ ] Pessimistic locking (`lockForUpdate()`) is applied to balance-sensitive models.
- [ ] Eager loading (`with()` / `loadMissing()`) is used to prevent N+1 queries.
- [ ] Composite indexes are present for columns filtered together in high-traffic queries.
- [ ] Immutability lifecycle hooks prevent modifying or deleting `POSTED` transactions.
- [ ] Migration passes cleanly with `php artisan migrate:status`.
