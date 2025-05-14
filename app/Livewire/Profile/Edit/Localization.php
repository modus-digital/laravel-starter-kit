<?php

namespace App\Livewire\Profile\Edit;

use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

class Localization extends Component
{
    use Toastable;

    public string $language = '';
    public string $timezone = '';
    public string $dateFormat = '';

    public function mount(?Authenticatable $user = null): void
    {
        $this->user = $user;
        $settings = $user->settings->where('key', UserSettings::LOCALIZATION)->first();
        $this->localizationSettings = collect($settings->value);

        $this->language = $this->localizationSettings->get('locale');
        $this->timezone = $this->localizationSettings->get('timezone');
        $this->dateFormat = $this->localizationSettings->get('date_format');
    }

    public function updateLocalizationSettings(): void
    {
        $user = Auth::user();
        $settings = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

        $settings->updateValueAttribute(newValue: [
            'locale' => $this->language,
            'timezone' => $this->timezone,
            'date_format' => $this->dateFormat,
        ]);

        $this->success('Localization settings updated successfully');
    }

    public function render()
    {
        return view('livewire.profile.edit.localization');
    }
}
