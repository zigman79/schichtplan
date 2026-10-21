<?php

namespace App\Notifications;

use App\Models\Arbeitszeit;
use App\Models\User;
use App\Utils\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramMessage;

class ArbeitszeitManuallyChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private array $before,
        private Arbeitszeit $arbeitszeit,
        private User $changedBy,
    ) {}

    public function shouldSend(User $notifiable, $channel): bool
    {
        return Telegram::isValidChatId($notifiable->telegram_id);
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $tag = \Carbon\Carbon::parse($this->arbeitszeit->tag)->format('d.m.Y');

        $msg = "✏️ Deine Arbeitszeit vom {$tag} wurde geändert";
        $msg .= "\n(durch: {$this->changedBy->name})\n";

        $msg .= "\n*Vorher:*";
        $msg .= "\n" . $this->formatZeiten($this->before['beginn'], $this->before['ende'], $this->before['frei_urlaub_krank']);

        $msg .= "\n\n*Nachher:*";
        $msg .= "\n" . $this->formatZeiten(
            $this->arbeitszeit->beginn,
            $this->arbeitszeit->ende,
            $this->arbeitszeit->frei_urlaub_krank
        );

        return TelegramMessage::create()->content($msg);
    }

    private function formatZeiten(?string $beginn, ?string $ende, ?string $freiUrlaubKrank): string
    {
        if ($freiUrlaubKrank) {
            return ucfirst($freiUrlaubKrank);
        }

        $line = $beginn ? 'Beginn: ' . Str::substr($beginn, 0, -3) . ' Uhr' : 'Beginn: –';
        $line .= $ende ? "\nEnde:   " . Str::substr($ende, 0, -3) . ' Uhr' : "\nEnde:   –";

        return $line;
    }
}
