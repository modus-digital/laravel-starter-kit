@use('App\Enums\Settings\Appearance')
@use('App\Enums\Settings\Theme')

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="space-y-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('settings.appearance.title') }} & {{ __('settings.theme.title') }}
        </h2>

        <form wire:submit="updateDisplaySettings">
            <div class="space-y-4">
                <div>
                    <label for="appearance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('settings.appearance.title') }}
                    </label>
                    <select id="appearance" wire:model.live="appearance" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        @foreach (Appearance::cases() as $appearance)
                            <option
                                value="{{ $appearance->value }}"
                                {{ $this->appearance === $appearance->value ? 'selected' : '' }}
                            >
                                {{ $appearance->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('appearance')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('settings.theme.title') }}
                    </label>
                    <select id="theme" wire:model.live="theme" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        @foreach (Theme::cases() as $theme)
                            <option
                                value="{{ $theme->value }}"
                                {{ $this->theme === $theme->value ? 'selected' : '' }}
                            >
                                {{ $theme->description() }}
                            </option>
                        @endforeach
                    </select>
                    @error('theme')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 active:bg-primary-600 transition">
                        {{ __('user.settings.update') }}
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
