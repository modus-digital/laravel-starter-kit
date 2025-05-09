<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="space-y-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Update Personal Information') }}
        </h2>

        <form wire:submit="save">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Name') }}
                    </label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500" />
                    @error('name')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Email') }}
                    </label>
                    <input type="email" id="email" wire:model="email" disabled class="disabled:bg-gray-200 dark:disabled:bg-gray-700 mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500" />
                    @error('email')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Phone') }}
                    </label>
                    <input type="text" id="phone" wire:model="phone" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500" />
                    @error('phone')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 active:bg-primary-600 transition">
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
