<?php

namespace App\Policies;

use App\Models\Expedient;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpedientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('expedients.view');
    }

    public function view(User $user, Expedient $expedient): bool
    {
        return $user->hasPermissionTo('expedients.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('expedients.create');
    }

    public function update(User $user, Expedient $expedient): bool
    {
        return $user->hasPermissionTo('expedients.update');
    }

    public function delete(User $user, Expedient $expedient): bool
    {
        return $user->hasPermissionTo('expedients.delete');
    }

    public function changeLocation(User $user, Expedient $expedient): bool
    {
        return $user->hasPermissionTo('expedients.change-location');
    }
}
