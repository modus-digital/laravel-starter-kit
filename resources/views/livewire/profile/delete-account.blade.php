<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="space-y-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Delete Account') }}
        </h2>

        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </div>

        <button
            onclick="Livewire.dispatch('open-modal', { name: 'delete-profile' })"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 transition">
            {{ __('Delete Account') }}
        </button>

        <x-modal name="delete-profile">
            <x-slot name="title">
                {{ __('Delete Account') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Are you sure you want to delete your account? This action cannot be undone.') }}
            </x-slot>

            <livewire:actions.delete-profile />
        </x-modal>
    </div>
</div>
