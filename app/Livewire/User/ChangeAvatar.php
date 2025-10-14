<?php

declare(strict_types=1);

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toastable;

final class ChangeAvatar extends Component
{
    use Toastable;
    use WithFileUploads;

    #[Validate('nullable|image|max:5120')] // 5MB max
    public TemporaryUploadedFile|string|null $avatar = null;

    public ?string $currentAvatar = null;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        $this->currentAvatar = $user?->avatar_url;
    }

    public function updatedAvatar(): void
    {
        $this->validate();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->avatar instanceof TemporaryUploadedFile) {
            /** @var User $user */
            $user = auth()->user();

            // Delete old avatar if exists
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Store new avatar
            $path = $this->avatar->store('avatars', 'public');

            if ($path === false) {
                $this->error(__('user.avatar.messages.upload_failed'));

                return;
            }

            // Update user
            $user->update([
                'avatar_path' => $path,
            ]);

            $this->currentAvatar = Storage::disk('public')->url($path);

            $this->success(__('user.avatar.messages.updated'));

            // Reset the form
            $this->avatar = null;

            // Refresh the component and close modal
            $this->dispatch('avatar-updated');
            $this->dispatch('close-modal', name: 'change-avatar');
        }
    }

    public function removeAvatar(): void
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update([
            'avatar_path' => null,
        ]);

        $this->currentAvatar = null;
        $this->avatar = null;

        $this->success(__('user.avatar.messages.removed'));

        $this->dispatch('avatar-updated');
        $this->dispatch('close-modal', name: 'change-avatar');
    }

    public function render(): View
    {
        return view('livewire.user.change-avatar');
    }
}
