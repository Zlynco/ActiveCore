<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class' => $this->class->name,
            'member' => $this->member->name,
            'booking_date' => $this->booking_date,
            'booking_code' => $this->booking_code,
            'amount' => $this->amount,
            'paid' => $this->paid,
            'scanned' => $this->scanned,
        ];
    }
}
