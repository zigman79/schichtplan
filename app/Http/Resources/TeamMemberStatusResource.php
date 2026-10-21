<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var User $this */
        $todayArbeitszeit = $this->arbeitszeiten->first();

        $isLoggedIn = false;
        $isOnBreak = false;
        $loginTime = null;
        $logoutTime = null;
        $breaks = [];

        if ($todayArbeitszeit) {
            $isLoggedIn = $todayArbeitszeit->beginn_bestaetigt && !$todayArbeitszeit->ende_bestaetigt;
            $loginTime = $todayArbeitszeit->beginn;
            $logoutTime = $todayArbeitszeit->ende_bestaetigt ? $todayArbeitszeit->ende : null;

            $breaks = $todayArbeitszeit->pausen->map(function ($pause) {
                $isOngoing = $pause->ende === null;
                return [
                    'id' => $pause->id,
                    'start' => $pause->beginn,
                    'end' => $pause->ende,
                    'is_ongoing' => $isOngoing,
                    'duration_minutes' => $pause->ende
                        ? round($pause->calculatePausenzeit() * 60)
                        : null,
                ];
            });

            $lastPause = $todayArbeitszeit->pausen->sortByDesc('id')->first();
            $isOnBreak = $lastPause && $lastPause->ende === null;
        }

        return [
            'id' => $this->id,
            'name' => $this->short_name,
            'is_logged_in' => $isLoggedIn,
            'is_on_break' => $isOnBreak,
            'login_time' => $loginTime,
            'logout_time' => $logoutTime,
            'breaks' => $breaks,
        ];
    }
}
