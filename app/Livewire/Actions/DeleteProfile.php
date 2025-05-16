<?php

namespace App\Livewire\Actions;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DeleteProfile extends Component
{
    #[Validate('required|current_password')]
    public string $password = '';

    public ?User $user = null;

    public function mount(): void
    {
        $this->user = auth()->user();
    }

    public function deleteAccount(): RedirectResponse
    {
        $this->validate();

        if (! $this->user) {
            return to_route('login');
        }

        $this->user->delete();
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->success(message: __('notifications.toasts.profile.deleted'));

        return to_route('login');
    }

    public function render(): View
    {
        return view('livewire.actions.delete-profile');
    }
}
