<?php

namespace App\Enums;

enum UploadStage: string
{
    case INITIAL_REPORT = 'initial_report';
    case REVIEW = 'review';
    case ACTION_ITEM = 'action_item';
    case CLOSEOUT = 'closeout';
    case UPDATE = 'update';

    public function label(): string
    {
        return match($this) {
            self::INITIAL_REPORT => 'Initial Report',
            self::REVIEW => 'Review',
            self::ACTION_ITEM => 'Action Item',
            self::CLOSEOUT => 'Close-out',
            self::UPDATE => 'Update',
        };
    }
}
