<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="space-y-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('user.account.delete.title') }}
        </h2>

        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('user.account.delete.description') }}
        </div>

        <button
            onclick="Livewire.dispatch('open-modal', { name: 'delete-profile' })"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 transition">
            {{ __('user.account.delete.button') }}
        </button>

        <x-modal name="delete-profile">
            <x-slot name="title">
                {{ __('user.account.delete.title') }}
            </x-slot>

            <x-slot name="description">
                {{ __('user.account.delete.confirmation') }}
            </x-slot>

            <livewire:actions.delete-profile />
        </x-modal>
    </div>
</div>
