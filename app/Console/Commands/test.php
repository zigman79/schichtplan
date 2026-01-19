<?php

namespace App\Console\Commands;

use App\Http\Controllers\ArbeitszeitController;
use App\Jobs\calculateOvertimeJob;
use App\Jobs\ImportFeiertage;
use App\Mail\ArbeitszeitenMail;
use App\Mail\MinijobZeitenMail;
use App\Models\Arbeitszeit;
use App\Models\Feiertag;
use App\Models\MinijobGroup;
use App\Models\MinijobVorgabe;
use App\Models\StartValue;
use App\Models\User;
use App\Notifications\ArbeitszeitUpdatedNotification;
use App\Utils\ArbeitszeitUtil;
use App\Utils\Telegram;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::whereShowOvertime(1)->get();
        $filename = 'arbeitszeiten_' . Carbon::now()->subYear()->year . '.csv';

        $handle = fopen($filename, 'w+');
        fputcsv($handle, ['user_id', 'Name', 'Start2025', 'aktuell', 'Resturlaub']);
        foreach ($users as $user) {
            $row = [
                'user_id' => $user->id,
                'name' => $user->name,
                'overtime_start' => $user->startValues(now()->year)->ueberstunden ??0,
                'overtime' => $user->startValues(now()->year)->ueberstunden_aktuell ??0,
                'resturlaub' => $user->startValues(now()->year)->resturlaub_aktuell ??0,
            ];
            fputcsv($handle, $row);
        }

        return;
        /* get all arbeitszeiten from last year and for users with id 5 and 7 */
        $arbeitszeiten = Arbeitszeit::whereYear('tag', Carbon::now()->subYear()->year)
            ->whereIn('user_id', [5, 7])
            ->whereNotNull('beginn')
            ->whereNotNull('ende')
            ->orderBy('user_id')
            ->orderBy('tag')
            ->get();
        $data = [];
        /* create an excel sheet with all days from last year and fill the lowest start time and highest end time for each day */
        foreach ($arbeitszeiten as $arbeitszeit) {
            if (!isset($data[ $arbeitszeit->tag])) {
                $beginn = Carbon::createFromFormat('H:i:s', $arbeitszeit->beginn);
                $ende   = Carbon::createFromFormat('H:i:s', $arbeitszeit->ende);
                $data[ $arbeitszeit->tag] = [
                    'date' => $arbeitszeit->tag,
                    'start' => $arbeitszeit->beginn,
                    'end' => $arbeitszeit->ende,
                    'dauer' => Str::replace('.',',',$ende->floatDiffInHours($beginn,true)),
                ];
            } else {
                if ($arbeitszeit->beginn < $data[ $arbeitszeit->tag]['start']) {
                    $data[ $arbeitszeit->tag]['start'] = $arbeitszeit->beginn;
                }
                if ($arbeitszeit->ende > $data[ $arbeitszeit->tag]['end']) {
                    $data[ $arbeitszeit->tag]['end'] = $arbeitszeit->ende;
                }
                $beginn = Carbon::createFromFormat('H:i:s', $data[ $arbeitszeit->tag]['start']);
                $ende   = Carbon::createFromFormat('H:i:s', $data[ $arbeitszeit->tag]['end']);
                $data[ $arbeitszeit->tag]['dauer'] = Str::replace('.',',',$ende->floatDiffInHours($beginn,true));

            }
        }
        sort($data);
        /* create a csv file with the data */
        $filename = 'arbeitszeiten_' . Carbon::now()->subYear()->year . '.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, ['user_id', 'date', 'start', 'end']);
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
    }
}
