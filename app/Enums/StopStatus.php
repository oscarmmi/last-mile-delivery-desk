<?php

namespace App\Enums;

enum StopStatus: string
{
    case Queued = 'queued';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Failed = 'failed';
}
