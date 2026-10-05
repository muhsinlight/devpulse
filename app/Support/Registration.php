<?php

namespace App\Support;

use App\Models\User;

class Registration
{
    /**
     * Registration stays closed after the first account unless the env flag is on.
     */
    public function isOpen(): bool
    {
        if ((bool) config('app.registration_enabled')) {
            return true;
        }

        return ! User::query()->exists();
    }
}
