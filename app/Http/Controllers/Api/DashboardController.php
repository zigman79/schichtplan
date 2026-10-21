<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobGroupShiftResource;
use App\Http\Resources\TeamMemberStatusResource;
use App\Models\JobGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get team members' login status for today
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function teamStatus(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isArbeitszeitenTeamleader()) {
            return response()->json([
                'message' => 'Sie haben keine Berechtigung, Teammitglieder zu sehen.',
            ], 403);
        }

        $teamMembers = $user->arbeitszeitenTeam()
            ->with([
                'arbeitszeiten' => function ($query) {
                    $query->where('tag', now()->format('Y-m-d'));
                },
                'arbeitszeiten.pausen'
            ])
            ->get();

        if ($teamMembers->isEmpty()) {
            return response()->json([
                'message' => 'Keine Teammitglieder zugeordnet.',
                'team_members' => [],
                'date' => now()->format('Y-m-d'),
                'date_formatted' => now()->format('d.m.Y'),
            ], 200);
        }

        return response()->json([
            'team_members' => TeamMemberStatusResource::collection($teamMembers),
            'date' => now()->format('Y-m-d'),
            'date_formatted' => now()->format('d.m.Y'),
        ], 200);
    }

    /**
     * Get shifts for a specific job group
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jobGroupShifts(Request $request): JsonResponse
    {
        $request->validate([
            'job_group_id' => ['required', 'integer', 'exists:job_groups,id'],
        ]);

        $jobGroup = JobGroup::with([
            'users',
            'shifts' => function ($query) {
                $query->where('shift_date', '>=', now()->format('Y-m-d'))
                    ->orderBy('shift_date', 'asc')
                    ->orderBy('start_time', 'asc');
            },
            'shifts.users',
            'shifts.unavailableUsers'
        ])->findOrFail($request->input('job_group_id'));

        $totalUsersInGroup = $jobGroup->users->count();

        $shifts = $jobGroup->shifts->map(function ($shift) use ($totalUsersInGroup) {
            $enrolledCount = $shift->users->count();
            $declinedCount = $shift->unavailableUsers->count();
            $noResponseCount = $totalUsersInGroup - $enrolledCount - $declinedCount;

            return [
                'id' => $shift->id,
                'shift_date' => $shift->shift_date->format('Y-m-d'),
                'shift_date_formatted' => $shift->shift_date->format('d.m.Y'),
                'start_time' => $shift->start_time->format('H:i'),
                'end_time' => $shift->end_time->format('H:i'),
                'admin_comment' => $shift->admin_comment,
                'required_employees' => $shift->required_employees,
                'enrolled_count' => $enrolledCount,
                'declined_count' => $declinedCount,
                'no_response_count' => max(0, $noResponseCount),
                'available_slots' => $shift->available_slots,
                'is_full' => $shift->is_full,
                'enrolled_users' => $shift->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->short_name,
                        'comment' => $user->pivot->user_comment,
                        'enrolled_at' => $user->pivot->created_at?->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        });

        return response()->json([
            'job_group' => [
                'id' => $jobGroup->id,
                'name' => $jobGroup->name,
                'total_users' => $totalUsersInGroup,
            ],
            'shifts' => $shifts,
        ], 200);
    }
}
