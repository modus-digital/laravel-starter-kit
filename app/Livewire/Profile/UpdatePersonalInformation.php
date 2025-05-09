<?php

namespace App\Livewire\Profile;

use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

class UpdatePersonalInformation extends Component
{
    use Toastable;

    #[Validate('required|string|max:255')]
    public string $name;

    public string $email;

    #[Validate('nullable|string|max:255')]
    public ?string $phone;

    public function mount(?Authenticatable $user)
    {
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? null;
    }

    public function save()
    {
        $this->validate();

        auth()->user()->update([
            'name' => $this->name,
            'phone' => $this->phone,
        ]);

        $this->success(message: 'Updated your personal information');
    }

    public function render()
    {
        return view('livewire.profile.update-personal-information');
    }
}
