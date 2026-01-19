<?php

namespace App\Jobs;

use App\Models\Arbeitszeit;
use App\Utils\Telegram;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\ArtisanDispatchable\Jobs\ArtisanDispatchable;

class CheckNotCheckedOutJob implements ShouldQueue,ArtisanDispatchable
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
        $arbeitszeiten = Arbeitszeit::whereYear('tag',now()->year)
            ->whereNot('tag',now()->format('Y-m-d'))
            ->whereNotNull('beginn')
            ->whereNull('ende')
            ->get();
        foreach ($arbeitszeiten as $arbeitszeit) {
            // gelöschte user oder micha oder udo löschen
            if ( $arbeitszeit->user == null  || in_array($arbeitszeit->user->id,[1,74]) ) {
                $arbeitszeit->delete();
                continue;
            }
            /** @var Arbeitszeit $arbeitszeit */
            $msg = $arbeitszeit->user->name ."  hat am ".Carbon::parse($arbeitszeit->tag)->format("d.m.Y"). " zwar eingeloggt, aber nicht ausgeloggt.";
            Telegram::sendMessage(env('TELEGRAM_GROUP'), $msg);
            if ($arbeitszeit->user->telegram_id != null) {
                Telegram::sendMessage($arbeitszeit->user->telegram_id, $msg);
            }
            foreach ($arbeitszeit->user->arbeitszeitenTeamleiter as $teamleiter) {
                if ($teamleiter->telegram_id != null) {
                    Telegram::sendMessage($teamleiter->telegram_id, $msg);
                }
            }

        }
    }
}
