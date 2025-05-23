<?php

namespace App\Livewire\Profile;

use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TwoFactorStatus extends Component
{
    public ?array $twoFactorSettings = null;

    /**
     * Mount the component and set the user.
     * This component will be re-rendered if the event 'two-factor-status-updated' is dispatched.
     */
    #[On('two-factor-status-updated')]
    public function mount(?Authenticatable $user = null): void
    {
        $this->twoFactorSettings = $user
            ->settings
            ->first()
            ->retrieve(UserSettings::SECURITY, 'two_factor');
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('livewire.profile.two-factor-status');
    }
}
