<?php

namespace App\Enums;

enum ProcessingStatusEnum: string
{
    case Pending = 'Pending';
    case Washing = 'Washing';
    case Drying = 'Drying';
    case Ready = 'Ready Area';
    case Delivered = 'Delivered';
}
