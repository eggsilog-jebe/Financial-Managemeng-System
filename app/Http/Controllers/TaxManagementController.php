<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bir2307Certificate;
use App\Models\TaxCertificate;
use App\Models\TaxReturn;
use App\Models\TaxRule;
use Illuminate\Contracts\View\View;

final class TaxManagementController extends Controller
{
    public function taxConfiguration(): View
    {
        $taxRules = TaxRule::orderBy('tax_code')->get();
        return view('tax.tax-configuration', compact('taxRules'));
    }

    /**
     * BIR Form 2307 — Certificates of Creditable Tax Withheld at Source.
     * Loads from `bir_2307_certificates` (procurement EWT withholdings),
     * NOT from `tax_certificates` (doctor/payee one-off certificates).
     */
    public function withholdingTax(): View
    {
        $certificates = Bir2307Certificate::with(['vendor', 'purchaseBill'])
            ->latest()
            ->get();

        return view('tax.withholding-tax', compact('certificates'));
    }

    public function taxReturns(): View
    {
        $returns = TaxReturn::latest()->get();
        return view('tax.tax-returns', compact('returns'));
    }

    /**
     * Senior / PWD Tax Exemptions — uses TaxCertificate (doctor/payee one-off 2307s).
     */
    public function taxExemptions(): View
    {
        $certificates = TaxCertificate::latest()->get();
        return view('tax.tax-exemptions', compact('certificates'));
    }

    public function taxAuditTrail(): View
    {
        $taxRules     = TaxRule::all();
        $certificates = TaxCertificate::latest()->get();
        return view('tax.tax-audit-trail', compact('taxRules', 'certificates'));
    }
}

