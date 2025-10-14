<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
    <form wire:submit.prevent="save">
        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-4">{{ __('user.profile.preferences.title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.preferences.language') }}</span>
                    <select wire:model.defer="locale" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100">
                        @foreach (\App\Enums\Settings\Language::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->getLabel() }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.preferences.date_format') }}</span>
                    <select wire:model.defer="date_format" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100">
                        @foreach ($dateFormats as $format => $label)
                            <option value="{{ $format }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.preferences.time_format') }}</span>
                    <select wire:model.defer="time_format" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100">
                        @foreach ($timeFormats as $format => $label)
                            <option value="{{ $format }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.preferences.timezone') }}</span>
                    <select wire:model.defer="timezone" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100">
                        @foreach (\DateTimeZone::listIdentifiers() as $tz)
                            <option value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </select>
                </label>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md">{{ __('common.save_changes') }}</button>
        </div>
    </form>
</div>


