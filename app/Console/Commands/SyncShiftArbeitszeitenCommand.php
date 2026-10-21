<?php

namespace App\Console\Commands;

use App\Models\Shift;
use Illuminate\Console\Command;

class SyncShiftArbeitszeitenCommand extends Command
{
    protected $signature = 'shifts:sync-arbeitszeiten';
    protected $description = 'Legt für alle zukünftigen Schicht-Zusagen die Arbeitszeiten an, falls noch nicht vorhanden';

    public function handle()
    {
        $shifts = Shift::with('users')
            ->where('shift_date', '>=', now()->format('Y-m-d'))
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($shifts as $shift) {
            foreach ($shift->users as $user) {
                $date = $shift->shift_date->format('Y-m-d');
                $exists = $user->arbeitszeiten()->where('tag', $date)->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $user->arbeitszeiten()->create([
                    'tag'    => $date,
                    'beginn' => $shift->start_time->format('H:i:s'),
                    'ende'   => $shift->end_time->format('H:i:s'),
                ]);

                $this->line("  ✓ {$user->name} – {$date}");
                $created++;
            }
        }

        $this->info("Fertig: {$created} Arbeitszeiten angelegt, {$skipped} übersprungen.");
    }
}
