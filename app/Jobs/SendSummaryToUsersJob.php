<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
            ->whereNotNull('telegram_id')
            ->where('email', 'not like', '%@golfpark-hufeisensee.de')
            ->get();

        foreach ($all_users as $user) {
            SendSummaryToSingleUserJob::dispatch($user, $year, $month);
        }

    }
}
