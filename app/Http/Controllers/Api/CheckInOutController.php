<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DeleteUnusenPausedJob;
use App\Models\Arbeitszeit;
use App\Models\Pause;
use App\Models\User;
use App\Notifications\ArbeitszeitUpdatedNotification;
use App\Utils\Telegram;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInOutController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(User $user, Request $request)
    {
        $now = now()->setSecond(0);
        /* @var Arbeitszeit $arbeitszeit */
        $arbeitszeit = $user->arbeitszeiten()
            ->where('tag', $now->format('Y-m-d'))
            ->first();
        if ($now->hour < 3) {
            // Vor um 3 und gestern nicht "ausgeloggt" ? dann arbeiten wir immnoch
            // für gestern auschecken und nachricht schicken. Dann neue Arbeitszeit für heute anlegen
            /* @var Arbeitszeit $arbeitszeit */
            $arbeitszeit = $user->arbeitszeiten()
                ->where('tag', now()->subDay()->format('Y-m-d'))
                ->first();
            if ($arbeitszeit != null && !$arbeitszeit->ende_bestaetigt) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), $user->name . "  arbeitet über Nacht.");

                $arbeitszeit->ende = "23:59:00";
                $arbeitszeit->ende_bestaetigt = true;
                $arbeitszeit->save();
                $user->notify(new ArbeitszeitUpdatedNotification($arbeitszeit));
                /* @var Arbeitszeit $arbeitszeit */
                $arbeitszeit = $user->arbeitszeiten()->create([
                    "tag" => $now,
                    "beginn" => "00:00:00",
                    "updated_at" =>  now()->startOfDay()->setSecond(0),
                    "beginn_bestaetigt" => true,
                    "ende" => $now,
                ]);
                try {
                    $user->notify(new ArbeitszeitUpdatedNotification($arbeitszeit));

                } catch (\Exception $e) {
                    Telegram::sendMessage(env('TELEGRAM_GROUP'), "Fehler beim Senden der Notification an " . $user->name . ": " . $e->getMessage());
                }
            }
        }
        if ($arbeitszeit == null) {
            /* @var Arbeitszeit $arbeitszeit */
            $arbeitszeit = $user->arbeitszeiten()->create([
                "tag" => $now,
                "beginn" => $now,
                "beginn_bestaetigt" => true
            ]);

            if (config('tenant.send_group_notifiaction')) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), $user->name . " hat mit der Arbeit begonnen.");
            }

            try {
                $user->notify(new ArbeitszeitUpdatedNotification($arbeitszeit));

            } catch (\Exception $e) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), "Fehler beim Senden der Notification an " . $user->name . ": " . $e->getMessage());
            }
            return response()->json();
        }

        if (!$arbeitszeit->beginn_bestaetigt) {
            $arbeitszeit->frei_urlaub_krank = null;
            $arbeitszeit->beginn = $now;
            $arbeitszeit->beginn_bestaetigt = true;
            $arbeitszeit->save();
            $arbeitszeit->pausen()->delete();

            if (config('tenant.send_group_notifiaction')) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), $user->name . " hat mit der Arbeit begonnen.");
            }

            try {
                $user->notify(new ArbeitszeitUpdatedNotification($arbeitszeit));

            } catch (\Exception $e) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), "Fehler beim Senden der Notification an " . $user->name . ": " . $e->getMessage());
            }
            return response()->json();
        }
        // return if $arbeitszeit updated at in the last 5 minutes and beginn_bestaetigt
        if ($arbeitszeit->updated_at->diffInMinutes($now,true) < 2) {
            return response()->json([], 429);
        }

        // get last pause from $arbeitszeit
        /** @var Pause $pause * */
        $pause = $arbeitszeit->pausen()->latest()->first();

        if ($pause != null) {
            if (now()->setTimeFromTimeString($pause->beginn)->diffInMinutes($now,true) < 2 ||
                ($pause->ende !== null && now()->setTimeFromTimeString($pause->ende)->diffInMinutes($now) < 2)) {
                return response()->json([], 429);
            }
            if ($pause->ende == null) {

                $pause->ende = $now;
                $pause->save();
                $arbeitszeit->ende = $now;
                $arbeitszeit->ende_bestaetigt = false;
                $arbeitszeit->calculateArbeitszeit();
                $arbeitszeit->save();

                if (config('tenant.send_group_notifiaction')) {
                    Telegram::sendMessage(env('TELEGRAM_GROUP'), $user->name . " hat Pause beendet.");
                }

                try {
                    $user->notify(new ArbeitszeitUpdatedNotification($arbeitszeit));

                } catch (\Exception $e) {
                    Telegram::sendMessage(env('TELEGRAM_GROUP'), "Fehler beim Senden der Notification an " . $user->name . ": " . $e->getMessage());
                }
                return response()->json();
            }
        }

        if ($arbeitszeit->beginn_bestaetigt) {

            $arbeitszeit->ende = $now;
            $arbeitszeit->ende_bestaetigt = true;
            $arbeitszeit->save();

            $neuepause = $arbeitszeit->pausen()->create([
                'beginn' => $now,
            ]);
            ray($neuepause);

            if (config('tenant.send_group_notifiaction')) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), $user->name . " hat ausgeloggt.");
            }
            try {
                $user->notify(new ArbeitszeitUpdatedNotification($arbeitszeit));

            } catch (\Exception $e) {
                Telegram::sendMessage(env('TELEGRAM_GROUP'), "Fehler beim Senden der Notification an " . $user->name . ": " . $e->getMessage());
            }
            //DeleteUnusenPausedJob::dispatch($neuepause)->delay(now()->addHour());
            return response()->json();
        }
    }
}
