<?php

declare(strict_types=1);

namespace App\Http\Controllers\GeneralLedger;

use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\JsonResponse;

final class PostJournalEntryController extends Controller
{
    public function __invoke(PostJournalEntryRequest $request, JournalEntryService $service): JsonResponse
    {
        $lines = array_map(
            fn(array $line): JournalLineData => new JournalLineData(
                accountId: (int) $line['account_id'],
                debit: (string) $line['debit'],
                credit: (string) $line['credit'],
                memo: $line['memo'] ?? null
            ),
            $request->validated('lines')
        );

        $dto = new JournalEntryData(
            referenceNumber: $request->validated('reference_number'),
            entryDate: $request->validated('entry_date'),
            description: $request->validated('description'),
            type: $request->validated('type'),
            postedBy: (int) ($request->user()?->id ?? 1),
            lines: $lines
        );

        $entry = $service->createAndPostEntry($dto);

        return response()->json([
            'message' => 'Journal Entry successfully balanced and posted.',
            'data' => new JournalEntryResource($entry),
        ], 201);
    }
}
