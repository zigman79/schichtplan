<?php

namespace App\Http\Resources;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobGroupShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Shift $this */
        return [
            'id' => $this->id,
            'shift_date' => $this->shift_date->format('Y-m-d'),
            'shift_date_formatted' => $this->shift_date->format('d.m.Y'),
            'start_time' => $this->start_time->format('H:i'),
            'end_time' => $this->end_time->format('H:i'),
            'admin_comment' => $this->admin_comment,
            'required_employees' => $this->required_employees,
            'enrolled_count' => $this->enrolled_count,
            'available_slots' => $this->available_slots,
            'is_full' => $this->is_full,
            'is_enrolled' => $this->users->contains(auth()->id()),
            'enrolled_users' => $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'comment' => $user->pivot->user_comment,
                    'enrolled_at' => $user->pivot->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
        ];
    }
}
