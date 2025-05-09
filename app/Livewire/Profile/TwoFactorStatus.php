<?php

namespace App\Livewire\Profile;

use App\Enums\Settings\UserSettings;
use App\Models\UserSetting;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

class TwoFactorStatus extends Component
{
    public UserSetting $userSetting;

    public function mount(?Authenticatable $user = null): void
    {
        $this->userSetting = $user->settings->get(UserSettings::SECURITY);

        // dump($this->userSetting);
    }

    public function render()
    {
        return view('livewire.profile.two-factor-status');
    }
}
