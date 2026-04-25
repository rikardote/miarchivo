<?php

namespace App\Policies;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LoanRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('loans.view');
    }

    public function view(User $user, LoanRequest $loanRequest): bool
    {
        // Users can view their own requests, or if they have permission to view all
        return $user->hasPermissionTo('loans.view') || $user->id === $loanRequest->requester_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('loans.create');
    }

    public function approve(User $user, LoanRequest $loanRequest): bool
    {
        return $user->hasPermissionTo('loans.approve');
    }

    public function deliver(User $user, LoanRequest $loanRequest): bool
    {
        return $user->hasPermissionTo('loans.deliver');
    }

    public function return(User $user, LoanRequest $loanRequest): bool
    {
        return $user->hasPermissionTo('loans.return');
    }

    public function cancel(User $user, LoanRequest $loanRequest): bool
    {
        if ($user->hasPermissionTo('loans.cancel-any')) {
            return true;
        }

        return $user->hasPermissionTo('loans.cancel-own') && $user->id === $loanRequest->requester_id;
    }
}
