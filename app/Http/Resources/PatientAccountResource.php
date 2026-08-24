<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PatientAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'patient_id_number'  => $this->patient_id_number,
            'full_name'          => $this->full_name,
            'admission_type'     => $this->admission_type,
            'hmo_provider'       => $this->hmo_provider,
            'total_billed'       => (float) $this->total_billed,
            'current_balance'    => (float) $this->current_balance,
            'status'             => $this->status,
        ];
    }
}
