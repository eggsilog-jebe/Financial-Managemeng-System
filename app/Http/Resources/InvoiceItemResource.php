<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'item_code'              => $this->item_code,
            'description'            => $this->description,
            'department'             => $this->department,
            'revenue_category'       => $this->revenue_category,
            'quantity'               => (float) $this->quantity,
            'unit_price'             => (float) $this->unit_price,
            'gross_amount'           => (float) $this->gross_amount,
            'is_vatable'             => $this->is_vatable,
            'is_senior_pwd_eligible' => $this->is_senior_pwd_eligible,
        ];
    }
}
