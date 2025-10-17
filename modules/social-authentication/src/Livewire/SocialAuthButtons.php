<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use ModusDigital\SocialAuthentication\Models\SocialiteProvider;

final class SocialAuthButtons extends Component
{
    public function render(): View
    {
        $providers = SocialiteProvider::enabled()
            ->ordered()
            ->get();

        return view('social-authentication::livewire.social-auth-buttons', [
            'providers' => $providers,
        ]);
    }
}
