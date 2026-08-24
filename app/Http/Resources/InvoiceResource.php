<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'invoice_number'     => $this->invoice_number,
            'patient_account_id' => $this->patient_account_id,
            'invoice_date'       => $this->invoice_date->toDateString(),
            'total_amount'       => (float) $this->total_amount,
            'insurance_covered'  => (float) $this->insurance_covered,
            'patient_payable'    => (float) $this->patient_payable,
            'status'             => $this->status,
            'patient'            => new PatientAccountResource($this->whenLoaded('patientAccount')),
            'items'              => InvoiceItemResource::collection($this->whenLoaded('items')),
            'philhealth_claim'   => $this->whenLoaded('philhealthClaim'),
            'hmo_claims'         => $this->whenLoaded('hmoClaims'),
            'statutory_discounts'=> $this->whenLoaded('statutoryDiscounts'),
            'created_at'         => $this->created_at?->toIso8601String(),
        ];
    }
}
