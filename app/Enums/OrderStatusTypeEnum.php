<?php

namespace App\Enums;

enum OrderStatusTypeEnum: string
{
    case Processing = 'processing';
    case Payment = 'payment';
    case Order = 'order';
}
