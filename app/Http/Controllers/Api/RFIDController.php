<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\Telegram;
use Illuminate\Http\Request;

class RFIDController extends Controller
{
    public function getToken(User $user, Request $request)
    {
        foreach ($user->tokens as $token) {
            if ($token->name == $user->chip_id) {
                $token->delete();
            }
        }
        $token = $user->createToken($user->chip_id, ['user']);
        $admins = User::whereArbeitszeitAdmin(1)->get();
        foreach ($admins as $admin) {
            if ($admin->telegram_id) {
                Telegram::sendMessage($admin->telegram_id, "Chip ".$user->chip_id." wurde ". $user->name." zugeordnet");
            }
        }
        return ['token' => $token->plainTextToken];
    }

    public function annouceChipID($chip_id)
    {
        $user = User::whereNull("chip_id")->where("created_at",">",now()->subday())->first();
        if ($user) {
            $user->chip_id = $chip_id;
            $user->save();
        }
        $admins = User::whereArbeitszeitAdmin(1)->get();
        foreach ($admins as $admin) {
            if ($admin->telegram_id) {
                if ($user) {
                    Telegram::sendMessage($admin->telegram_id, "Neuer Chip für user ".$user->name." . ID : ".$chip_id);

                } else {
                    Telegram::sendMessage($admin->telegram_id, "Neuer Chip. ID : ".$chip_id);
                }
            }
        }
    }
    public function registerDeviceRegister(Request $request)
    {
        $data = $request->validate([
            'ip' => 'nullable|ip',
            'mac' => 'nullable|string',
            'hostname' => 'nullable|string',
            'rssi' => 'nullable|integer',
            'log_url' => 'nullable|url',
        ]);

        $message = "RFID-Gerät angemeldet:\n";
        $message .= "Hostname: " . ($data['hostname'] ?? 'unbekannt') . "\n";
        $message .= "MAC: " . ($data['mac'] ?? 'unbekannt') . "\n";
        $message .= "IP: " . ($data['ip'] ?? 'unbekannt') . "\n";
        if (isset($data['rssi'])) {
            $message .= "RSSI: " . $data['rssi'] . " dBm\n";
        }
        if (isset($data['log_url'])) {
            $message .= "Log: " . $data['log_url'];
        }

        $admins = User::whereArbeitszeitAdmin(1)->get();
        foreach ($admins as $admin) {
            if ($admin->telegram_id) {
                Telegram::sendMessage($admin->telegram_id, $message);
            }
        }

        return response()->json(['status' => 'registered', 'message' => 'Device registered successfully']);
    }
}
