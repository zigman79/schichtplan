<?php

namespace App\Jobs;

use App\Mail\ArbeitszeitenMail;
use App\Models\User;
use App\Utils\ArbeitszeitUtil;
use App\Utils\Telegram;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSummaryToUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ref_date = now();
        if (now()->day == 1) {
            $ref_date->subMonth();
        }
        $year = $ref_date->year;
        $month = $ref_date->month;

        $all_users = User::whereKeineArbeitszeit(0)
            ->whereNotNull('telegram_id')->get();

        foreach ($all_users as $user) {
            $users = User::where('id', $user->id)->get();
            $data = (new ArbeitszeitUtil())->getSummaryData($users, $month, $year);
            $pdf = PDF::loadView('print.arbeitszeit.summary', $data)->setPaper('a4', 'portait');
            try {
                Mail::to($user->email)
                    ->send(new ArbeitszeitenMail($user, 'Übersicht ' . $year . '-' . $month . '.pdf', $pdf->output()));
            } catch (\Exception $e) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), 'Fehler beim Versenden der Monatsübersicht an ' . $user->name . ' ' . $user->email);
            }
        }

    }
}
