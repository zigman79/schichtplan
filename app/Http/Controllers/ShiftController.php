<?php

namespace App\Http\Controllers;

use App\Models\Arbeitszeit;
use App\Models\JobGroup;
use App\Models\Shift;
use App\Models\User;
use App\Utils\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = Shift::with(['jobGroup', 'users', 'unavailableUsers'])
            ->where('shift_date', '>=', now()->format('Y-m-d'))
            ->orderBy('shift_date', 'asc')
            ->orderBy('start_time', 'asc');

        // Filter by job group if provided
        if ($request->has('job_group_id') && $request->job_group_id) {
            $query->where('job_group_id', $request->job_group_id);
        }

        // Filter by date range if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->where('shift_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->where('shift_date', '<=', $request->date_to);
        }

        $shifts = $query->get()->map(function ($shift) {
            return [
                'id' => $shift->id,
                'job_group_id' => $shift->job_group_id,
                'job_group_name' => $shift->jobGroup->name,
                'shift_date' => $shift->shift_date->format('d.m.Y'),
                'start_time' => $shift->start_time->format('H:i'),
                'end_time' => $shift->end_time->format('H:i'),
                'admin_comment' => $shift->admin_comment,
                'required_employees' => $shift->required_employees,
                'enrolled_count' => $shift->enrolled_count,
                'available_slots' => $shift->available_slots,
                'is_full' => $shift->is_full,
                'unavailable_count' => $shift->unavailableUsers->count(),
            ];
        });

        $jobGroups = JobGroup::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
            ];
        });

        // Users mit letzter App-Nutzung laden (nur api-access Token)
        $usersWithLastUsage = User::whereHas('jobGroups')
            ->with(['tokens' => function ($query) {
                $query->where('name', 'api-access')
                    ->orderByDesc('last_used_at')
                    ->limit(1);
            }])
            ->get()
            ->map(function ($user) {
                $lastToken = $user->tokens->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_used_at' => $lastToken?->last_used_at?->format('d.m.Y H:i'),
                    'last_used_at_raw' => $lastToken?->last_used_at,
                    'never_used' => $lastToken === null || $lastToken->last_used_at === null,
                ];
            })
            ->sortBy([
                ['never_used', 'asc'],
                ['last_used_at_raw', 'desc'],
                ['name', 'asc'],
            ])
            ->values();

        return Inertia::render('Shift/Index', [
            'shifts' => $shifts,
            'jobGroups' => $jobGroups,
            'filters' => $request->only(['job_group_id', 'date_from', 'date_to']),
            'usersWithLastUsage' => $usersWithLastUsage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        $jobGroups = JobGroup::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
            ];
        });

        return Inertia::render('Shift/Create', [
            'jobGroups' => $jobGroups,
            'defaultDate' => $request->query('date'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_group_id' => ['required', 'exists:job_groups,id'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'admin_comment' => ['nullable', 'string'],
            'required_employees' => ['required', 'integer', 'min:1'],
        ]);

        $shift = Shift::create($validated);

        return Redirect::route('shifts.index')->with('success', 'Schicht wurde erstellt');
    }

    /**
     * Store multiple shifts at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeMultiple(Request $request)
    {
        $validated = $request->validate([
            'job_group_id' => ['required', 'exists:job_groups,id'],
            'shift_date' => ['required', 'date'],
            'shift_date_end' => ['required', 'date', 'after_or_equal:shift_date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'admin_comment' => ['nullable', 'string'],
            'required_employees' => ['required', 'integer', 'min:1'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'min:0', 'max:6'],
        ]);

        $startDate = new \DateTime($validated['shift_date']);
        $endDate = new \DateTime($validated['shift_date_end']);
        $selectedWeekdays = $validated['weekdays'];

        $count = 0;
        $current = clone $startDate;

        while ($current <= $endDate) {
            // PHP: 0 = Sonntag, 1 = Montag, ..., 6 = Samstag
            // Unser Frontend: 0 = Montag, ..., 6 = Sonntag
            $dayOfWeek = (int) $current->format('w');
            $dayIndex = ($dayOfWeek + 6) % 7; // Konvertierung

            if (in_array($dayIndex, $selectedWeekdays)) {
                Shift::create([
                    'job_group_id' => $validated['job_group_id'],
                    'shift_date' => $current->format('Y-m-d'),
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'admin_comment' => $validated['admin_comment'],
                    'required_employees' => $validated['required_employees'],
                ]);
                $count++;
            }

            $current->modify('+1 day');
        }

        return Redirect::route('shifts.index')->with('success', $count . ' Schichten wurden erstellt');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Inertia\Response
     */
    public function edit(Shift $shift)
    {
        $shift->load(['jobGroup', 'users', 'unavailableUsers']);

        $jobGroups = JobGroup::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
            ];
        });

        $enrolledUsers = $shift->users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'user_comment' => $user->pivot->user_comment,
                'enrolled_at' => $user->pivot->created_at->format('d.m.Y H:i'),
            ];
        });

        $unavailableUsers = $shift->unavailableUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'reason' => $user->pivot->reason,
                'declined_at' => $user->pivot->created_at->format('d.m.Y H:i'),
            ];
        });

        // Get IDs of users who have responded (enrolled or declined)
        $enrolledUserIds = $shift->users->pluck('id')->toArray();
        $unavailableUserIds = $shift->unavailableUsers->pluck('id')->toArray();
        $respondedUserIds = array_merge($enrolledUserIds, $unavailableUserIds);

        // Get users in the job group who have not responded yet
        $noResponseUsers = User::whereHas('jobGroups', function ($query) use ($shift) {
            $query->where('job_groups.id', $shift->job_group_id);
        })
            ->whereNotIn('id', $respondedUserIds)
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            });

        // Available users for adding (not enrolled, but may have declined)
        $availableUsers = User::whereHas('jobGroups', function ($query) use ($shift) {
            $query->where('job_groups.id', $shift->job_group_id);
        })
            ->whereNotIn('id', $enrolledUserIds)
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            });

        return Inertia::render('Shift/Edit', [
            'shift' => [
                'id' => $shift->id,
                'job_group_id' => $shift->job_group_id,
                'shift_date' => $shift->shift_date->format('Y-m-d'),
                'start_time' => $shift->start_time->format('H:i'),
                'end_time' => $shift->end_time->format('H:i'),
                'admin_comment' => $shift->admin_comment,
                'required_employees' => $shift->required_employees,
                'enrolled_count' => $shift->enrolled_count,
                'available_slots' => $shift->available_slots,
            ],
            'jobGroups' => $jobGroups,
            'enrolledUsers' => $enrolledUsers,
            'unavailableUsers' => $unavailableUsers,
            'noResponseUsers' => $noResponseUsers,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'job_group_id' => ['required', 'exists:job_groups,id'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'admin_comment' => ['nullable', 'string'],
            'required_employees' => ['required', 'integer', 'min:1'],
        ]);

        $shift->update($validated);

        return Redirect::back()->with('success', 'Schicht wurde aktualisiert');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();

        return Redirect::route('shifts.index')->with('success', 'Schicht wurde gelöscht');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:shifts,id',
        ]);

        $count = Shift::whereIn('id', $request->ids)->delete();

        return Redirect::route('shifts.index')->with('success', "{$count} Schichten wurden gelöscht.");
    }

    /**
     * Add a user to a shift.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addUser(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'user_comment' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Check if user is already enrolled
        if ($shift->users->contains($user->id)) {
            return Redirect::back()->with('error', 'Mitarbeiter ist bereits eingetragen');
        }

        $shift->users()->attach($user->id, [
            'user_comment' => $validated['user_comment'] ?? null,
        ]);

        $shift->createArbeitszeit($user);
        $this->sendShiftTelegram($user->name, 'Zusage', $shift);

        return Redirect::back()->with('success', 'Mitarbeiter wurde hinzugefügt');
    }

    /**
     * Remove a user from a shift.
     *
     * @param  \App\Models\Shift  $shift
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeUser(Shift $shift, User $user)
    {
        $shift->users()->detach($user->id);

        $shift->deleteArbeitszeit($user);
        $this->sendShiftTelegram($user->name, 'Zusage zurückgezogen', $shift);

        return Redirect::back()->with('success', 'Mitarbeiter wurde entfernt');
    }

    /**
     * Mark a user as unavailable for a shift.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\RedirectResponse
     */
    public function declineUser(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Check if user already declined
        if ($shift->unavailableUsers->contains($user->id)) {
            return Redirect::back()->with('error', 'Mitarbeiter hat bereits abgesagt');
        }

        $shift->unavailableUsers()->attach($user->id, [
            'reason' => $validated['reason'] ?? null,
        ]);

        $this->sendShiftTelegram($user->name, 'Absage', $shift);

        return Redirect::back()->with('success', 'Absage wurde gespeichert');
    }

    /**
     * Remove a user's decline from a shift.
     *
     * @param  \App\Models\Shift  $shift
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeDecline(Shift $shift, User $user)
    {
        $shift->unavailableUsers()->detach($user->id);

        $this->sendShiftTelegram($user->name, 'Absage zurückgezogen', $shift);

        return Redirect::back()->with('success', 'Absage wurde entfernt');
    }


    private function sendShiftTelegram(string $userName, string $action, Shift $shift): void
    {
        if (!env('TELEGRAM_GROUP')) {
            return;
        }

        $date = $shift->shift_date->format('d.m.Y');
        $group = $shift->jobGroup ? $shift->jobGroup->name : '';
        $message = "Schicht {$date}" . ($group ? " ({$group})" : '') . ": {$userName} - {$action}";

        Telegram::sendMessage(env('TELEGRAM_GROUP'), $message);
    }
}