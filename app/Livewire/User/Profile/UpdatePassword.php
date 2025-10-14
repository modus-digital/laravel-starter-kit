<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

final class UpdatePassword extends Component
{
    use Toastable;

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function save(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ], [], [
            'new_password' => 'new password',
        ]);

        $user = Auth::user();

        $user?->forceFill([
            'password' => Hash::make($this->new_password),
        ])->save();

        $securitySetting = $user?->settings()->where('key', UserSettings::SECURITY)->first();
        $security = $securitySetting->value ?? [];
        data_set($security, 'password_last_changed_at', now()->toISOString());
        $securitySetting?->updateValueAttribute(null, $security);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->success(__('common.saved'));
    }

    public function render(): View
    {
        return view('livewire.user.profile.update-password');
    }
}
