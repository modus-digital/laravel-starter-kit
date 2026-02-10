<?php

declare(strict_types=1);

namespace App\Traits\Enums;

/**
 * This trait provides an options() method for enums.
 */
trait HasOptions
{
    /**
     * This method should return the label of the enum.
     * It is used to get the localized label for the enum.
     */
    abstract public function getLabel(): string;

    /**
     * This method will return an array of options for the enum.
     * Uses the getLabel() method from HasLabel interface to get localized labels.
     *
     * This method is used to populate the select options in the UI.
     *
     * @return array<mixed, string>
     */
    public static function options(): array
    {
        return array_combine(
            keys: array_map(fn (self $case): string => (string) $case->value, self::cases()),
            values: array_map(fn (self $case): string => $case->getLabel(), self::cases())
        );
    }
}
