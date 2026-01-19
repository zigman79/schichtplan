<?php

namespace App\Jobs;

use App\Models\Feiertag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ImportFeiertage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $year;
    private $federalState;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year = null, $federalState = null)
    {
        $this->year = $year ?? now()->year;
        $this->federalState = $federalState ?? 'ST';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $feiertage_url = 'https://www.feiertage-api.de/api/?jahr='.$this->year.'&nur_land='.$this->federalState;

        $heiligabend = [
            "datum" => $this->year.'-12-24',
            "bemerkung" => "",
        ];

        $silvester = [
            "datum" => $this->year.'-12-31',
            "bemerkung" => "",
        ];

        $response = Http::get($feiertage_url)->collect()
            ->prepend($heiligabend, 'Heiligabend')
            ->prepend($silvester, 'Silvester');

        $response->each(function ($content, $feiertag) {
            Feiertag::updateOrCreate(
                ['datum' => $content['datum']],
                ['name' => $feiertag]
            );
        });

    }
}
