<?php

namespace App\Console\Commands;

use App\Models\StartValue;
use App\Models\User;
use App\Utils\ArbeitszeitUtil;
use Illuminate\Console\Command;

class Jahreswechsel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jahreswechsel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Händisch ausdokumentieren . Achtung !! überschreibt die Werte für dieses Jahr !
        return;
        $auswertungUtil = new ArbeitszeitUtil();
        $users = User::whereKeineArbeitszeit(0)->get();
        foreach ($users as $user) {
            dump($user->name);
            $pdf_data = $auswertungUtil->calculateOvertime(now()->year, $user);
            $pdf_data = $auswertungUtil->calculateOvertime(now()->subYear()->year, $user);
            $alt = $user->startValues(now()->subYear()->year);
            $neu = $user->startValues(now()->year);
            /** @var StartValue resturlaub */
            $neu->resturlaub = $pdf_data["end"]["urlaub"];
            $neu->ueberstunden = $pdf_data["end"]["summe"] / 60;
            if ($neu->urlaub == 0) {
                $neu->urlaub = $alt->urlaub;
            }
            $neu->save();
        }
    }
}
