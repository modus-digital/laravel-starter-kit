<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
    <form wire:submit.prevent="save">
        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-4">{{ __('user.profile.update_password.title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block sm:col-span-2">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.update_password.current_password') }}</span>
                    <input type="password" wire:model.defer="current_password" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100" autocomplete="current-password" />
                    @error('current_password')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.update_password.new_password') }}</span>
                    <input type="password" wire:model.defer="new_password" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100" autocomplete="new-password" />
                    @error('new_password')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.update_password.confirm_new_password') }}</span>
                    <input type="password" wire:model.defer="new_password_confirmation" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100" autocomplete="new-password" />
                </label>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md">{{ __('user.profile.update_password.button') }}</button>
        </div>
    </form>
</div>


