<?php

namespace App\Http\Controllers;

use App\Models\JobGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class JobGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('JobGroup/Index', [
            'groups' => JobGroup::withCount('users', 'shifts')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('JobGroup/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $group = JobGroup::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]));

        return Redirect::route('jobGroups.edit', $group->id)->with('success', 'Jobgruppe wurde erstellt');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobGroup  $jobGroup
     * @return \Inertia\Response
     */
    public function edit(JobGroup $jobGroup)
    {
        $jobGroup->load('users');
        
        $allUsers = User::all()
            ->sortBy('name')
            ->transform(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->readable_role,
            ])->values();

        return Inertia::render('JobGroup/Edit', [
            'group' => $jobGroup,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobGroup  $jobGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, JobGroup $jobGroup)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
        ]);

        $jobGroup->update($request->only('name'));
        
        // Sync users
        if ($request->has('users')) {
            $jobGroup->users()->sync($request->users);
        }

        return Redirect::back()->with('success', 'Jobgruppe wurde aktualisiert');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobGroup  $jobGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(JobGroup $jobGroup)
    {
        $jobGroup->delete();

        return Redirect::route('jobGroups.index')->with('success', 'Jobgruppe wurde gelöscht');
    }
}