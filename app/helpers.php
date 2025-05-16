<?php

use App\Enums\Settings\UserSettings;
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
    function local_date(Carbon|int|string $date): Carbon|string
    {
        if (! $date instanceof Carbon) {
            $date = new Carbon($date);
        }

        if (! auth()->check()) {
            return $date->timezone(config('app.timezone'));
        }

        $localizationSettings = auth()
            ->user()
            ->settings
            ->where('key', UserSettings::LOCALIZATION)
            ->first()
            ->value;

        $settings = [
            'timezone' => $localizationSettings['timezone'],
            'date_format' => $localizationSettings['date_format'],
        ];

        return $date->setTimezone($settings['timezone'])->format($settings['date_format']);
    }
}

if (! function_exists('download_backup_codes')) {
    /**
     * Download backup codes as a CSV file.
     *
     * @param  string  $filename  The name of the file to download.
     * @param  array<string>  $backupCodes  The backup codes to include in the file.
     * @return StreamedResponse The CSV file as a streamed response.
     */
    function download_backup_codes(string $filename, array $backupCodes): StreamedResponse
    {
        return response()
            ->streamDownload(
                callback: function () use ($backupCodes): void {
                    $file = fopen('php://output', 'w');

                    if (! $file) {
                        return;
                    }

                    try {
                        fwrite($file, 'Recovery Codes for ' . config('app.name') . "\n---------\n");

                        foreach ($backupCodes as $code) {
                            fputcsv($file, [$code], escape: '\\');
                        }

                        fclose($file);
                    }
                    catch (Throwable) {
                        fclose($file);
                    }
                },
                name: $filename
            );
    }
}
