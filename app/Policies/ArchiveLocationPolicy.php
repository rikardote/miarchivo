<?php

namespace App\Policies;

use App\Models\ArchiveLocation;
use App\Models\User;

class ArchiveLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('locations.view');
    }

    public function view(User $user, ArchiveLocation $archiveLocation): bool
    {
        return $user->can('locations.view');
    }

    public function create(User $user): bool
    {
        return $user->can('locations.create');
    }

    public function update(User $user, ArchiveLocation $archiveLocation): bool
    {
        return $user->can('locations.update');
    }

    public function delete(User $user, ArchiveLocation $archiveLocation): bool
    {
        return $user->can('locations.delete');
    }
}
