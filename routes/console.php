<?php

use App\Jobs\calculateOvertimeJob;
use App\Jobs\CheckNotCheckedOutJob;
use App\Jobs\ImportFeiertage;
use App\Jobs\SendJahresSummaryToUsersJob;
use App\Jobs\SendMinijobArbeitszeitenMailJob;
use App\Jobs\SendSummaryToUsersJob;
use App\Jobs\testjob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ImportFeiertage())->yearlyOn(1, 1);
Schedule::job(new CheckNotCheckedOutJob())->dailyAt('10:00');
Schedule::job(new calculateOvertimeJob())->dailyAt('01:02');
Schedule::job(new SendSummaryToUsersJob())->weeklyOn(1,'01:00');
Schedule::job(new SendSummaryToUsersJob())->monthlyOn(1,'02:00');
Schedule::job(new SendJahresSummaryToUsersJob())->monthlyOn(1,'03:00');
Schedule::job(new SendMinijobArbeitszeitenMailJob())->monthlyOn(1,'06:00');
