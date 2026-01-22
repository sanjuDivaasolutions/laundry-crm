<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Partial => 'Partially Paid',
            self::Paid => 'Paid',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Unpaid => 'danger',
            self::Partial => 'warning',
            self::Paid => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Unpaid => 'heroicon-m-exclamation-circle',
            self::Partial => 'heroicon-m-clock',
            self::Paid => 'heroicon-m-check-circle',
        };
    }
}
