<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
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
            'name' => $this->name,
            'description' =>$this->description,
            'day_of_week' =>$this->day_of_week,
            'date' =>$this->date,
            'start_time' =>$this->start_time,
            'end_time' =>$this->end_time,
            'price' =>$this->price,
            'coach' =>$this->coach->name,
            'category' =>$this->category->name,
            'quota' =>$this->quota,
            'room' =>$this->room->name,
            'registered_count' =>$this->registered_count,
        ];
    }
}
