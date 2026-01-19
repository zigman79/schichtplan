<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftEnrollmentController extends Controller
{
    /**
     * Get available shifts for the authenticated user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get job group IDs for the user
        $jobGroupIds = $user->jobGroups->pluck('id');

        if ($jobGroupIds->isEmpty()) {
            return response()->json([
                'message' => 'Sie sind keiner Jobgruppe zugeordnet.',
                'shifts' => [],
            ], 200);
        }

        $query = Shift::with(['jobGroup', 'users', 'unavailableUsers'])
            ->whereIn('job_group_id', $jobGroupIds)
            ->where('shift_date', '>=', now()->format('Y-m-d'))
            ->orderBy('shift_date', 'asc')
            ->orderBy('start_time', 'asc');

        $shifts = $query->get()->map(function ($shift) use ($user) {
            $isEnrolled = $shift->users->contains($user->id);
            $userEnrollment = $isEnrolled ? $shift->users->find($user->id) : null;
            
            $isUnavailable = $shift->unavailableUsers->contains($user->id);
            $userUnavailability = $isUnavailable ? $shift->unavailableUsers->find($user->id) : null;

            // Map enrolled users
            $enrolledUsers = $shift->users->map(function ($enrolledUser) {
                return [
                    'id' => $enrolledUser->id,
                    'name' => $enrolledUser->name,
                    'comment' => $enrolledUser->pivot->user_comment,
                    'enrolled_at' => $enrolledUser->pivot->created_at->format('Y-m-d H:i:s'),
                ];
            });

            // Map unavailable users
            $unavailableUsers = $shift->unavailableUsers->map(function ($unavailableUser) {
                return [
                    'id' => $unavailableUser->id,
                    'name' => $unavailableUser->name,
                    'reason' => $unavailableUser->pivot->reason,
                    'declined_at' => $unavailableUser->pivot->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return [
                'id' => $shift->id,
                'job_group_name' => $shift->jobGroup->name,
                'shift_date' => $shift->shift_date->format('Y-m-d'),
                'shift_date_formatted' => $shift->shift_date->format('d.m.Y'),
                'start_time' => $shift->start_time->format('H:i'),
                'end_time' => $shift->end_time->format('H:i'),
                'admin_comment' => $shift->admin_comment,
                'required_employees' => $shift->required_employees,
                'enrolled_count' => $shift->enrolled_count,
                'available_slots' => $shift->available_slots,
                'is_full' => $shift->is_full,
                'is_enrolled' => $isEnrolled,
                'user_comment' => $userEnrollment ? $userEnrollment->pivot->user_comment : null,
                'can_enroll' => !$isEnrolled && !$shift->is_full,
                'is_unavailable' => $isUnavailable,
                'unavailability_reason' => $userUnavailability ? $userUnavailability->pivot->reason : null,
                'enrolled_users' => $enrolledUsers,
                'unavailable_users' => $unavailableUsers,
            ];
        });

        return response()->json([
            'shifts' => $shifts,
        ], 200);
    }

    /**
     * Get details of a specific shift
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Shift $shift)
    {
        $user = Auth::user();
        $shift->load(['jobGroup', 'users']);

        $isEnrolled = $shift->users->contains($user->id);
        $userEnrollment = $isEnrolled ? $shift->users->find($user->id) : null;

        return response()->json([
            'shift' => [
                'id' => $shift->id,
                'job_group_name' => $shift->jobGroup->name,
                'shift_date' => $shift->shift_date->format('Y-m-d'),
                'shift_date_formatted' => $shift->shift_date->format('d.m.Y'),
                'start_time' => $shift->start_time->format('H:i'),
                'end_time' => $shift->end_time->format('H:i'),
                'admin_comment' => $shift->admin_comment,
                'required_employees' => $shift->required_employees,
                'enrolled_count' => $shift->enrolled_count,
                'available_slots' => $shift->available_slots,
                'is_full' => $shift->is_full,
                'is_enrolled' => $isEnrolled,
                'user_comment' => $userEnrollment ? $userEnrollment->pivot->user_comment : null,
                'can_enroll' => $shift->canUserEnroll($user),
            ],
        ], 200);
    }

    /**
     * Enroll the authenticated user to a shift
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\JsonResponse
     */
    public function enroll(Request $request, Shift $shift)
    {
        $user = Auth::user();

        // Check if user can enroll
        if (!$shift->canUserEnroll($user)) {
            $reasons = [];
            
            if ($shift->is_full) {
                $reasons[] = 'Die Schicht ist bereits voll belegt.';
            }
            
            if (!$user->jobGroups->contains($shift->job_group_id)) {
                $reasons[] = 'Sie gehören nicht zur erforderlichen Jobgruppe.';
            }
            
            if ($shift->users->contains($user->id)) {
                $reasons[] = 'Sie sind bereits zu dieser Schicht angemeldet.';
            }

            return response()->json([
                'message' => 'Anmeldung nicht möglich.',
                'reasons' => $reasons,
            ], 422);
        }

        $request->validate([
            'user_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Enroll user
        $shift->users()->attach($user->id, [
            'user_comment' => $request->input('user_comment'),
        ]);

        return response()->json([
            'message' => 'Erfolgreich zur Schicht angemeldet.',
            'shift' => [
                'id' => $shift->id,
                'enrolled_count' => $shift->fresh()->enrolled_count,
                'available_slots' => $shift->fresh()->available_slots,
            ],
        ], 200);
    }

    /**
     * Unenroll the authenticated user from a shift
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\JsonResponse
     */
    public function unenroll(Shift $shift)
    {
        $user = Auth::user();

        // Check if user is enrolled
        if (!$shift->users->contains($user->id)) {
            return response()->json([
                'message' => 'Sie sind nicht zu dieser Schicht angemeldet.',
            ], 422);
        }

        // Unenroll user
        $shift->users()->detach($user->id);

        return response()->json([
            'message' => 'Erfolgreich von der Schicht abgemeldet.',
            'shift' => [
                'id' => $shift->id,
                'enrolled_count' => $shift->fresh()->enrolled_count,
                'available_slots' => $shift->fresh()->available_slots,
            ],
        ], 200);
    }

    /**
     * Decline a shift (mark user as unavailable)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\JsonResponse
     */
    public function decline(Request $request, Shift $shift)
    {
        $user = Auth::user();

        // Check if user is in the job group
        if (!$user->jobGroups->contains($shift->job_group_id)) {
            return response()->json([
                'message' => 'Sie gehören nicht zur erforderlichen Jobgruppe.',
            ], 422);
        }

        // Check if user is already enrolled - can't decline an enrolled shift
        if ($shift->users->contains($user->id)) {
            return response()->json([
                'message' => 'Sie können keine Schicht absagen, zu der Sie bereits angemeldet sind. Bitte melden Sie sich zuerst ab.',
            ], 422);
        }

        // Check if already declined
        if ($shift->unavailableUsers->contains($user->id)) {
            return response()->json([
                'message' => 'Sie haben diese Schicht bereits abgesagt.',
            ], 422);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        // Mark as unavailable
        $shift->unavailableUsers()->attach($user->id, [
            'reason' => $request->input('reason'),
        ]);

        return response()->json([
            'message' => 'Schicht erfolgreich abgesagt.',
        ], 200);
    }

    /**
     * Remove decline (remove unavailability)
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeDecline(Shift $shift)
    {
        $user = Auth::user();

        // Check if user has declined
        if (!$shift->unavailableUsers->contains($user->id)) {
            return response()->json([
                'message' => 'Sie haben diese Schicht nicht abgesagt.',
            ], 422);
        }

        // Remove unavailability
        $shift->unavailableUsers()->detach($user->id);

        return response()->json([
            'message' => 'Absage erfolgreich zurückgezogen.',
        ], 200);
    }
}