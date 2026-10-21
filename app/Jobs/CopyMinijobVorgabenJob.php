<?php

namespace App\Jobs;

use App\Models\MinijobGroup;
use App\Models\MinijobVorgabe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CopyMinijobVorgabenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        $currentYear = (string) now()->year;
        $previousYear = (string) (now()->year - 1);

        $groups = MinijobGroup::all();

        foreach ($groups as $group) {
            // Prüfe, ob bereits Vorgaben für das aktuelle Jahr existieren
            $existingVorgaben = MinijobVorgabe::where('group_id', $group->id)
                ->where('year', $currentYear)
                ->exists();

            if ($existingVorgaben) {
                continue;
            }

            // Hole Vorgaben vom Vorjahr
            $previousVorgaben = MinijobVorgabe::where('group_id', $group->id)
                ->where('year', $previousYear)
                ->get();

            // Kopiere jede Vorgabe fürs neue Jahr
            foreach ($previousVorgaben as $vorgabe) {
                MinijobVorgabe::create([
                    'year' => $currentYear,
                    'month' => $vorgabe->month,
                    'hours' => $vorgabe->hours,
                    'away' => $vorgabe->away,
                    'group_id' => $vorgabe->group_id,
                ]);
            }
        }
    }
}
