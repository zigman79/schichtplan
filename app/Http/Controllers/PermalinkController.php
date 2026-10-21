<?php

namespace App\Http\Controllers;

use App\Models\Arbeitszeit;
use App\Models\Feiertag;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

/**
 * Schreibgeschützte Wochenansicht über einen geheimen Link, ohne Login.
 * Der Link gehört zu einem User und zeigt genau die Arbeitszeiten,
 * die dieser User auch eingeloggt sehen darf.
 */
class PermalinkController extends Controller
{
    public function show(string $token)
    {
        $user = $this->userZumToken($token);

        // Deaktivierter oder erneuerter Link: verständlicher Hinweis statt nackter 404-Seite
        $antwort = $user
            ? Inertia::render('Permalink/Woche', [
                'token' => $token,
                'woche' => $this->wochenDaten($user, now()),
            ])->toResponse(request())
            : Inertia::render('Permalink/Ungueltig')->toResponse(request())->setStatusCode(404);

        return $antwort->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Weitere Wochen als JSON, damit die Seite beim Wischen nicht neu lädt.
     */
    public function woche(string $token, string $montag)
    {
        $user = $this->userZumToken($token);

        abort_unless($user, 404);

        return response()
            ->json($this->wochenDaten($user, Carbon::createFromFormat('!Y-m-d', $montag)))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function userZumToken(string $token): ?User
    {
        return User::where('permalink_token', $token)->first();
    }

    private function wochenDaten(User $user, CarbonInterface $datum): array
    {
        $start = $datum->copy()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
        $tage = collect(range(0, 6))->map(fn (int $i) => $start->copy()->addDays($i));

        $von = $tage->first()->toDateString();
        $bis = $tage->last()->toDateString();

        $users = $user->sichtbareArbeitszeitUsers()->load([
            'arbeitszeiten' => fn ($query) => $query->whereBetween('tag', [$von, $bis])->orderBy('id'),
        ]);

        $feiertage = Feiertag::whereBetween('datum', [$von, $bis])->pluck('name', 'datum');
        $turniere = $this->turniere($tage);

        return [
            'start' => $von,
            'kw' => $start->isoWeek(),
            'heute' => today()->toDateString(),
            'stand' => now()->format('H:i'),
            'besitzer' => $user->name,
            'tage' => $tage->map(fn (CarbonInterface $tag) => [
                'datum' => $tag->toDateString(),
                'feiertag' => $feiertage[$tag->toDateString()] ?? null,
                'turniere' => $turniere[$tag->toDateString()] ?? [],
            ])->all(),
            // Bewusst nur die nötigen Felder, keine kompletten User-Models
            'users' => $users->map(fn (User $mitarbeiter) => [
                'id' => $mitarbeiter->id,
                'name' => $mitarbeiter->name,
                'kurz' => $mitarbeiter->short_name,
                'tage' => $tage->map(fn (CarbonInterface $tag) => $this->eintrag(
                    $mitarbeiter->arbeitszeiten->firstWhere('tag', $tag->toDateString())
                ))->all(),
            ])->all(),
        ];
    }

    private function eintrag(?Arbeitszeit $arbeitszeit): ?array
    {
        if (! $arbeitszeit) {
            return null;
        }

        // Frei/Urlaub/Krank/Schule geht vor Uhrzeiten, wie im Arbeitsplan
        if ($arbeitszeit->frei_urlaub_krank) {
            return ['status' => $arbeitszeit->frei_urlaub_krank, 'von' => null, 'bis' => null];
        }

        if (! $arbeitszeit->beginn && ! $arbeitszeit->ende) {
            return null;
        }

        return [
            'status' => null,
            'von' => $arbeitszeit->beginn ? substr($arbeitszeit->beginn, 0, 5) : null,
            'bis' => $arbeitszeit->ende ? substr($arbeitszeit->ende, 0, 5) : null,
        ];
    }

    /**
     * Turniernamen nach Datum. Kurz gecacht, damit beim Durchwischen
     * der Wochen nicht jedes Mal die Backoffice-API gefragt wird.
     */
    private function turniere(Collection $tage): array
    {
        if (! config('tenant.use_tournaments')) {
            return [];
        }

        return $tage->map(fn (CarbonInterface $tag) => $tag->format('Y/m'))->unique()
            ->flatMap(fn (string $monat) => Cache::remember('permalink-turniere-'.str_replace('/', '-', $monat), now()->addMinutes(10), function () use ($monat) {
                try {
                    return Http::withToken(config('tenant.backoffice_api_key'))
                        ->timeout(5)
                        ->get(config('tenant.backoffice_api_url').'/tournaments/calendar/'.$monat)
                        ->throw()
                        ->json();
                } catch (\Throwable $e) {
                    // null wird nicht gecacht -> beim nächsten Aufruf neuer Versuch
                    return null;
                }
            }) ?? [])
            ->map(fn ($turniere) => collect($turniere)->pluck('turniername')->filter()->values()->all())
            ->all();
    }
}
