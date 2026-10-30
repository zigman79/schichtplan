<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Legt den Lese-Token für Greenkeeper-Orga an (nur Abwesenheiten ohne Grund und
 * Stempelstatus heute). Der Token hängt am ersten Arbeitszeit-Admin.
 */
class CreateGreenkeepingTokenCommand extends Command
{
    protected $signature = 'greenkeeping:token';

    protected $description = 'Lese-Token für Greenkeeper-Orga anlegen (Fähigkeit greenkeeping:read)';

    public function handle(): int
    {
        $admin = User::whereArbeitszeitAdmin(1)->first();
        if ($admin === null) {
            $this->error('Kein Arbeitszeit-Admin gefunden.');

            return self::FAILURE;
        }

        $token = $admin->createToken('greenkeeper-orga', ['greenkeeping:read'])->plainTextToken;

        $this->info('Token für Greenkeeper-Orga (ARBEITSPLAN_TOKEN), wird nur jetzt angezeigt:');
        $this->line($token);

        return self::SUCCESS;
    }
}
