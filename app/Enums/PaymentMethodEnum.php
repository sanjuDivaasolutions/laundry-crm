<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Upi = 'upi';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Card => 'Card',
            self::Upi => 'UPI',
            self::Other => 'Other',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Cash => 'success',
            self::Card => 'info',
            self::Upi => 'primary',
            self::Other => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'heroicon-m-banknotes',
            self::Card => 'heroicon-m-credit-card',
            self::Upi => 'heroicon-m-device-phone-mobile',
            self::Other => 'heroicon-m-ellipsis-horizontal-circle',
        };
    }
}
