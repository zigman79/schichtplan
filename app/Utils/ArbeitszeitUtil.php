<?php

namespace App\Utils;

use App\Models\Arbeitszeit;
use App\Models\Feiertag;
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
        if ($user->eingestellt_am > $monat) {
            return [
                'monat' => $monat,
                'urlaub' => 0,
                'sum' => 0,
                'sum_nice' => 0,
                'vorgabe' => 0,
                'vorgabe_nice' => 0,
                'diff' => 0,
                'diff_nice' => 0,
            ];
        }

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

    public function calculateOvertime(int $year, User $user, $shouldSave = true)
    {

        $start = $user->startValues($year);

        if ($start == null) {
            $start = StartValue::create(['user_id' => $user->id, 'year' => $year]);
        }

        $auswertungUtil = new ArbeitszeitUtil();

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
