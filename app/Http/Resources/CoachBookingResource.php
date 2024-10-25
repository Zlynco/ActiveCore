<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachBookingResource extends JsonResource
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
            'coach' => $this->coach->name,
            'member' => $this->member->name,
            'session_count' => $this->session_count,
            'booking_date' => $this->booking_date,
            'start_booking_time' => $this->start_booking_time,
            'end_booking_time' => $this->end_booking_time,
            'booking_code' => $this->booking_code,
            'payment_required' => $this->payment_required,
        ];
    }
}
