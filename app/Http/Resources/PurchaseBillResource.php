<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PurchaseBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'bill_number'     => $this->bill_number,
            'vendor_id'       => $this->vendor_id,
            'bill_date'       => $this->bill_date->toDateString(),
            'due_date'        => $this->due_date->toDateString(),
            'total_amount'    => (float) $this->total_amount,
            'paid_amount'     => (float) $this->paid_amount,
            'status'          => $this->status,
            'vendor'          => $this->whenLoaded('vendor'),
            'items'           => $this->whenLoaded('items'),
            'three_way_match' => $this->whenLoaded('threeWayMatch'),
            'bir_certificate' => $this->whenLoaded('birCertificate'),
        ];
    }
}
