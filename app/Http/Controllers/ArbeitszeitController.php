<?php

namespace App\Http\Controllers;

use App\Http\Resources\OverTimeCollection;
use App\Models\Arbeitszeit;
use App\Models\Feiertag;
use App\Models\Pause;
use App\Models\User;
use App\Utils\ArbeitszeitUtil;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ArbeitszeitController extends Controller
{
    public function index($year = null, $month = null)
    {

        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        $users = auth()->user()->arbeitszeitenTeam()->get()
            ->add(auth()->user())->sortBy('sort');
        if (auth()->user()->arbeitszeit_admin == 1) {
            $users = User::all();
        }

        $users = $users->reject(function ($user) {
            return $user->keine_arbeitszeit;
        });

        $users
            ->load([
                'arbeitszeiten' => function ($query) use ($month, $year) {
                    $query->whereMonth('tag', $month)->whereYear('tag', $year);
                },
                'arbeitszeiten.pausen',
            ]);

        if (config('tenant.use_tournaments')) {

            $tournaments = Http::withToken(env('BACKOFFICE_API_KEY'))
                ->get('https://api.handicapp.golf/tournaments/calendar/'.$year.'/'.$month)
                ->json();

        }

        $feiertage = Feiertag::whereYear('datum', $year)->whereMonth('datum', $month)->get()->keyBy('datum');

        // Values and All to get the Sorted Collection as Array
        $users = $users->values()->all();

        return Inertia::render('Dashboard', [
            'users' => $users,
            'tournaments' => $tournaments ?? [],
            'year' => $year,
            'month' => $month,
            'feiertage' => $feiertage,
        ]);
    }

    public function store(User $user, Request $request)
    {

        $this->validate($request, [
            'tag' => 'required|date_format:Y-m-d',
            'beginn' => 'nullable|date_format:H:i:s',
            'ende' => 'nullable|date_format:H:i:s',
            'frei_urlaub_krank' => 'nullable|in:frei,urlaub,krank,schule',

            // Validate Pausen
            'pausen' => 'nullable|array',
            'pausen.*.beginn' => 'nullable|date_format:H:i:s',
            'pausen.*.ende' => 'nullable|date_format:H:i:s',
        ]);

        $arbeitszeit = $user->arbeitszeiten()->create([
            'tag' => $request->tag,
            'beginn' => $request->beginn,
            'ende' => $request->ende,
            'frei_urlaub_krank' => $request->frei_urlaub_krank,
        ]);

        // Create Pausen to $arbeitszeit
        if ($request->pausen) {
            foreach ($request->pausen as $pausen) {
                $arbeitszeit->pausen()->create([
                    'beginn' => $pausen['beginn'],
                    'ende' => $pausen['ende'],
                ]);
            }
        }

        return Redirect::back()->with('success', 'Arbeitszeit erstellt.');
    }

    public function update(Arbeitszeit $arbeitszeit, Request $request)
    {

        $this->validate($request, [
            'tag' => 'required|date_format:Y-m-d',
            'beginn' => 'nullable|date_format:H:i:s',
            'ende' => 'nullable|date_format:H:i:s',
            'frei_urlaub_krank' => 'nullable|in:frei,urlaub,krank,schule',

            // Validate Pausen
            'pausen' => 'nullable|array',
            'pausen.*.beginn' => 'nullable|date_format:H:i:s',
            'pausen.*.ende' => 'nullable|date_format:H:i:s',
        ]);

        // If Request or Arbeitszeit has Pausen
        if (count($request->input('pausen')) > 0 || $arbeitszeit->pausen()->count() > 0) {

            // Delete Pausen
            $arbeitszeit->pausen()->delete();

            // Create Pausen to $arbeitszeit
            $request->pausen = array_map(function ($pause) use ($arbeitszeit) {
                $pause['arbeitszeit_id'] = $arbeitszeit->id;
                unset($pause['created_at']);
                unset($pause['updated_at']);
                unset($pause['id']);

                return $pause;
            }, $request->pausen);

            Pause::upsert($request->pausen, []);

        } elseif ($request->has('frei_urlaub_krank') && ($arbeitszeit->pausen() || $arbeitszeit->pausenzeit > 0)) {

            $this->resetPausen($arbeitszeit);
            $arbeitszeit->calculateArbeitszeit();
        }

        $arbeitszeit->update([
            'tag' => $request->tag,
            'beginn' => $request->beginn,
            'ende' => $request->ende,
            'frei_urlaub_krank' => $request->frei_urlaub_krank,
        ]);

        return Redirect::back()->with('success', 'Arbeitszeit aktualisiert.');
    }

    public function resetPausen(Arbeitszeit $arbeitszeit): void
    {

        $arbeitszeit->pausen()->delete();

        $arbeitszeit->update([
            'pausenzeit' => 0,
        ]);
    }

    public function destroy(Arbeitszeit $arbeitszeit)
    {
        $arbeitszeit->delete();

        return Redirect::back()->with('success', 'Arbeitszeit gelöscht.');
    }

    public function print($year = null, $month = null, $preview = false)
    {

        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        $users = auth()->user()->arbeitszeitenTeam()->get()
            ->add(auth()->user());

        if (auth()->user()->arbeitszeit_admin == 1) {
            $users = User::all();
        }

        $users = $users->reject(function ($user) {
            return $user->keine_arbeitszeit;
        });

        if (config('tenant.use_tournaments')) {

            $tournaments = Http::withToken(env('BACKOFFICE_API_KEY'))
                ->get('https://api.handicapp.golf/tournaments/calendar/'.$year.'/'.$month)
                ->json();

        }

        $data = (new ArbeitszeitUtil())->getArbeitzeitenData($users, $month, $year);

        if ($preview) {

            return view('print.arbeitszeit.month', $data);

        } else {

            $pdf = PDF::loadView('print.arbeitszeit.month', $data)->setPaper('a4', 'landscape');

            return $pdf->download('Arbeitszeiten '.$year.'-'.$month.'.pdf');
        }

    }

    public function summary($year = null, $month = null, $preview = false)
    {

        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        $users = auth()->user()->arbeitszeitenTeam()->get()
            ->add(auth()->user());

        if (auth()->user()->arbeitszeit_admin == 1) {
            $users = User::all();
        }

        $users = $users->reject(function ($user) {
            return $user->keine_arbeitszeit;
        });

        $data = (new ArbeitszeitUtil())->getSummaryData($users, $month, $year);
        if ($preview) {

            return view('print.arbeitszeit.summary', $data);

        } else {

            $pdf = PDF::loadView('print.arbeitszeit.summary', $data)->setPaper('a4', 'portait');

            return $pdf->download('Übersicht '.$year.'-'.$month.'.pdf');
        }

    }

    public function getOvertime()
    {
        return OverTimeCollection::collection(User::whereShowOvertime(1)->get());
    }

    public function overview(int $year, User $user, $preview = false)
    {

        $auswertungUtil = new ArbeitszeitUtil();

        $pdf_data = $auswertungUtil->calculateOvertime($year, $user, false);
        if ($preview) {
            return view('print.arbeitszeit.overview', $pdf_data);
        } else {
            $pdf = PDF::loadView('print.arbeitszeit.overview', $pdf_data)->setPaper('a4', 'portait');

            return $pdf->download('Übersicht '.$year.'-'.$user->name.'.pdf');
        }
    }

    public function showWochenplan(bool $preview = false)
    {
        $auswertungUtil = new ArbeitszeitUtil();

        $pdf_data = $auswertungUtil->getWeekData();

        if ($preview) {
            return view('print.arbeitszeit.wochenplan', $pdf_data);
        } else {
            $pdf = PDF::loadView('print.arbeitszeit.wochenplan', $pdf_data)->setPaper('a4', 'landscape');

            return $pdf->download('aktuellÜbersicht.pdf');
        }
    }

    /**
     * @return Response
     */
    public function wochenplan()
    {
        $auswertungUtil = new ArbeitszeitUtil();

        $data = $auswertungUtil->getWeekData();

        $users = User::get(['id', 'name']);

        return Inertia::render('Arbeitszeit/Wochenplan', [
            'wochenplan' => $data,
            'users' => $users,
        ]);
    }

    public function getGastroTime($date) {
        $time = 0;
        $users = User::find(45)->arbeitszeitenTeam()->get();
        foreach ($users as $user) {
            $arbeitszeiten = $user->arbeitszeiten()->where('tag', $date)->get();
            foreach ($arbeitszeiten as $arbeitszeit) {
                $time += $arbeitszeit->arbeitszeit_in_minutes;
            }
        }
        return $time;
    }
}
