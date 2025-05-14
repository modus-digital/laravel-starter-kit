<?php

namespace App\Livewire\Profile\TwoFactor;

use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

class DisableTwoFactor extends Component
{
    use Toastable;

    #[Validate('required|current_password')]
    public string $password = '';

    public function disable()
    {
        $this->validate();

        $user = auth()->user();
        $user->settings->where('key', UserSettings::SECURITY)->first()->updateValueAttribute(
            path: 'two_factor',
            newValue: [
                'status' => TwoFactor::DISABLED->value,
                'secret' => null,
                'confirmed_at' => null,
                'recovery_codes' => [],
            ]
        );

        $this->success(message: __('notifications.toasts.two_factor.disabled'));
        $this->dispatch('close-modal');
        $this->dispatch('two-factor-status-updated');
    }


    public function render()
    {
        return view('livewire.profile.two-factor.disable-two-factor');
    }
}
