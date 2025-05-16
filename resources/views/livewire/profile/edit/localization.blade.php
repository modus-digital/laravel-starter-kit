@use('App\Enums\Settings\Language')

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="space-y-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('settings.categories.localization') }}
        </h2>

        <form wire:submit="updateLocalizationSettings">
            <div class="space-y-4">
                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('settings.language.title') }}
                    </label>
                    <select id="language" wire:model.live="language" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        @foreach (Language::cases() as $language)
                            <option
                                value="{{ $language->value }}"
                                {{ $this->language === $language->value ? 'selected' : '' }}
                            >
                                {{ $language->displayName() }}
                            </option>
                        @endforeach
                    </select>
                    @error('language')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('settings.datetime.timezone') }}
                    </label>
                    <select id="timezone" wire:model.live="timezone" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        @php
                            $timezones = DateTimeZone::listIdentifiers();
                            $regions = [
                                'Africa' => DateTimeZone::AFRICA,
                                'America' => DateTimeZone::AMERICA,
                                'Antarctica' => DateTimeZone::ANTARCTICA,
                                'Asia' => DateTimeZone::ASIA,
                                'Atlantic' => DateTimeZone::ATLANTIC,
                                'Australia' => DateTimeZone::AUSTRALIA,
                                'Europe' => DateTimeZone::EUROPE,
                                'Indian' => DateTimeZone::INDIAN,
                                'Pacific' => DateTimeZone::PACIFIC
                            ];
                        @endphp

                        @foreach($regions as $region => $mask)
                            <optgroup label="{{ $region }}">
                                @foreach(DateTimeZone::listIdentifiers($mask) as $tz)
                                    <option
                                        value="{{ $tz }}"
                                        {{ $this->timezone === $tz ? 'selected' : '' }}
                                    >
                                        {{ str_replace('_', ' ', $tz) }} ({{ (new DateTime('now', new DateTimeZone($tz)))->format('P') }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('timezone')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="dateFormat" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('settings.datetime.date_format') }}
                    </label>
                    <select id="dateFormat" wire:model.live="dateFormat" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="d-m-Y H:i">{{ date('d-m-Y H:i') }} ({{ __('settings.datetime.formats.day_month_year_time') }})</option>
                        <option value="d-m-Y">{{ date('d-m-Y') }} ({{ __('settings.datetime.formats.day_month_year') }})</option>
                        <option value="d/m/Y">{{ date('d/m/Y') }} ({{ __('settings.datetime.formats.day_month_year_slash') }})</option>
                        <option value="d.m.Y">{{ date('d.m.Y') }} ({{ __('settings.datetime.formats.day_month_year_dot') }})</option>
                        <option value="Y-m-d">{{ date('Y-m-d') }} ({{ __('settings.datetime.formats.year_month_day') }})</option>
                        <option value="m/d/Y">{{ date('m/d/Y') }} ({{ __('settings.datetime.formats.month_day_year') }})</option>
                    </select>
                    @error('dateFormat')
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
