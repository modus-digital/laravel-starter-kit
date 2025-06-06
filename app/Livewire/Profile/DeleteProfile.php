<?php

namespace App\Livewire\Profile;

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

    /**
     * Mount the component and set the user.
     */
    public function mount(): void
    {
        $this->user = auth()->user();
    }

    /**
     * Delete the user's account.
     */
    public function deleteAccount(): RedirectResponse
    {
        $this->validate();

        if (! $this->user instanceof User) {
            return to_route('login');
        }

        $this->user->delete();
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->success(message: __('notifications.toasts.profile.deleted'));

        return to_route('login');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.profile.delete-profile');
    }
}
