<?php

namespace App\Events;

use App\Models\LoanRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanDelivered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LoanRequest $loanRequest) {}
}
