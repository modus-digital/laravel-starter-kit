<?php

namespace App\Livewire\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DeleteProfile extends Component
{
    #[Validate('required|current_password')]
    public string $password = '';

    public ?Authenticatable $user = null;

    public function mount(?Authenticatable $user = null)
    {
        $this->user = $user;
    }

    public function deleteAccount(): RedirectResponse
    {
        $this->validate();

        $this->user->delete();
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->success(message: __('notifications.toasts.profile.deleted'));

        return to_route('login');
    }

    public function render()
    {
        return view('livewire.actions.delete-profile');
    }
}
