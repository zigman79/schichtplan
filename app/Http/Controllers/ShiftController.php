<?php

namespace App\Http\Controllers;

use App\Models\JobGroup;
use App\Models\Shift;
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

        return Inertia::render('Shift/Index', [
            'shifts' => $shifts,
            'jobGroups' => $jobGroups,
            'filters' => $request->only(['job_group_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $jobGroups = JobGroup::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
            ];
        });

        return Inertia::render('Shift/Create', [
            'jobGroups' => $jobGroups,
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
}