<?php

namespace App\Http\Controllers;

use App\Models\MinijobGroup;
use App\Models\MinijobVorgabe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class MinijobVorgabeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request, $year = null)
    {
        $year = $year ?? date('Y');
        $selectedGroupId = $request->query('group') ? (int) $request->query('group') : null;

        return Inertia::render('MinijobVorgaben/Index', [
            'groups' => MinijobGroup::withVorgaben($year),
            'year' => $year,
            'selectedGroupId' => $selectedGroupId,
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
        $this->validate($request, [
            'group_id' => 'required|integer',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'hours' => 'required|numeric|min:0',
            'away' => 'required|numeric|min:0',
        ]);

        MinijobVorgabe::create($request->all());

        return Redirect::back()->with('success', 'Vorgabe erstellt.');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, MinijobVorgabe $minijobvorgabe)
    {
        $this->validate($request, [
            'group_id' => 'required|integer',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'hours' => 'required|numeric|min:0',
            'away' => 'required|numeric|min:0',
        ]);

        $minijobvorgabe->update($request->all());

        return Redirect::back()->with('success', 'Vorgabe wurde erfolgreich aktualisiert.');
    }

    /**
     * Apply values to all months of a year for a group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function applyToAllMonths(Request $request)
    {
        $this->validate($request, [
            'group_id' => 'required|integer',
            'year' => 'required|integer',
            'hours' => 'required|numeric|min:0',
            'away' => 'required|numeric|min:0',
        ]);

        $groupId = $request->group_id;
        $year = $request->year;
        $hours = $request->hours;
        $away = $request->away;

        for ($month = 1; $month <= 12; $month++) {
            MinijobVorgabe::updateOrCreate(
                [
                    'group_id' => $groupId,
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'hours' => $hours,
                    'away' => $away,
                ]
            );
        }

        return Redirect::back()->with('success', 'Vorgaben für alle Monate übernommen.');
    }
}
