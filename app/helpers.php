<?php

use App\Helpers\FeatureStatus;
use App\Models\ApplicationSetting;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

if (! function_exists('feature')) {
    /**
     * Retrieve a feature's status.
     *
     * @param  string  $featureKey  The name of the feature setting.
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

if (! function_exists('local_date')) {
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
            'timezone' => config('app.default_timezone', 'UTC'),
            'date_format' => config('app.default_date_format', 'd-m-Y H:i'),
        ];

        return $date->setTimezone($settings['timezone'])->format($settings['date_format']);
    }
}

if (! function_exists('download_backup_codes')) {
    function download_backup_codes(string $filename, array $backupCodes): StreamedResponse
    {
        return response()
            ->streamDownload(
                callback: function () use ($backupCodes) {
                    $file = fopen('php://output', 'w');

                    if (! $file) {
                        fclose($file);

                        return;
                    }

                    try {
                        fwrite($file, 'Recovery Codes for ' . config('app.name') . "\n---------\n");

                        foreach ($backupCodes as $code) {
                            fputcsv($file, [$code]);
                        }

                        fclose($file);
                    }
                    catch (Throwable $e) {
                        fclose($file);
                    }
                },
                name: $filename
            );
    }
}
