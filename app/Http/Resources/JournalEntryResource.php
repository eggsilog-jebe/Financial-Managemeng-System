<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'entry_date' => $this->entry_date->toDateString(),
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'posted_at' => $this->posted_at?->toIso8601String(),
            'lines' => $this->whenLoaded('lines', function () {
                return $this->lines->map(fn($line) => [
                    'id' => $line->id,
                    'account_code' => $line->account->code ?? null,
                    'account_name' => $line->account->name ?? null,
                    'debit' => number_format((float) $line->debit, 4, '.', ''),
                    'credit' => number_format((float) $line->credit, 4, '.', ''),
                    'memo' => $line->memo,
                ]);
            }),
        ];
    }
}
