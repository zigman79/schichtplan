<?php

namespace App\Utils;

use App\Models\Arbeitszeit;
use App\Models\Feiertag;
use App\Models\MinijobVorgabe;
use App\Models\StartValue;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

class ArbeitszeitUtil
{
    public function convertToHours($min)
    {
        $ret = $min < 0 ? '-' : '';
        $min = intval(abs($min));

        return $ret.sprintf('%02d:%02d', intval(floor($min / 60)), $min % 60);
    }

    public function monatsAuswertung(User $user, Carbon $monat)
    {
        $sum = 0;

        $vorgabe = $monat->daysInMonth;

        Feiertag::whereYear('datum', $monat->year)->whereMonth('datum', $monat->month)->get()->keyBy('datum')->each(function ($feiertag) use (&$vorgabe) {
            if (Carbon::parse($feiertag->datum)->dayOfWeek != CarbonInterface::SATURDAY && Carbon::parse($feiertag->datum)->dayOfWeek != CarbonInterface::SUNDAY) {
                $vorgabe -= 1;
            }
        });

        $period = CarbonPeriod::create($monat->clone()->firstOfMonth(), $monat->clone()->lastOfMonth());

        foreach ($period as $day) {
            if (! $day->isWeekday()) {
                $vorgabe -= 1;
            }
        }
        if ($user->isMinijob()) {
            $minijob_vorgabe = $user->minijobVorgabe($monat->year, $monat->month);
        }

        $vorgabe *= 8;

        //Repeat
        $user_vorgabe = ($user->isMinijob() ? $minijob_vorgabe->hours ?? $vorgabe : $vorgabe) * 60;

        $urlaub = ($user->isMinijob() ? $minijob_vorgabe->away ?? 8 : 8) * 60;

        $urlaubstage = 0;

        $arbeitszeiten = Arbeitszeit::whereUserId($user->id)
            ->whereBetween('tag', [$monat->clone()->firstOfMonth(), $monat->clone()->lastOfMonth()])
            ->orderBy('tag')
            ->get();

        foreach ($arbeitszeiten as $arbeitszeit) {
            $sum += $arbeitszeit->getArbeitszeitInMinutesAttribute();
            if ($arbeitszeit->frei_urlaub_krank === 'urlaub') {
                $urlaubstage++;
            }
            if ($arbeitszeit->frei_urlaub_krank === 'urlaub' || $arbeitszeit->frei_urlaub_krank === 'krank' || $arbeitszeit->frei_urlaub_krank === 'schule') {
                $sum += $urlaub;
            }
        }
        if ($user->eingestellt_am > $monat) {
            return [
                'monat' => $monat,
                'urlaub' => 0,
                'sum' => $sum,
                'sum_nice' => $this->convertToHours($sum),
                'vorgabe' => 0,
                'vorgabe_nice' => $this->convertToHours(0),
                'diff' => $sum,
                'diff_nice' => $this->convertToHours($sum),
            ];
        }
        return [
            'monat' => $monat,
            'urlaub' => $urlaubstage,
            'sum' => $sum,
            'sum_nice' => $this->convertToHours($sum),
            'vorgabe' => $user_vorgabe,
            'vorgabe_nice' => $this->convertToHours($user_vorgabe),
            'diff' => $sum - $user_vorgabe,
            'diff_nice' => $this->convertToHours($sum - $user_vorgabe),
        ];
    }

    /**
     * Minijobber der Standardgruppe werden nur nach Stunden bezahlt: abgerechnet wird
     * höchstens die Monatsvorgabe, Mehrstunden wandern in den Folgemonat und füllen
     * dort auf, was unter der Vorgabe bleibt. Urlaub, Krank und Schule zählen nicht.
     *
     * Jedes Jahr startet am 1.1. mit einem Übertrag: entweder von Hand in den Startwerten
     * eingetragen (start_values.minijob_uebertrag) oder aus dem Dezember des Vorjahres
     * berechnet. Im MINIJOB_UEBERTRAG_START_JAHR startet die Regel ohne Eintrag mit 0.
     */
    public const MINIJOB_STANDARD_GROUP = 1;

    public const MINIJOB_UEBERTRAG_START_JAHR = 2026;

    public function isMinijobStandard(User $user): bool
    {
        return $user->isMinijob() && optional($user->minijob_group)->id === self::MINIJOB_STANDARD_GROUP;
    }

    /**
     * Übertrag zum 1.1. in Minuten, wie er sich ohne Eintrag ergibt.
     */
    public function minijobStartwertBerechnet(User $user, int $year): int
    {
        if ($year <= self::MINIJOB_UEBERTRAG_START_JAHR) {
            return 0;
        }

        return $this->minijobAbrechnung($user, Carbon::create($year - 1, 12))['uebertrag_neu'];
    }

    /**
     * Von Hand eingetragener Übertrag zum 1.1. in Minuten, oder null.
     */
    public function minijobStartwertManuell(User $user, int $year): ?int
    {
        $stunden = StartValue::whereUserId($user->id)->where('year', $year)->value('minijob_uebertrag');

        return $stunden === null ? null : (int) round($stunden * 60);
    }

    public function minijobAbrechnung(User $user, Carbon $monat): array
    {
        if ($monat->year < self::MINIJOB_UEBERTRAG_START_JAHR) {
            return [
                'gearbeitet' => 0,
                'uebertrag_alt' => 0,
                'grenze' => 0,
                'abzurechnen' => 0,
                'uebertrag_neu' => 0,
            ];
        }

        return last($this->minijobJahresverlauf($user, $monat->year, $monat->month));
    }

    /**
     * Abrechnung der Monate Januar bis $bisMonat eines Jahres, je Monat ein Eintrag
     * wie bei minijobAbrechnung (Schlüssel 'monat' zusätzlich).
     */
    public function minijobJahresverlauf(User $user, int $year, int $bisMonat): array
    {
        $aktuell = Carbon::create($year, 1, 1)->startOfDay();
        $ende = Carbon::create($year, $bisMonat, 1)->lastOfMonth();

        $minuten = Arbeitszeit::whereUserId($user->id)
            ->whereBetween('tag', [$aktuell, $ende])
            ->whereNotNull('beginn')
            ->whereNotNull('ende')
            ->get()
            ->groupBy(fn ($arbeitszeit) => Carbon::parse($arbeitszeit->tag)->format('Y-m'))
            ->map(fn ($tage) => $tage->sum(fn ($arbeitszeit) => $arbeitszeit->getArbeitszeitInMinutesAttribute()));

        $vorgaben = MinijobVorgabe::whereGroupId(self::MINIJOB_STANDARD_GROUP)
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $uebertrag = $this->minijobStartwertManuell($user, $year)
            ?? $this->minijobStartwertBerechnet($user, $year);

        $verlauf = [];

        for ($m = 1; $m <= $bisMonat; $m++) {
            // Fehlt die Vorgabe für einen Monat, gilt die letzte davor (bzw. die erste überhaupt)
            $vorgabe = $vorgaben->last(fn ($v) => $v->year * 100 + $v->month <= $aktuell->year * 100 + $aktuell->month)
                ?? $vorgaben->first();
            $grenze = (int) round(($vorgabe->hours ?? 0) * 60);

            $gearbeitet = $minuten->get($aktuell->format('Y-m'), 0);
            $gesamt = $gearbeitet + $uebertrag;
            $abzurechnen = min($gesamt, $grenze);

            $verlauf[] = [
                'monat' => $aktuell->clone(),
                'gearbeitet' => $gearbeitet,
                'uebertrag_alt' => $uebertrag,
                'grenze' => $grenze,
                'abzurechnen' => $abzurechnen,
                'uebertrag_neu' => $gesamt - $abzurechnen,
            ];

            $uebertrag = $gesamt - $abzurechnen;
            $aktuell->addMonth();
        }

        return $verlauf;
    }

    public function calculateOvertime(int $year, User $user, $shouldSave = true)
    {

        $start = $user->startValues($year);

        if ($start == null) {
            $start = StartValue::create(['user_id' => $user->id, 'year' => $year]);
        }

        $auswertungUtil = new ArbeitszeitUtil();

        if ($this->isMinijobStandard($user) && $year >= self::MINIJOB_UEBERTRAG_START_JAHR) {
            return $this->calculateMinijobUebertrag($year, $user, $start, $shouldSave);
        }

        $sum = $start->ueberstunden * 60;

        $urlaub = $start->resturlaub + $start->urlaub;

        $data = [
            'start' => $start,
            'user' => $user,
            'year' => $year,
        ];

        $i = 0;

        if ($year == now()->year) {
            while (++$i < now()->month) {
                $v = $auswertungUtil->monatsAuswertung($user, now()->month($i));
                $sum += $v['diff'];
                $urlaub -= $v['urlaub'];
                $data['verlauf'][] = $v;
            }
        } else {
            while (++$i < 13) {
                $v = $auswertungUtil->monatsAuswertung($user, now()->month($i)->year($year));
                $sum += $v['diff'];
                $urlaub -= $v['urlaub'];
                $data['verlauf'][] = $v;
            }
        }

        $data['end'] = [
            'summe' => $sum,
            'summe_nice' => $auswertungUtil->convertToHours($sum),
            'urlaub' => $urlaub,
        ];

        $start->ueberstunden_aktuell = $sum / 60;
        $start->resturlaub_aktuell = $urlaub;

        if ($shouldSave) {
            $start->save();
        }

        $start->ueberstunden_nice = $auswertungUtil->convertToHours($start->ueberstunden * 60);

        return $data;
    }

    /**
     * Jahresübersicht für Minijobber der Standardgruppe: es gibt kein Soll und keine
     * Minusstunden. Je Monat wird höchstens die Vorgabe bezahlt, der Rest bleibt als
     * Übertrag stehen. 'diff' ist die Änderung des Übertrags, die Summe der Stand.
     */
    private function calculateMinijobUebertrag(int $year, User $user, StartValue $start, bool $shouldSave): array
    {
        $bisMonat = $year == now()->year ? now()->month - 1 : 12;
        $startwert = $this->minijobStartwertManuell($user, $year) ?? $this->minijobStartwertBerechnet($user, $year);
        $urlaub = $start->resturlaub + $start->urlaub;

        $verlauf = [];
        $stand = $startwert;

        foreach ($bisMonat > 0 ? $this->minijobJahresverlauf($user, $year, $bisMonat) : [] as $a) {
            $urlaubstage = Arbeitszeit::whereUserId($user->id)
                ->whereBetween('tag', [$a['monat']->clone()->firstOfMonth(), $a['monat']->clone()->lastOfMonth()])
                ->where('frei_urlaub_krank', 'urlaub')
                ->count();
            $urlaub -= $urlaubstage;
            $diff = $a['uebertrag_neu'] - $a['uebertrag_alt'];
            $stand = $a['uebertrag_neu'];

            $verlauf[] = [
                'monat' => $a['monat'],
                'urlaub' => $urlaubstage,
                'sum' => $a['gearbeitet'],
                'sum_nice' => $this->convertToHours($a['gearbeitet']),
                'vorgabe' => $a['abzurechnen'],
                'vorgabe_nice' => $this->convertToHours($a['abzurechnen']),
                'diff' => $diff,
                'diff_nice' => $this->convertToHours($diff),
                'uebertrag' => $stand,
                'uebertrag_nice' => $this->convertToHours($stand),
            ];
        }

        $start->ueberstunden_aktuell = $stand / 60;
        $start->resturlaub_aktuell = $urlaub;

        if ($shouldSave) {
            $start->save();
        }

        $start->ueberstunden_nice = $this->convertToHours($startwert);

        return [
            'start' => $start,
            'user' => $user,
            'year' => $year,
            'minijob' => true,
            'verlauf' => $verlauf,
            'end' => [
                'summe' => $stand,
                'summe_nice' => $this->convertToHours($stand),
                'urlaub' => $urlaub,
            ],
        ];
    }

    public function getWeekData(int $tage = 7)
    {
        for ($c = 0; $c < $tage; $c++) {
            $tag[$c] = now()->addDays($c)->format('d.m.Y');
            $arbeit[$c] = Arbeitszeit::whereTag(now()->addDays($c)->format('Y-m-d'))
                ->where('frei_urlaub_krank', null)
                ->get();
            $frei[$c] = Arbeitszeit::whereTag(now()->addDays($c)->format('Y-m-d'))
                ->where('frei_urlaub_krank', 'frei')
                ->get();
            $krank[$c] = Arbeitszeit::whereTag(now()->addDays($c)->format('Y-m-d'))
                ->whereIn('frei_urlaub_krank', ['krank','schule'])
                ->get();
            $urlaub[$c] = Arbeitszeit::whereTag(now()->addDays($c)->format('Y-m-d'))
                ->where('frei_urlaub_krank', 'urlaub')
                ->get();
        }

        return [
            'tag' => $tag,
            'arbeit' => $arbeit,
            'frei' => $frei,
            'krank' => $krank,
            'urlaub' => $urlaub,
        ];
    }

    public function getArbeitzeitenData($users, $month, $year)
    {
        $users
            ->load([
                'arbeitszeiten' => function ($query) use ($month, $year) {
                    $query->whereMonth('tag', $month)
                        ->whereYear('tag', $year);
                }, 'arbeitszeiten.pausen',
            ]);

        $feiertage = Feiertag::whereYear('datum', $year)->whereMonth('datum', $month)->get()->keyBy('datum')->toArray();

        // Values and All to get the Sorted Collection as Array
        $users = $users->values()->all();

        $date = Carbon::createFromFormat('Y-m-d', $year.'-'.$month.'-15');
        $startOfCalendar = $date->copy()->firstOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $period = CarbonPeriod::create($startOfCalendar, $endOfCalendar);

        $weeks = $period->toArray();
        $weeks = array_chunk($weeks, 7);

        $dayLabels = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];

        return [
            'year' => $year,
            'month' => $month,
            'users' => $users,
            'weeks' => $weeks,
            'tournaments' => $tournaments ?? [],
            'feiertage' => $feiertage,
            'dayLabels' => $dayLabels,
        ];
    }

    public function getSummaryData($users, $month, $year)
    {
        $users->load(['arbeitszeiten' => function ($query) use ($month, $year) {
            $query->whereMonth('tag', $month)
                ->whereYear('tag', $year);
        }])->each(function ($user) use ($month, $year) {
            if ($user->isMinijob()) {
                $user->minijob_vorgabe = $user->minijobVorgabe($year, $month);
            }
        });
        // Values and All to get the Sorted Collection as Array
        $users = $users->values()->all();

        $date = Carbon::createFromFormat('Y-m-d', $year.'-'.$month.'-15');
        $startOfCalendar = $date->copy()->firstOfMonth();
        $endOfCalendar = $date->copy()->endOfMonth();

        $period = CarbonPeriod::create($startOfCalendar, $endOfCalendar);
        $days = $period->toArray();
        $month_labels = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        $feiertage = Feiertag::whereYear('datum', $year)->whereMonth('datum', $month)->get()->keyBy('datum');
        $vorgabe = $date->daysInMonth;
        $feiertage->each(function ($feiertag) use (&$vorgabe) {
            if (Carbon::parse($feiertag->datum)->dayOfWeek != Carbon::SATURDAY && Carbon::parse($feiertag->datum)->dayOfWeek != Carbon::SUNDAY) {
                $vorgabe -= 1;
            }
        });
        foreach ($days as $day) {
            if (Carbon::parse($day)->dayOfWeek === Carbon::SATURDAY || Carbon::parse($day)->dayOfWeek === Carbon::SUNDAY) {
                $vorgabe -= 1;
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'users' => $users,
            'days' => $days,
            'month_labels' => $month_labels,
            'feiertage' => $feiertage,
            'vorgabe' => $vorgabe * 8,
        ];
    }
}
