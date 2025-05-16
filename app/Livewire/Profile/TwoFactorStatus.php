<?php

namespace App\Livewire\Profile;

use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Attributes\On;
use Livewire\Component;

class TwoFactorStatus extends Component
{
    public ?array $twoFactorSettings = null;

    #[On('two-factor-status-updated')]
    public function mount(?Authenticatable $user = null): void
    {
        $this->twoFactorSettings = $user
            ->settings
            ->first()
            ->retrieve(UserSettings::SECURITY, 'two_factor');
    }

    public function render()
    {
        return view('livewire.profile.two-factor-status');
    }
}
