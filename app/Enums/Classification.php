<?php

namespace App\Enums;

enum Classification: string
{
    case POSITIVE_ASPECT = 'positive_aspect';
    case UNSAFE_CONDITION = 'unsafe_condition';
    case UNSAFE_ACTION = 'unsafe_action';
    case NEAR_MISS = 'near_miss';
    case INCIDENT = 'incident';

    public function label(): string
    {
        return match($this) {
            self::POSITIVE_ASPECT => 'Positive Aspect',
            self::UNSAFE_CONDITION => 'Unsafe Condition',
            self::UNSAFE_ACTION => 'Unsafe Action',
            self::NEAR_MISS => 'Near Miss',
            self::INCIDENT => 'Incident',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::POSITIVE_ASPECT => 'success',
            self::UNSAFE_CONDITION => 'warning',
            self::UNSAFE_ACTION => 'primary',
            self::NEAR_MISS => 'info',
            self::INCIDENT => 'danger',
        };
    }
}
