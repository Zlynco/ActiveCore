<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'class' => $this->class->name ?? 'No Class',
            'coach' => $this->coach->name,
            'attendance_date' => $this->attendance_date,
            'status' => $this->status,
            'check in' => $this->check_in,
            'check out' => $this->check_out,
            'absence reason' => $this->absence_reason,
            'absence code' => $this->unique_code
        ];
    }
}
