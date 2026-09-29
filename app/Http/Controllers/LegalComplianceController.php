<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class LegalComplianceController extends Controller
{
    /**
     * Display the System Terms & Conditions.
     */
    public function terms(): View
    {
        return view('legal.terms');
    }

    /**
     * Display the Hospital Privacy Policy.
     */
    public function privacy(): View
    {
        return view('legal.privacy');
    }
}
