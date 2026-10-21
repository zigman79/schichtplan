<?php

namespace App\Utils;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class Telegram
{
    public static function sendMessage($chat_id, $message)
    {
        if (!self::isValidChatId($chat_id)) {
            return;
        }

        // Telegram-Nachrichten sind nur Info: Ist die API nicht erreichbar, darf
        // das weder den Check-in noch einen Job abbrechen
        try {
            Http::connectTimeout(5)
                ->timeout(10)
                ->retry(2, 500, fn ($e) => $e instanceof ConnectionException, throw: false)
                ->post("https://api.telegram.org/bot".env('TELEGRAM_KEY')."/sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "disable_web_page_preview" => true
                ]);
        } catch (ConnectionException $e) {
            // Die Meldung enthält die URL und damit den Bot-Token
            Log::warning('Telegram nicht erreichbar: '.str_replace((string) env('TELEGRAM_KEY'), '***', $e->getMessage()));
        }
    }

    /**
     * Platzhalter-IDs (kleiner als 10) gehören zu Nutzern ohne Telegram-Account.
     * Sie bekommen weiterhin die Monatsmail (die nur eine gesetzte telegram_id
     * voraussetzt), aber keine Telegram-Nachrichten.
     */
    public static function isValidChatId($chat_id): bool
    {
        $chat_id = trim((string) $chat_id);

        if ($chat_id === '') {
            return false;
        }

        // Gruppen-IDs sind negativ, deshalb der Betrag
        return !is_numeric($chat_id) || abs((int) $chat_id) >= 10;
    }

}
