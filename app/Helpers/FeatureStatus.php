<?php

namespace App\Helpers;

use Stringable;

// Define the FeatureStatus class
class FeatureStatus implements Stringable
{
    public function __construct(protected mixed $value) {}

    /**
     * Checks if the feature is enabled.
     * Considers '1', 'true', 'on', 'yes' (case-insensitive) or boolean true as enabled.
     */
    public function enabled(): bool
    {
        // Handles common truthy string values and boolean true
        return
            in_array(strtolower((string) $this->value), ['1', 'true', 'on', 'yes', 'enabled'], true) ||
            filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
    }

    public function disabled(): bool
    {
        return ! $this->enabled();
    }

    /**
     * Returns the raw value of the feature setting.
     */
    public function value(): mixed
    {
        return $this->value;
    }

    /**
     * Returns the string representation of the value.
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
