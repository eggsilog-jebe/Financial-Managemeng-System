<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\SystemAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SystemAlertController extends Controller
{
    public function __construct(
        private readonly SystemAlertService $alertService,
    ) {}

    /**
     * Poll live system alerts feed.
     */
    public function feed(Request $request): JsonResponse
    {
        $acknowledgedAt = $request->session()->get('security_alert_acknowledged_at');
        $data = $this->alertService->getLiveAlertsForUser($request->user(), $acknowledgedAt);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Acknowledge/dismiss system alerts for current user session.
     */
    public function acknowledge(Request $request): JsonResponse|RedirectResponse
    {
        $request->session()->put('security_alert_acknowledged_at', now()->toIso8601String());

        $data = $this->alertService->getLiveAlertsForUser($request->user(), now()->toIso8601String());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Security alert acknowledged.',
                'data'    => $data,
            ]);
        }

        return back()->with('success', 'Security alert acknowledged.');
    }
}
