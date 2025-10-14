<?php

declare(strict_types=1);

namespace App\Traits\Enums;

trait HasValues
{
    /**
     * This method will return an array of values for the enum.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            callback: fn (self $case): string => (string) $case->value,
            array: self::cases()
        );
    }
}
