<?php

namespace App\Livewire\Profile;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class UpdatePassword extends Component
{
    #[Validate('required|current_password')]
    public string $current_password;

    #[Validate('required|string|min:8|confirmed')]
    public string $password;

    #[Validate('required|string|min:8')]
    public string $password_confirmation;

    /**
     * Save the new password.
     */
    public function save(): void
    {
        $this->validate();

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.profile.update-password');
    }
}
