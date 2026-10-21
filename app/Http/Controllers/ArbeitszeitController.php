<?php

namespace App\Http\Controllers;

use App\Http\Resources\OverTimeCollection;
use App\Models\Arbeitszeit;
use App\Models\Feiertag;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\ArbeitszeitManuallyChangedNotification;
use App\Utils\ArbeitszeitUtil;
use App\Utils\Telegram;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
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

            $tournaments = Http::withToken(config('tenant.backoffice_api_key'))
                ->get(config('tenant.backoffice_api_url').'/tournaments/calendar/'.$year.'/'.$month)
                ->json();

        }

        $feiertage = Feiertag::whereYear('datum', $year)->whereMonth('datum', $month)->get()->keyBy('datum');

        $shifts = Shift::whereYear('shift_date', $year)
            ->whereMonth('shift_date', $month)
            ->with(['users:id,name', 'unavailableUsers:id,name', 'jobGroup:id,name', 'jobGroup.users:id,name'])
            ->get()
            ->groupBy(fn($s) => $s->shift_date->format('Y-m-d'));

        // Values and All to get the Sorted Collection as Array
        $users = $users->values()->all();

        return Inertia::render('Dashboard', [
            'users' => $users,
            'tournaments' => $tournaments ?? [],
            'year' => $year,
            'month' => $month,
            'feiertage' => $feiertage,
            'shifts' => $shifts,
        ]);
    }

    public function store(User $user, Request $request)
    {

        $this->validateArbeitszeit($request);

        // updateOrCreate statt create: Gibt es für den Tag schon einen Eintrag
        // (z.B. veraltete Ansicht in einem zweiten Tab), scheitert create am
        // Unique-Index (tag, user_id)
        $arbeitszeit = $user->arbeitszeiten()->updateOrCreate(
            ['tag' => $request->tag],
            [
                'beginn' => $request->beginn,
                'ende' => $request->ende,
                'frei_urlaub_krank' => $request->frei_urlaub_krank,
            ]
        );

        // Bestehende Pausen werden durch die aus dem Formular ersetzt
        if (! $arbeitszeit->wasRecentlyCreated) {
            $arbeitszeit->pausen()->delete();
        }

        // Create Pausen to $arbeitszeit
        $arbeitszeit->pausen()->createMany($this->ausgefuelltePausen($request));

        return Redirect::back()->with('success', 'Arbeitszeit erstellt.');
    }

    private function validateArbeitszeit(Request $request): void
    {
        $this->validate($request, [
            'tag' => 'required|date_format:Y-m-d',
            'beginn' => 'nullable|date_format:H:i:s',
            'ende' => 'nullable|date_format:H:i:s',
            'frei_urlaub_krank' => 'nullable|in:frei,urlaub,krank,schule',

            // Validate Pausen
            'pausen' => 'nullable|array',
            'pausen.*' => 'array',
            'pausen.*.beginn' => 'nullable|required_with:pausen.*.ende|date_format:H:i:s',
            'pausen.*.ende' => 'nullable|date_format:H:i:s',
        ], [
            'pausen.*.beginn.required_with' => 'Bitte den Beginn der Pause angeben.',
        ]);
    }

    /**
     * Pausen aus dem Formular ohne leere Zeilen: Eine Pause kann hinzugefügt,
     * aber nie ausgefüllt werden und kommt dann mit beginn = null an.
     */
    private function ausgefuelltePausen(Request $request): array
    {
        return collect($request->input('pausen') ?? [])
            ->filter(fn ($pause) => ($pause['beginn'] ?? null) !== null)
            ->map(fn ($pause) => [
                'beginn' => $pause['beginn'],
                'ende' => $pause['ende'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function update(Arbeitszeit $arbeitszeit, Request $request)
    {

        $this->validateArbeitszeit($request);

        $owner = $arbeitszeit->user;
        $editor = auth()->user();
        $notifyUser = $owner->id !== $editor->id && Telegram::isValidChatId($owner->telegram_id);

        $before = [
            'beginn' => $arbeitszeit->beginn,
            'ende' => $arbeitszeit->ende,
            'frei_urlaub_krank' => $arbeitszeit->frei_urlaub_krank,
        ];

        $pausen = $this->ausgefuelltePausen($request);

        // If Request or Arbeitszeit has Pausen
        if (count($pausen) > 0 || $arbeitszeit->pausen()->count() > 0) {

            // Delete Pausen
            $arbeitszeit->pausen()->delete();

            // Create Pausen to $arbeitszeit
            $arbeitszeit->pausen()->createMany($pausen);

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

        if ($notifyUser) {
            try {
                $owner->notify(new ArbeitszeitManuallyChangedNotification($before, $arbeitszeit, $editor));
            } catch (\Exception $e) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), "Fehler beim Senden der Notification an " . $owner->name . ": " . $e->getMessage());
            }
        }

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

            $tournaments = Http::withToken(config('tenant.backoffice_api_key'))
                ->get(config('tenant.backoffice_api_url').'/tournaments/calendar/'.$year.'/'.$month)
                ->json();

        }

        $data = (new ArbeitszeitUtil())->getArbeitzeitenData($users, $month, $year);
        $data['tournaments'] = $tournaments ?? [];

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

        abort_unless($this->darfArbeitszeitenSehenVon($user), 403);

        $auswertungUtil = new ArbeitszeitUtil();

        $pdf_data = $auswertungUtil->calculateOvertime($year, $user, false);
        if ($preview) {
            return view('print.arbeitszeit.overview', $pdf_data);
        } else {
            $pdf = PDF::loadView('print.arbeitszeit.overview', $pdf_data)->setPaper('a4', 'portait');

            return $pdf->download('Übersicht '.$year.'-'.$user->name.'.pdf');
        }
    }

    public function jahresuebersicht($year = null)
    {

        $year = (int) ($year ?? $this->defaultJahr());

        return Inertia::render('Arbeitszeit/Jahresuebersicht', [
            'year' => $year,
            'years' => [now()->year, now()->year - 1],
            'monate' => $this->getJahresMonate($year),
            'users' => $this->getJahresuebersichtData($year),
        ]);
    }

    public function printJahresuebersicht($year = null, $preview = false)
    {

        $year = (int) ($year ?? $this->defaultJahr());

        $data = [
            'year' => $year,
            'monate' => $this->getJahresMonate($year),
            'users' => $this->getJahresuebersichtData($year),
        ];

        if ($preview) {

            return view('print.arbeitszeit.jahresuebersicht', $data);

        } else {

            $pdf = PDF::loadView('print.arbeitszeit.jahresuebersicht', $data)->setPaper('a4', 'landscape');

            return $pdf->download('Jahresübersicht '.$year.'.pdf');
        }

    }

    /**
     * Im Januar gibt es für das laufende Jahr noch keine Auswertung,
     * daher startet die Übersicht dann mit dem Vorjahr.
     */
    private function defaultJahr(): int
    {
        return now()->month === 1 ? now()->year - 1 : now()->year;
    }

    /**
     * Monate, die im gewählten Jahr ausgewertet werden.
     * Im laufenden Jahr wird der aktuelle Monat noch nicht gewertet.
     */
    private function getJahresMonate(int $year): array
    {
        \Carbon\Carbon::setlocale(config('app.locale'));

        $letzterMonat = $year == now()->year ? now()->month - 1 : 12;

        $monate = [];

        for ($i = 1; $i <= $letzterMonat; $i++) {
            $monate[] = \Carbon\Carbon::create($year, $i, 1)->translatedFormat('F');
        }

        return $monate;
    }

    /**
     * Überstunden aller (sichtbaren) Mitarbeiter für ein Jahr.
     * Teamleiter sehen nur ihr eigenes Team, Admins alle.
     */
    private function getJahresuebersichtData(int $year): array
    {
        \Carbon\Carbon::setlocale(config('app.locale'));

        // Sortiert nach Nachname (letztes Wort im Namen), bei Gleichheit nach Vorname
        $users = $this->sichtbareUsers()->sortBy(fn (User $user) => [
            Str::lower(Str::ascii(Str::afterLast(trim($user->name), ' '))),
            Str::lower(Str::ascii($user->name)),
        ]);

        $auswertungUtil = new ArbeitszeitUtil();

        return $users->map(function (User $user) use ($auswertungUtil, $year) {

            $data = $auswertungUtil->calculateOvertime($year, $user, false);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'uebertrag' => $data['start']->ueberstunden_nice,
                'minijob' => $data['minijob'] ?? false,
                // Minijobber haben keine Minusstunden: je Monat steht dort der Stand des Übertrags
                'monate' => collect($data['verlauf'] ?? [])->map(function ($monat) use ($data) {
                    $minijob = $data['minijob'] ?? false;

                    return [
                        'monat' => $monat['monat']->translatedFormat('F'),
                        'diff' => $minijob ? $monat['uebertrag'] : $monat['diff'],
                        'diff_nice' => $minijob ? $monat['uebertrag_nice'] : $monat['diff_nice'],
                    ];
                })->values()->all(),
                'summe' => $data['end']['summe'],
                'summe_nice' => $data['end']['summe_nice'],
                'urlaub' => $data['end']['urlaub'],
            ];
        })->values()->all();
    }

    /**
     * Alle User, deren Arbeitszeiten der angemeldete User sehen darf.
     */
    private function sichtbareUsers()
    {
        return auth()->user()->sichtbareArbeitszeitUsers();
    }

    private function darfArbeitszeitenSehenVon(User $user): bool
    {
        if (auth()->user()->arbeitszeit_admin == 1 || auth()->id() === $user->id) {
            return true;
        }

        return auth()->user()->arbeitszeitenTeam()->whereKey($user->id)->exists();
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
