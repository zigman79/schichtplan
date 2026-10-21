<?php

namespace App\Jobs;

use App\Mail\ArbeitszeitenMail;
use App\Models\User;
use App\Utils\ArbeitszeitUtil;
use App\Utils\Telegram;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendSummaryToSingleUserJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly int $year,
        public readonly int $month,
    ) {}

    public function handle(): void
    {
        $users = User::where('id', $this->user->id)->get();
        $data = (new ArbeitszeitUtil())->getSummaryData($users, $this->month, $this->year);
        $pdf = Pdf::loadView('print.arbeitszeit.summary', $data)->setPaper('a4', 'portrait');

        Mail::to($this->user->email)
            ->send(new ArbeitszeitenMail($this->user, 'Übersicht ' . $this->year . '-' . $this->month . '.pdf', $pdf->output()));
    }

    public function failed(\Throwable $e): void
    {
        if (env('TELEGRAM_GROUP')) {
            Telegram::sendMessage(
                env('TELEGRAM_GROUP'),
                'Fehler beim Versenden der Monatsübersicht an ' . $this->user->name . ' (' . $this->user->email . '): ' . $e->getMessage()
            );
        }
    }
}
