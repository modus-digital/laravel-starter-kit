<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Avatar extends Component
{
    public bool $editable = false;

    public string $size = 'w-16 h-16';

    public function render(): View
    {
        return view('livewire.avatar');
    }
}
