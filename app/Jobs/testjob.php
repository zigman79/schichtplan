<?php

namespace App\Jobs;

use App\Utils\Telegram;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class testjob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Telegram::sendMessage(464615461, 'Testjob wurde ausgeführt. '.Str::random(10));
    }
}
