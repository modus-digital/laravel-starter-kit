<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteAccount extends Component
{
    public function render()
    {
        return view('livewire.profile.delete-account');
    }
}
