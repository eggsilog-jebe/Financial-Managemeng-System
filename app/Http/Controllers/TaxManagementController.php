<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Tax\FileTaxReturnRequest;
use App\Http\Requests\Tax\StoreTaxRuleRequest;
use App\Models\Bir2307Certificate;
use App\Models\CasAuditTrail;
use App\Models\DisbursementVoucher;
use App\Models\OfficialReceipt;
use App\Models\PurchaseBill;
use App\Models\StatutoryDiscount;
use App\Models\TaxCertificate;
use App\Models\TaxReturn;
use App\Models\TaxRule;
use App\Services\Accounting\BirTaxScheduleService;
use App\Services\Accounting\CasAuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class TaxManagementController extends Controller
{
    public function __construct(
        private readonly BirTaxScheduleService $taxScheduleService,
        private readonly CasAuditTrailService $auditTrailService
    ) {}

    public function taxConfiguration(): View
    {
        $taxRules = TaxRule::orderBy('tax_code')->get();

        return view('tax.tax-configuration', compact('taxRules'));
    }

    public function storeTaxRule(StoreTaxRuleRequest $request): RedirectResponse
    {
        $taxRule = TaxRule::create([
            'tax_code' => $request->validated('tax_code'),
            'name'     => $request->validated('name'),
            'atc_code' => $request->validated('atc_code'),
            'category' => $request->validated('category'),
            'cat_type' => $request->validated('cat_type'),
            'rate'     => number_format((float) $request->validated('rate'), 4, '.', ''),
            'scope'    => $request->validated('scope'),
            'status'   => 'Active',
        ]);

        $this->auditTrailService->logFinancialEvent(
            $taxRule,
            'INSERT',
            null,
            $taxRule->toArray(),
            $request->user()?->id ?? 1,
            $request->user()?->name ?? 'System Officer',
            $request->ip() ?? '127.0.0.1'
        );

        return redirect()->route('tax.tax-config')->with('success', "Tax rule {$taxRule->tax_code} configured successfully.");
    }

    public function toggleTaxRule(int $id, Request $request): RedirectResponse
    {
        $taxRule = TaxRule::findOrFail($id);
        $oldValues = $taxRule->toArray();

        $newStatus = $taxRule->status === 'Active' ? 'Inactive' : 'Active';
        $taxRule->update(['status' => $newStatus]);

        $this->auditTrailService->logFinancialEvent(
            $taxRule,
            'UPDATE',
            $oldValues,
            $taxRule->toArray(),
            $request->user()?->id ?? 1,
            $request->user()?->name ?? 'System Officer',
            $request->ip() ?? '127.0.0.1'
        );

        return redirect()->route('tax.tax-config')->with('success', "Tax rule {$taxRule->tax_code} status set to {$newStatus}.");
    }

    /**
     * BIR Form 2307 — Certificates of Creditable Tax Withheld at Source.
     * Loads from `bir_2307_certificates` (procurement & doctor EWT withholdings).
     */
    public function withholdingTax(): View
    {
        $certificates = Bir2307Certificate::with(['vendor', 'purchaseBill', 'doctorProfile'])
            ->latest()
            ->get();

        return view('tax.withholding-tax', compact('certificates'));
    }

    /**
     * BIR Statutory Tax Returns & Filing Workstation.
     * Integrates live computation schedules from BirTaxScheduleService:
     * - Form 1601-EQ (Expanded Withholding Tax)
     * - Form 1601-C (Compensation Withholding Tax)
     * - Form 2550Q (Quarterly Value-Added Tax)
     */
    public function taxReturns(Request $request): View
    {
        $year = (string) ($request->query('year') ?: date('Y'));
        $month = $request->query('month') ? str_pad((string) $request->query('month'), 2, '0', STR_PAD_LEFT) : date('m');
        $quarter = (int) ($request->query('quarter') ?: ceil((int) date('n') / 3));

        $qStartMonth = str_pad((string) (($quarter - 1) * 3 + 1), 2, '0', STR_PAD_LEFT);
        $qEndMonth = str_pad((string) ($quarter * 3), 2, '0', STR_PAD_LEFT);
        $fromDate = "{$year}-{$qStartMonth}-01";
        $toDate = date('Y-m-t', strtotime("{$year}-{$qEndMonth}-01"));

        $ewt1601eq = $this->taxScheduleService->getBir1601EQSummary($fromDate, $toDate);
        $comp1601c = $this->taxScheduleService->getBir1601CSummary($year, $month);
        $vat2550q  = $this->taxScheduleService->getBirVatSummary($fromDate, $toDate);

        $returns = TaxReturn::latest('filing_date')->get();

        return view('tax.tax-returns', compact(
            'returns',
            'ewt1601eq',
            'comp1601c',
            'vat2550q',
            'year',
            'month',
            'quarter'
        ));
    }

    public function storeTaxReturn(FileTaxReturnRequest $request): RedirectResponse
    {
        $year = date('Y');
        $nextSeq = TaxReturn::count() + 1;
        $returnNumber = 'TR-' . $year . '-' . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);

        $taxReturn = TaxReturn::create([
            'return_number'  => $returnNumber,
            'form_type'      => $request->validated('form_type'),
            'period_covered' => $request->validated('period_covered'),
            'tax_due'        => number_format((float) $request->validated('tax_due'), 4, '.', ''),
            'status'         => 'FILED',
            'filing_date'    => $request->validated('filing_date'),
        ]);

        $this->auditTrailService->logFinancialEvent(
            $taxReturn,
            'POST',
            null,
            $taxReturn->toArray(),
            $request->user()?->id ?? 1,
            $request->user()?->name ?? 'System Officer',
            $request->ip() ?? '127.0.0.1'
        );

        return redirect()->route('tax.tax-returns')->with('success', "Statutory return {$taxReturn->return_number} filed successfully.");
    }

    public function markReturnPaid(int $id, Request $request): RedirectResponse
    {
        $taxReturn = TaxReturn::findOrFail($id);

        if ($taxReturn->status === 'PAID') {
            return redirect()->route('tax.tax-returns')->with('error', "Tax return {$taxReturn->return_number} is already marked as PAID.");
        }

        $oldValues = $taxReturn->toArray();
        $taxReturn->update(['status' => 'PAID']);

        $this->auditTrailService->logFinancialEvent(
            $taxReturn,
            'UPDATE',
            $oldValues,
            $taxReturn->toArray(),
            $request->user()?->id ?? 1,
            $request->user()?->name ?? 'System Officer',
            $request->ip() ?? '127.0.0.1'
        );

        return redirect()->route('tax.tax-returns')->with('success', "Tax return {$taxReturn->return_number} remitted and marked as PAID.");
    }

    /**
     * Senior / PWD & Healthcare Statutory Tax Exemptions Register.
     */
    public function taxExemptions(): View
    {
        $seniorDiscounts = StatutoryDiscount::where('discount_type', 'SENIOR')->get();
        $pwdDiscounts = StatutoryDiscount::where('discount_type', 'PWD')->get();
        $totalVatExemptSales = OfficialReceipt::where('status', 'VALID')->sum('vat_exempt_sales') ?? '0.0000';

        $certificates = TaxCertificate::latest()->get();

        $exemptions = collect([
            [
                'name'   => 'RA 11534 (CREATE Act - Essential Medicines)',
                'basis'  => 'Section 109(BB) NIRC, as amended by CREATE Act',
                'ref'    => 'BIR-CERT-CREATE-MED',
                'cat'    => 'meds',
                'gross'  => '₱' . number_format((float) $totalVatExemptSales, 2),
                'saved'  => '₱' . number_format((float) bcmul((string) $totalVatExemptSales, '0.12', 2), 2),
                'status' => 'Enforced',
            ],
            [
                'name'   => 'RA 9994 (Expanded Senior Citizens Act)',
                'basis'  => '20% Discount and VAT Exemption on Medical & Hospital Care',
                'ref'    => 'BIR-CERT-RA9994-SNR',
                'cat'    => 'senior',
                'gross'  => '₱' . number_format((float) $seniorDiscounts->sum('vat_exempt_amount'), 2),
                'saved'  => '₱' . number_format((float) $seniorDiscounts->sum('discount_amount'), 2),
                'status' => 'Enforced',
            ],
            [
                'name'   => 'RA 10754 (Persons with Disability Act)',
                'basis'  => '20% Discount and VAT Exemption on Hospital Services',
                'ref'    => 'BIR-CERT-RA10754-PWD',
                'cat'    => 'senior',
                'gross'  => '₱' . number_format((float) $pwdDiscounts->sum('vat_exempt_amount'), 2),
                'saved'  => '₱' . number_format((float) $pwdDiscounts->sum('discount_amount'), 2),
                'status' => 'Enforced',
            ],
            [
                'name'   => 'NIRC Section 109(G) Healthcare Services Exemption',
                'basis'  => 'Hospital, Dental, and Medical Professional Services',
                'ref'    => 'BIR-CERT-SEC109G',
                'cat'    => 'meds',
                'gross'  => '₱' . number_format((float) $totalVatExemptSales, 2),
                'saved'  => '₱' . number_format((float) bcmul((string) $totalVatExemptSales, '0.12', 2), 2),
                'status' => 'Enforced',
            ],
        ]);

        return view('tax.tax-exemptions', compact('exemptions', 'certificates'));
    }

    public function taxAuditTrail(): View
    {
        $taxRules     = TaxRule::orderBy('tax_code')->get();
        $certificates = TaxCertificate::latest()->get();

        $logs = CasAuditTrail::with('user')
            ->where(function ($q) {
                $q->whereIn('auditable_type', [
                    TaxReturn::class,
                    TaxRule::class,
                    Bir2307Certificate::class,
                    TaxCertificate::class,
                    PurchaseBill::class,
                    OfficialReceipt::class,
                    DisbursementVoucher::class,
                ])
                ->orWhere('action', 'LIKE', '%TAX%')
                ->orWhere('action', 'LIKE', '%RETURN%')
                ->orWhere('action', 'LIKE', '%2307%')
                ->orWhere('action', 'LIKE', '%VAT%');
            })
            ->latest('id')
            ->limit(100)
            ->get();

        return view('tax.tax-audit-trail', compact('taxRules', 'certificates', 'logs'));
    }
}
