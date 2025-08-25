<?php

namespace App\Enum;

enum ExchangeRequestStatus: string
{
    case PENDING   = 'pending';
    case ACCEPTED  = 'accepted';
    case DECLINED  = 'declined';
    case CANCELLED = 'cancelled';
}