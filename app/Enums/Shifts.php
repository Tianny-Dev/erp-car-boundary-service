<?php

namespace App\Enums;

enum Shifts: string
{
    case MORNING = 'Morning';
    case AFTERNOON = 'Afternoon';
    case EVENING = 'Evening';

    public function label(): string
    {
        return match ($this) {
            self::MORNING => 'Morning',
            self::AFTERNOON => 'Afternoon',
            self::EVENING => 'Evening',
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
