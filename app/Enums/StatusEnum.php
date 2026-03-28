<?php

namespace App\Enums;

enum StatusEnum: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::PENDING => $target === self::IN_PROGRESS,
            self::IN_PROGRESS => $target === self::DONE,
            self::DONE => false,
        };
    }
}
