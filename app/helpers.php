<?php

use App\Helpers\FeatureStatus;
use App\Models\ApplicationSetting;
use Carbon\Carbon;

if (!function_exists('feature')) {
    /**
     * Retrieve a feature's status.
     *
     * @param string $featureKey The name of the feature setting.
     * @return FeatureStatus An object representing the feature's status.
     */
    function feature(string $featureKey): FeatureStatus
    {
        // Fetch the setting, default to null if not found
        $setting = ApplicationSetting::where('name', $featureKey)->first();
        $value = $setting ? $setting->value : null; // Default to null if setting doesn't exist

        // Return a FeatureStatus object
        return new FeatureStatus($value);
    }
}

if (!function_exists('local_date')) {
    function local_date(Carbon|int|string $date)
    {
        if (! $date instanceof Carbon) {
            $date = new Carbon($date);
        }

        if (! auth()->check()) {
            return $date->timezone(config('app.timezone'));
        }

        // TODO: Get the user's timezone and date format from the database once settings are implemented
        $settings = [
            'timezone' => 'Europe/Amsterdam',
            'date_format' => 'd-m-Y H:i',
        ];

        return $date->setTimezone($settings['timezone'])->format($settings['date_format']);
    }
}
