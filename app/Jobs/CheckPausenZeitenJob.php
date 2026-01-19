<?php

namespace App\Jobs;

use App\Models\Arbeitszeit;
use App\Utils\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPausenZeitenJob implements ShouldQueue
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
        $arbeitszeiten = Arbeitszeit::whereTag(now()->subDay()->format('Y-m-d'))->get();
        foreach ($arbeitszeiten as $arbeitszeit) {
            if ($arbeitszeit->ArbeitszeitInMinutes > 9*60) {
                if ($arbeitszeit->PausenzeitInMinuten < 45) {
                    $msg = $arbeitszeit->user->name." hat gestern zu wenig Pause (30 min) gemacht";
                    if (config('tenant.send_group_notifiaction')) {
                        Telegram::sendMessage(env('TELEGRAM_GROUP'), $msg);
                    }
                    Telegram::sendMessage($arbeitszeit->user->telegram_id, $msg);
                }
            }
            if ($arbeitszeit->ArbeitszeitInMinutes > 6*60) {
                if ($arbeitszeit->PausenzeitInMinuten < 30) {
                    $msg = $arbeitszeit->user->name." hat gestern zu wenig Pause (45 min) gemacht";
                    if (config('tenant.send_group_notifiaction')) {
                        Telegram::sendMessage(env('TELEGRAM_GROUP'), $msg);
                    }
                    Telegram::sendMessage($arbeitszeit->user->telegram_id, $msg);
                }
            }
        }
    }
}
