<?php

namespace App\Notifications;

use App\Models\Arbeitszeit;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramMessage;

class ArbeitszeitUpdatedNotification extends Notification
{
    use Queueable;

    private $arbeitszeit;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Arbeitszeit $arbeitszeit)
    {
        $this->arbeitszeit = $arbeitszeit;
        $this->arbeitszeit->refresh();
    }

    /**
     * Determine if the notification should be sent.
     *
     * @param User $notifiable
     * @param  string  $channel
     * @return bool
     */
    public function shouldSend(User $notifiable, $channel)
    {
        return $notifiable->telegram_id != "";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['telegram'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toTelegram($notifiable)
    {
        $msg = "Arbeitsbegin: ".Str::substr($this->arbeitszeit->beginn,0,-3)." Uhr";
        if ($this->arbeitszeit->ende) {
            $msg.= "\nArbeitsende : ".Str::substr($this->arbeitszeit->ende,0,-3)." Uhr";
        }
        $msg.="\nArbeitszeit für heute: ".$this->arbeitszeit->readable_time;
        return TelegramMessage::create()->content($msg);
    }

}
