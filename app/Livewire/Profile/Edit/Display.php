<?php

namespace App\Livewire\Profile\Edit;

use App\Enums\Settings\UserSettings;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

class Display extends Component
{
    use Toastable;

    protected ?User $user = null;

    /**
     * @var Collection<string, mixed>
     */
    public Collection $displaySettings;

    public string $appearance = '';

    public string $theme = '';

    public function mount(): void
    {
        $this->user = auth()->user();
        $settings = $this->user->settings->where('key', UserSettings::DISPLAY)->first();
        $this->displaySettings = collect($settings->value);

        $this->appearance = $this->displaySettings->get('appearance');
        $this->theme = $this->displaySettings->get('theme');
    }

    public function updateDisplaySettings(): void
    {
        $user = Auth::user();
        $settings = $user->settings->where('key', UserSettings::DISPLAY)->first();

        $settings->updateValueAttribute(newValue: [
            'appearance' => $this->appearance,
            'theme' => $this->theme,
        ]);

        $this->success('Display settings updated successfully');
    }

    public function render(): View
    {
        return view('livewire.profile.edit.display');
    }
}
