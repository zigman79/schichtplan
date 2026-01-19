<?php

namespace App\Observers;

use App\Models\Arbeitszeit;

class ArbeitszeitObserver
{
    /**
     * Handle the Arbeitszeit "saving" event.
     *
     * @param  Arbeitszeit  $arbeitszeit
     * @return void
     */
    public function saving(Arbeitszeit $arbeitszeit)
    {
        $arbeitszeit->arbeitszeit = $arbeitszeit->calculateArbeitszeit();
    }
}
