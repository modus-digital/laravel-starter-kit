<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="space-y-4 text-gray-900 dark:text-white">
        <h2 class="text-lg font-medium">
            {{ __('settings.security.two_factor.title') }}
        </h2>

        {{ __('settings.security.two_factor.status_message') }}
    </div>

    <div class="mt-8">
        <div class="flex items-center mb-8">
            @if ($this->twoFactorSettings['status'] === 'enabled')
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm13.707-1.293a1 1 0 0 0-1.414-1.414L11 12.586l-1.793-1.793a1 1 0 0 0-1.414 1.414l2.5 2.5a1 1 0 0 0 1.414 0l4-4Z" clip-rule="evenodd"/>
                </svg>


                <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('settings.security.two_factor.enabled') }}
                </span>

                <small class="ml-2 text-xs italic text-gray-600 dark:text-gray-400">
                    ({{ __('settings.security.two_factor.confirmed_at', ['date' => local_date($this->twoFactorSettings['confirmed_at'])]) }})
                </small>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-2.22 2.22a.75.75 0 101.06 1.06L12 13.06l2.22 2.22a.75.75 0 101.06-1.06L13.06 12l2.22-2.22a.75.75 0 10-1.06-1.06L12 10.94l-2.22-2.22z" clip-rule="evenodd" />
                </svg>

                <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('settings.security.two_factor.disabled') }}
                </span>
            @endif
        </div>

        <div class="flex items-center justify-between mt-4">
            <div class="flex items-center">
                @if ($this->twoFactorSettings['status'] === 'enabled')
                    <button
                        onclick="Livewire.dispatch('open-modal', { name: 'disable-two-factor' })"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 transition"
                    >
                        {{ __('settings.security.two_factor.disable') }}
                    </button>

                    <button
                        onclick="Livewire.dispatch('open-modal', { name: 'regenerate-backup-codes' }); Livewire.dispatch('regenerate-backup-codes')"
                        class="ml-2 inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none focus:border-gray-700 dark:focus:border-gray-600 focus:ring focus:ring-gray-200 dark:focus:ring-gray-500 active:bg-gray-600 dark:active:bg-gray-700 transition"
                    >
                        {{ __('settings.security.two_factor.regenerate_backup_code') }}
                    </button>
                @else
                    <button
                        type="submit"
                        onclick="Livewire.dispatch('open-modal', { name: 'setup-two-factor' })"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 active:bg-primary-600 transition"
                    >
                        {{ __('settings.security.two_factor.enable') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
