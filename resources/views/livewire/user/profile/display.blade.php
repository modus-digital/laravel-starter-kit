<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
    <form wire:submit.prevent="save">
        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-4">{{ __('user.profile.display.title') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="block text-sm text-zinc-800 dark:text-zinc-200 mb-2">{{ __('user.profile.display.appearance') }}</span>
                    @php($appearanceOptions = [
                        \App\Enums\Settings\Appearance::SYSTEM->value => 'heroicon-o-computer-desktop',
                        \App\Enums\Settings\Appearance::DARK->value => 'heroicon-o-moon',
                        \App\Enums\Settings\Appearance::LIGHT->value => 'heroicon-o-sun',
                    ])
                    <div class="inline-flex rounded-md border border-zinc-300 dark:border-zinc-600 overflow-hidden">
                        @foreach ($appearanceOptions as $value => $icon)
                            <button type="button"
                                    wire:click="$set('appearance', '{{ $value }}')"
                                    class="px-3 py-1.5 flex items-center gap-2 text-sm font-medium transition-colors {{ $appearance === $value ? 'bg-zinc-900 text-white dark:bg-zinc-200 dark:text-zinc-900' : 'bg-white text-zinc-700 hover:bg-zinc-100 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800' }} {{ $loop->first ? 'rounded-l-md' : '' }} {{ $loop->last ? 'rounded-r-md' : 'border-r border-zinc-300 dark:border-zinc-600' }}">
                                <x-dynamic-component :component="$icon" class="w-6 h-6" />
                                {{ \App\Enums\Settings\Appearance::from($value)->getLabel() }}
                            </button>
                        @endforeach
                    </div>
                </label>

                <label class="block">
                    <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.display.theme') }}</span>
                    <select wire:model.defer="theme" class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100">
                        @foreach (\App\Enums\Settings\Theme::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->getLabel() }}</option>
                        @endforeach
                    </select>
                </label>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md">{{ __('common.save_changes') }}</button>
        </div>
    </form>
</div>


