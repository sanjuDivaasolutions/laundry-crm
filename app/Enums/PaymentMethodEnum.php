<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case Cash = 'cash';
    case Card = 'card';
    case ApplePay = 'apple_pay';
    case GooglePay = 'google_pay';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Card => 'Card',
            self::ApplePay => 'Apple Pay',
            self::GooglePay => 'Google Pay',
            self::Other => 'Other',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Cash => 'success',
            self::Card => 'info',
            self::ApplePay => 'dark',
            self::GooglePay => 'primary',
            self::Other => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'heroicon-m-banknotes',
            self::Card => 'heroicon-m-credit-card',
            self::ApplePay => 'heroicon-m-device-phone-mobile',
            self::GooglePay => 'heroicon-m-device-phone-mobile',
            self::Other => 'heroicon-m-ellipsis-horizontal-circle',
        };
    }
}
