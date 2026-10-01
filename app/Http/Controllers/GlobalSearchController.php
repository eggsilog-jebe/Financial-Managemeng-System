<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Accounting\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GlobalSearchController extends Controller
{
    public function __construct(
        private readonly GlobalSearchService $searchService,
    ) {}

    /**
     * Unified Global & Contextual Search Endpoint for HIMS & FMS.
     */
    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');
        $context = $request->query('context');

        $results = $this->searchService->search(
            query: $query,
            currentRoute: is_string($context) ? $context : null
        );

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }
}
