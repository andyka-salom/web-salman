<?php

namespace App\Enums;

enum ActionStatus: string
{
    case DO = 'do';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANNOT_DO = 'cannot_do';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match($this) {
            self::DO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANNOT_DO => 'Cannot Do',
            self::OVERDUE => 'Overdue',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DO => 'secondary',
            self::IN_PROGRESS => 'info',
            self::COMPLETED => 'success',
            self::CANNOT_DO => 'danger',
            self::OVERDUE => 'warning',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DO => 'clipboard',
            self::IN_PROGRESS => 'arrow-repeat',
            self::COMPLETED => 'check-circle-fill',
            self::CANNOT_DO => 'x-circle-fill',
            self::OVERDUE => 'exclamation-triangle-fill',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
