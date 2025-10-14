<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ __('user.avatar.title') }}</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('user.avatar.description') }}
            </p>
        </div>

        <!-- Upload Progress -->
        <div wire:loading wire:target="avatar" class="mb-4 p-4 w-full bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-center gap-3">
                <svg class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ __('user.avatar.processing.title') }}</p>
                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">{{ __('user.avatar.processing.message') }}</p>
                </div>
            </div>
        </div>

        <form wire:submit="save">
            <x-image-upload
                wire:model="avatar"
                id="avatar-upload"
                label="{{ __('user.avatar.form.label') }}"
                :preview="$currentAvatar"
                accept="image/*"
                maxSize="5MB"
            />

            @error('avatar')
                <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                    </div>
                </div>
            @enderror

            <div
                @class([
                    'mt-6 flex items-center gap-3' => $currentAvatar,
                    'mt-6 w-full' => !$currentAvatar,
                ])
            >
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="avatar, save"
                    @class([
                        'px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors duration-200 flex items-center gap-2 shadow-sm',
                        'w-full justify-center' => !$currentAvatar,
                    ])
                >
                    <svg wire:loading.remove wire:target="save" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg wire:loading wire:target="save" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ __('user.avatar.form.save') }}</span>
                    <span wire:loading wire:target="save">{{ __('user.avatar.form.saving') }}</span>
                </button>

                @if($currentAvatar)
                    <button
                        type="button"
                        wire:click="removeAvatar"
                        wire:loading.attr="disabled"
                        wire:target="removeAvatar"
                        wire:confirm="{{ __('user.avatar.form.confirm_remove') }}"
                        class="px-6 py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 text-red-700 dark:text-red-400 font-medium rounded-lg transition-colors duration-200 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ __('user.avatar.form.remove') }}
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
