<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Ingestion\IngestBdmsPatientBillController;
use App\Http\Controllers\Api\V1\Ingestion\IngestHrmsPayrollRegisterController;
use App\Http\Controllers\Api\V1\Ingestion\IngestPsmVendorBillController;
use App\Http\Controllers\Api\V1\Ingestion\SimulateEncounterBillingApiController;
use App\Http\Middleware\EnsureIdempotency;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/ingest')->middleware([EnsureIdempotency::class])->group(function () {
    // 1. Patient Billing Ingestion (from BDMS / HICS)
    Route::post('/patient-bill', IngestBdmsPatientBillController::class)->name('api.v1.ingest.patient-bill');
    Route::post('/encounter-billing', SimulateEncounterBillingApiController::class)->name('api.v1.ingest.encounter-billing');

    // 2. Supply Chain Bill Ingestion (from PSM / SWS)
    Route::post('/vendor-bill', IngestPsmVendorBillController::class)->name('api.v1.ingest.vendor-bill');

    // 3. Payroll Ingestion (from HRMS)
    Route::post('/payroll-register', IngestHrmsPayrollRegisterController::class)->name('api.v1.ingest.payroll-register');
});
