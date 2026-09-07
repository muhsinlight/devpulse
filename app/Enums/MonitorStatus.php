<?php

namespace App\Enums;

enum MonitorStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Degraded = 'degraded';
    case Pending = 'pending';
}
