<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Arbeitszeit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Schnittstelle für Greenkeeper-Orga (KI-Planung im Greenkeeping).
 *
 * Liefert je Mitarbeiter nur, an welchen Tagen er abwesend ist – ohne Grund.
 * Ob jemand krank ist, ist ein Gesundheitsdatum (Art. 9 DSGVO) und verlässt
 * dieses System nicht. Dazu für heute, ob die Person schon eingestempelt hat.
 */
class GreenkeepingController extends Controller
{
    public function verfuegbarkeit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'user_ids' => ['required', 'array', 'max:100'],
            'user_ids.*' => ['integer'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->startOfDay();
        if ($from->diffInDays($to) > 62) {
            return response()->json(['message' => 'Zeitraum höchstens 62 Tage.'], 422);
        }

        $userIds = array_map('intval', $data['user_ids']);

        // Abwesend = frei, Urlaub, krank oder Schule – der Grund wird bewusst nicht übertragen.
        $absent = Arbeitszeit::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('tag', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('frei_urlaub_krank')
            ->where('frei_urlaub_krank', '!=', '')
            ->orderBy('tag')
            ->get(['user_id', 'tag'])
            ->map(fn (Arbeitszeit $a) => [
                'user_id' => $a->user_id,
                'datum' => Carbon::parse($a->tag)->toDateString(),
                'abwesend' => true,
            ])
            ->values();

        $today = Arbeitszeit::query()
            ->whereIn('user_id', $userIds)
            ->where('tag', now()->toDateString())
            ->get(['user_id', 'beginn_bestaetigt', 'ende_bestaetigt'])
            ->keyBy('user_id');

        $heute = collect($userIds)->map(fn (int $id) => [
            'user_id' => $id,
            'eingestempelt' => (bool) ($today->get($id)?->beginn_bestaetigt),
            'ausgestempelt' => (bool) ($today->get($id)?->ende_bestaetigt),
        ])->values();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'tage' => $absent,
            'heute' => $heute,
        ]);
    }
}
