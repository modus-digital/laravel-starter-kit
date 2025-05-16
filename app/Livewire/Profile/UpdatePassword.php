<?php

namespace App\Livewire\Profile;

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

    public function save()
    {
        $this->validate();

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);
    }

    public function render()
    {
        return view('livewire.profile.update-password');
    }
}
