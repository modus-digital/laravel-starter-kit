<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

final class Preferences extends Component
{
    use Toastable;

    public string $locale = 'en';

    public string $timezone = 'UTC';

    public string $date_format = 'Y-m-d';

    public string $time_format = 'H:i';

    /**
     * @var array<string, string>
     */
    public array $dateFormats = [
        'm/d/Y' => '',
        'd/m/Y' => '',
        'Y-m-d' => '',
    ];

    /**
     * @var array<string, string>
     */
    public array $timeFormats = [
        'h:i A' => '',
        'H:i' => '',
    ];

    public function mount(): void
    {
        $user = Auth::user();
        $settings = collect($user?->settings->where('key', UserSettings::LOCALIZATION)->first()->value ?? [])->dot();

        $this->locale = (string) $settings->get('locale', $this->locale);
        $this->timezone = (string) $settings->get('timezone', $this->timezone);
        $this->date_format = (string) $settings->get('date_format', $this->date_format);
        $this->time_format = (string) $settings->get('time_format', $this->time_format);

        // Load translated format labels
        $this->dateFormats = [
            'm/d/Y' => __('user.profile.preferences.date_formats.mdy'),
            'd/m/Y' => __('user.profile.preferences.date_formats.dmy'),
            'Y-m-d' => __('user.profile.preferences.date_formats.ymd'),
        ];

        $this->timeFormats = [
            'h:i A' => __('user.profile.preferences.time_formats.12h'),
            'H:i' => __('user.profile.preferences.time_formats.24h'),
        ];
    }

    public function save(): void
    {
        $user = Auth::user();
        $localizationSetting = $user?->settings()->where('key', UserSettings::LOCALIZATION)->first();

        $localizationSetting?->updateValueAttribute(null, [
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
        ]);

        $this->success(__('common.saved'));
    }

    public function render(): View
    {
        return view('livewire.user.profile.preferences');
    }
}
