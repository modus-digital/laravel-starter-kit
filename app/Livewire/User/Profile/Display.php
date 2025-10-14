<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

final class Display extends Component
{
    use Toastable;

    public string $appearance = 'system';

    public string $theme = 'blue';

    public function mount(): void
    {
        $user = Auth::user();
        $settings = collect($user?->settings->where('key', UserSettings::DISPLAY)->first()->value ?? [])->dot();

        $this->appearance = (string) $settings->get('appearance', $this->appearance);
        $this->theme = (string) $settings->get('theme', $this->theme);
    }

    public function save(): void
    {
        $user = Auth::user();
        $displaySetting = $user?->settings()->where('key', UserSettings::DISPLAY)->first();

        $displaySetting?->updateValueAttribute(null, [
            'appearance' => $this->appearance,
            'theme' => $this->theme,
        ]);

        $this->success(__('common.saved'));

        // Hard refresh to reflect theme/appearance changes
        $this->dispatch('reload-page');
    }

    public function render(): View
    {
        return view('livewire.user.profile.display');
    }
}
