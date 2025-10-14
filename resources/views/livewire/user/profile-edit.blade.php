<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Enums\Settings\Appearance;
use App\Enums\Settings\Theme;
use App\Enums\Settings\Language;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use App\Enums\RBAC\Role;
use App\Models\User;

new #[Layout('components.layouts.app')] class extends Component
{
    public User $user;
    public bool $confirming_account_deletion = false;

    public function mount(): void
    {
        $this->user = Auth::user();
    }

    public function deleteAccount(): void
    {
        $this->validate([
            'delete_password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        if (! $user) {
            return;
        }

        // Remove stored avatar if present
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Remove user settings (no FK cascade defined)
        $user->settings()->delete();

        // Log the user out before deleting the account and invalidate session
        Auth::logout();

        $user->delete();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirectRoute('login');
    }
};
?>

<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-800 dark:text-zinc-100">{{ __('pages.user.profile.edit') }}</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('pages.user.profile.subtitle') }}</p>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - User Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
                    <div class="flex items-start gap-4">
                        <!-- Avatar -->
                        <livewire:avatar :editable="true" />

                        <!-- User Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $this->user?->name ?? __('user.profile.card.placeholder_name') }}
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                    {{ Role::tryFrom($this->user?->roles->first()->name)->getLabel() }}
                                </span>
                            </div>

                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $this->user?->email ?? __('user.profile.card.placeholder_email') }}
                            </p>
                        </div>
                    </div>

                    <hr class="my-8 px-4 border-zinc-300 dark:border-zinc-700">

                    <a
                        href="{{ route('app.user.profile') }}"
                        class="mb-4 inline-block w-full px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-100 text-sm font-medium rounded-lg text-center transition-colors"
                        aria-label="{{ __('common.go_back') }}"
                    >
                        <span class="flex justify-center items-center gap-2">
                            {{ __('common.go_back') }}
                        </span>
                    </a>

                    <!-- Delete Account -->
                    <div class="ml-auto">
                        <button
                            type="button"
                            wire:click="$toggle('confirming_account_deletion')"
                            class="mb-4 inline-block w-full px-4 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-700 dark:hover:bg-red-600 text-red-800 dark:text-red-100 text-sm font-medium rounded-lg text-center transition-colors"
                            aria-label="{{ __('user.profile.card.delete_account.button') }}"
                        >
                            {{ __('user.profile.card.delete_account.button') }}
                        </button>
                    </div>

                    @if ($confirming_account_deletion)
                    <div class="mt-4 p-4 border border-red-300 dark:border-red-800 rounded-lg bg-red-50 dark:bg-red-900/20">
                        <p class="text-sm text-red-700 dark:text-red-300">
                            {{ __('user.profile.card.delete_account.description') }}
                        </p>

                        <form wire:submit.prevent="deleteAccount" class="mt-4">
                            <label class="block">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.card.delete_account.password_label') }}</span>
                                <input type="password" wire:model.defer="delete_password" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100" autocomplete="current-password" />
                                @error('delete_password')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <div class="flex gap-2 mt-4">
                                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-700 hover:bg-red-600 dark:bg-red-500 dark:hover:bg-red-400 rounded-md">
                                    {{ __('user.profile.card.delete_account.confirm') }}
                                </button>
                                <button type="button" wire:click="$set('confirming_account_deletion', false)" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-md">
                                    {{ __('common.cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Column - Edit Sections -->
            <div class="lg:col-span-2 space-y-6">
                <livewire:user.profile.preferences />
                <livewire:user.profile.display />
                <livewire:user.profile.update-password />
                <livewire:user.profile.two-factor />
            </div>
        </div>
    </div>
</div>
