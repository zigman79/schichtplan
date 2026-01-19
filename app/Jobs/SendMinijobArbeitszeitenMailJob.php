<?php

namespace App\Jobs;

use App\Mail\MinijobZeitenMail;
use App\Models\User;
use App\Utils\ArbeitszeitUtil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMinijobArbeitszeitenMailJob implements ShouldQueue
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
        $users = User::whereMinijob(1)->get();
        $auswertungUtil = new ArbeitszeitUtil();
        $zeitpunkt = now()->subMonth();
        $ret=[];
        foreach ($users as $user) {
            if (!$user->isMinijob()) {
                continue;
            }
            if ($user->minijobGroups()->get()->isEmpty()) {;
                $user->minijobGroups()->attach(1);
            }
            $v = $auswertungUtil->monatsAuswertung($user, $zeitpunkt);
            if ($v["sum"] > 0) {
                $ret[] = [
                    "user" => $user->name,
                    "sum" => number_format($v["sum"]/60,2,","),
                    "sum_nice" => $v["sum_nice"]
                ];
            }
        }
        Mail::to("pohle@kanzlei-ludolph.de")
            ->bcc("k.doerfer@golfpark-hufeisensee.de")
            ->send(new MinijobZeitenMail($zeitpunkt, $ret));
    }
}
