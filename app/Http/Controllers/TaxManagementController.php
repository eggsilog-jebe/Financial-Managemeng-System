<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TaxRule;
use App\Models\TaxCertificate;
use App\Models\TaxReturn;
use Illuminate\Contracts\View\View;

final class TaxManagementController extends Controller
{
    public function taxConfiguration(): View
    {
        $taxRules = TaxRule::orderBy('tax_code')->get();
        return view('tax.tax-configuration', compact('taxRules'));
    }

    public function withholdingTax(): View
    {
        $certificates = TaxCertificate::latest()->get();
        return view('tax.withholding-tax', compact('certificates'));
    }

    public function taxReturns(): View
    {
        $returns = TaxReturn::latest()->get();
        return view('tax.tax-returns', compact('returns'));
    }

    public function taxExemptions(): View
    {
        return view('tax.tax-exemptions');
    }

    public function taxAuditTrail(): View
    {
        return view('tax.tax-audit-trail');
    }
}
