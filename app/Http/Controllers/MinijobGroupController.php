<?php

namespace App\Http\Controllers;

use App\Models\MinijobGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class MinijobGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('MinijobGroup/Index', [
            'groups' =>  MinijobGroup::withCount('users')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('MinijobGroup/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
       $group = MinijobGroup::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]));

       return Redirect::route('minijobGroups.edit', $group->id)->with('success', 'Gruppe wurde erstellt');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MinijobGroup  $minijobGroup
     * @return \Illuminate\Http\Response
     */
    public function show(MinijobGroup $minijobGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MinijobGroup  $minijobGroup
     * @return \Inertia\Response
     */
    public function edit(MinijobGroup $minijobGroup)
    {
        return Inertia::render('MinijobGroup/Edit', [
            'group' => $minijobGroup,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MinijobGroup  $minijobGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, MinijobGroup $minijobGroup)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $minijobGroup->update($request->all());

        return Redirect::back()->with('success', 'Gruppe wurde aktualisiert');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MinijobGroup  $minijobGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MinijobGroup $minijobGroup)
    {
        if ($minijobGroup->id == 1) {
            return Redirect::back()->with('error', 'Gruppe kann nicht gelöscht werden');
        }

        $minijobGroup->delete();

        return Redirect::route('minijobGroups.index')->with('success', 'Gruppe wurde gelöscht');
    }
}
