<?php

use function Laravel\Folio\name;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Enums\Settings\UserSettings;
use Illuminate\Support\Arr;
use App\Enums\Settings\Language;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\Appearance;
use App\Enums\Settings\Theme;
use App\Enums\RBAC\Role;

name('user.profile');

// Define the component using an anonymous class
new class extends Component {
    public mixed $settings = [];

    public function mount() {
        $user = Auth::user();

        // Create a settings collection with nested data
        // Helper function to get settings collection
        $getSettings = function($key) use ($user) {
            return collect(
                $user?->settings->where('key', $key)->first()?->value ?? []
            )->dot();
        };

        $localization = $getSettings(UserSettings::LOCALIZATION);
        $display = $getSettings(UserSettings::DISPLAY);
        $security = $getSettings(UserSettings::SECURITY);

        // Create object-like structure that supports dot notation
        $this->settings = (object)[
            'localization' => $localization,
            'display' => $display,
            'security' => $security
        ];
    }
};

?>

<x-layouts.app title="{{ __('pages.user.profile.title') }}">
    @volt('user.profile.index')
    <div>
        <div class="mx-auto p-4">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <!-- Header Row -->
                <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('pages.user.profile.headers.personal_information') }}
                    </h2>
                     <a href="{{ route('user.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 transition">
                        {{ __('pages.user.profile.actions.edit') }}
                    </a>
                </div>

                {{-- Main content row --}}
                <div class="flex flex-col sm:flex-row items-center w-full space-y-4 sm:space-y-0 mb-6 sm:mb-0 sm:gap-x-4">
                    {{-- Block 1: Avatar and Name/Role (Equal Width on sm+) --}}
                    <div class="sm:flex-1 flex items-center justify-start space-x-4 w-full sm:w-auto">
                        {{-- Avatar --}}
                        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                            <img
                                src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->initials()) }}"
                                alt="{{ auth()->user()->name }}'s profile picture"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        {{-- Name & Role --}}
                        <div class="flex-shrink-0 text-left">
                            <span class="mb-1 inline-flex items-center rounded-md {{ Role::from(auth()->user()->roles->first()->name)->color() }} px-2 py-1 text-xs font-medium">
                                {{ Role::from(auth()->user()->roles->first()->name)->displayName() }}
                            </span>
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h1>
                        </div>
                    </div>

                     {{-- Spacer removed --}}

                    {{-- Block 2: Email (Equal Width on sm+) --}}
                    <div class="sm:flex-1 flex justify-start items-center sm:justify-center w-full sm:w-auto">
                        {{-- Mobile View (Icon + Text) --}}
                        <div class="flex sm:hidden items-center space-x-2">
                             <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91A2.25 2.25 0 0 1 2.25 6.993v-.243" /></svg>
                             <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->email }}</span>
                        </div>
                        {{-- Desktop View (Icon Box + Label/Text) --}}
                        <div class="hidden sm:flex sm:items-center sm:space-x-4">
                            <span class="inline-flex items-center justify-center h-20 w-20 rounded-md bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                <svg class="h-12 w-12 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91A2.25 2.25 0 0 1 2.25 6.993v-.243" /></svg>
                            </span>
                            <div class="flex flex-col items-start">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('user.profile.email') }}</span>
                                <span class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Block 3: Phone (Equal Width on sm+) --}}
                    <div class="sm:flex-1 flex justify-start items-center sm:justify-center w-full sm:w-auto">
                        {{-- Mobile View (Icon + Text) --}}
                        <div class="flex sm:hidden items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->phone ?? __('user.profile.phone_not_set') }}</span>
                        </div>
                        {{-- Desktop View (Icon Box + Label/Text) --}}
                        <div class="hidden sm:flex sm:items-center sm:space-x-4">
                            <span class="inline-flex items-center justify-center h-20 w-20 rounded-md bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                <svg class="h-12 w-12 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            </span>
                             <div class="flex flex-col items-start">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('user.profile.phone') }}</span>
                                <span class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->phone ?? __('user.profile.phone_not_set') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Block 4: Joined Date (Equal Width on sm+) --}}
                    <div class="sm:flex-1 flex justify-start items-center sm:justify-center w-full sm:w-auto">
                        {{-- Mobile View (Icon + Text) --}}
                        <div class="flex sm:hidden items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('user.profile.joined') }}</span>
                        </div>
                         {{-- Desktop View (Icon Box + Label/Text + Badge) --}}
                        <div class="hidden sm:flex sm:items-center sm:space-x-4">
                            <span class="inline-flex items-center justify-center h-20 w-20 rounded-md bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                <svg class="h-12 w-12 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                            </span>
                            <div class="flex flex-col items-start">
                                 <div class="flex items-center mb-1">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('user.profile.joined') }}
                                    </span>
                                    @if(auth()->user()->created_at->diffInDays(now()) <= 7)
                                        <span class="ml-1 inline-flex items-center rounded-md bg-green-50 dark:bg-green-900 px-1.5 py-0.5 text-xs font-medium text-green-700 dark:text-green-300 ring-1 ring-inset ring-green-600/20 dark:ring-green-400/30">
                                            {{ __('user.profile.new') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-lg font-medium text-gray-700 dark:text-gray-300">
                                    {{ local_date(auth()->user()->created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-1 pt-6 px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Settings Card (Styled like screenshot) -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <!-- Header for Settings Card -->
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ __('user.settings.header') }}
                        </h2>
                        <a href="{{ route('user.profile.settings') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 transition">
                            {{ __('user.settings.edit') }}
                        </a>
                    </div>

                    <div> <!-- Removed space-y-4 -->

                        <!-- Group 1: Locale -->
                        <fieldset class="border border-gray-200 dark:border-gray-700 rounded-md p-4 mb-6">
                            <legend class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400 px-1">
                                {{ __('settings.categories.localization') }}
                            </legend>
                            <div> <!-- Removed space-y-0 -->
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('settings.language.title') }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">{{ Language::from($this->settings->localization->get('locale'))->displayName() }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('settings.datetime.date_format') }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">{{ local_date(now(), $this->settings->localization->get('date_format')) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('settings.datetime.timezone') }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">{{ $this->settings->localization->get('timezone') }}</span>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Group 2: Security -->
                         <fieldset class="border border-gray-200 dark:border-gray-700 rounded-md p-4 mb-6">
                             <legend class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400 px-1">
                                {{ __('settings.categories.security') }}
                             </legend>
                              <div> <!-- Removed space-y-0 -->
                                 <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('settings.security.two_factor.status') }}
                                    </span>

                                    @switch($this->settings->security->get('two_factor.status'))
                                        @case(TwoFactor::DISABLED->value)
                                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-500/10 dark:ring-gray-600/20">
                                                <span class="inline-block h-2 w-2 rounded-full bg-gray-400 dark:bg-gray-500 mr-1.5"></span>
                                                {{  TwoFactor::DISABLED->description() }}
                                            </span>
                                            @break

                                        @case(TwoFactor::ENABLED->value)
                                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-700 px-3 py-1 text-xs font-medium text-green-600 dark:text-green-300 ring-1 ring-inset ring-green-500/10 dark:ring-green-600/20">
                                                <span class="inline-block h-2 w-2 rounded-full bg-green-400 dark:bg-green-500 mr-1.5"></span>
                                                {{  TwoFactor::ENABLED->description() }}
                                            </span>
                                            @break

                                        @default
                                            <span class="text-sm text-gray-900 dark:text-white font-medium">Unknown</span>
                                    @endswitch
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('settings.security.password.last_updated') }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">
                                        @php
                                            $lastUpdated = $this->settings->security->get('password_last_changed_at');
                                            $lastUpdated = $lastUpdated
                                                ? local_date($lastUpdated)
                                                : __('settings.security.password.last_updated_not_set');
                                        @endphp

                                        {{ $lastUpdated }}
                                    </span>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Group 3: Display -->
                         <fieldset class="border border-gray-200 dark:border-gray-700 rounded-md p-4"> <!-- Removed mb-6 -->
                            <legend class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400 px-1">
                                {{ __('settings.categories.display') }}
                            </legend>
                             <div> <!-- Removed space-y-0 -->
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('settings.appearance.title') }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">
                                        {{ Appearance::from($this->settings->display->get('appearance'))->description() }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('settings.theme.title') }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">{{ Theme::from($this->settings->display->get('theme'))->description() }}</span>
                                </div>
                            </div>
                        </fieldset>

                    </div>
                </div>

                <!-- Browser Sessions Card (Styled like screenshot) -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                     <!-- Header for Browser Sessions Card -->
                     <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ __('user.sessions.title') }}
                        </h2>
                        <button
                            type="button"
                            onclick="Livewire.dispatch('open-modal', { name: 'confirmable-password' })"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 transition"
                        >
                            {{ __('user.sessions.logout_other_sessions') }}
                        </button>

                        <x-modal name="confirmable-password">
                            <x-slot name="title">{{ __('notifications.modals.confirmable-password.title') }}</x-slot>
                            <x-slot name="description">{{ __('notifications.modals.confirmable-password.description') }}</x-slot>

                            <livewire:profile.sessions.clear-browser-sessions name="confirmable-password" />

                        </x-modal>
                    </div>

                    <livewire:profile.sessions.show-browser-sessions />

                </div>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.app>


