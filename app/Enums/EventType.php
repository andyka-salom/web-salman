<?php

namespace App\Enums;

enum EventType: string
{
    case HSE = 'hse';
    case SECURITY = 'security';

    public function label(): string
    {
        return match($this) {
            self::HSE => 'HSE',
            self::SECURITY => 'Security',
        };
    }
}

