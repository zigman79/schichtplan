<?php

namespace App\Jobs;

use App\Models\User;
use App\Utils\ArbeitszeitUtil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\ArtisanDispatchable\Jobs\ArtisanDispatchable;

class calculateOvertimeJob implements ShouldQueue,ArtisanDispatchable
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
        $users = User::whereShowOvertime(1)->get();
        $auswertungUtil = new ArbeitszeitUtil();
        foreach ($users as $user) {
            $auswertungUtil->calculateOvertime(now()->subMonth()->year,$user);
        };
    }
}
