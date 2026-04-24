<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

trait ConfirmsSudo
{
    public string $sudoPassword = '';

    public function confirmSudo(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (Hash::check($this->sudoPassword, $user->password)) {
            $this->sudoPassword = ''; // clear it immediately
            return true;
        }

        return false;
    }
}
