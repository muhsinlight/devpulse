<?php

namespace App\Events;

use App\Models\Monitor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonitorCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Monitor $monitor) {}
}
