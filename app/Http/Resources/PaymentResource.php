<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'payment_reference'       => $this->payment_reference,
            'invoice_id'              => $this->invoice_id,
            'patient_account_id'      => $this->patient_account_id,
            'payment_date'            => $this->payment_date->toDateString(),
            'amount'                  => (float) $this->amount,
            'payment_method'          => $this->payment_method,
            'transaction_channel_ref' => $this->transaction_channel_ref,
            'payment_type'            => $this->payment_type,
            'official_receipt'        => $this->whenLoaded('officialReceipt'),
            'patient'                 => new PatientAccountResource($this->whenLoaded('patientAccount')),
            'invoice'                 => new InvoiceResource($this->whenLoaded('invoice')),
        ];
    }
}
