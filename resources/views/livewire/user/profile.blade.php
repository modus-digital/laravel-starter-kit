<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Enums\Settings\Appearance;
use App\Enums\Settings\Theme;
use App\Enums\Settings\UserSettings;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\Language;
use App\Enums\RBAC\Role;
use App\Models\User;

new #[Layout('components.layouts.app')] class extends Component
{
    public object $settings;
    public User $user;

    public function mount()
    {
        $this->user = Auth::user();

        $getSettings = function($key) {
            return collect(
                $this->user?->settings->where('key', $key)->first()?->value ?? []
            )->dot();
        };

        $localization = $getSettings(UserSettings::LOCALIZATION);
        $display = $getSettings(UserSettings::DISPLAY);
        $security = $getSettings(UserSettings::SECURITY);

        $this->settings = (object)[
            'localization' => $localization,
            'display' => $display,
            'security' => $security
        ];
    }

}
?>

<div class="py-8 px-4 sm:px-6 lg:px-8">
    <x-slot name="title">{{ __('pages.user.profile.title') }}</x-slot>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-800 dark:text-zinc-100">{{ __('pages.user.profile.title') }}</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('pages.user.profile.subtitle') }}</p>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column - User Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
                    <div class="flex items-start gap-4">
                        <!-- Avatar -->
                        <livewire:avatar :editable="true" />

                        <!-- User Info -->
                        <div class="flex-1 min-w-0">
                            <!-- Name and Role Badge -->
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $this->user?->name ?? __('user.profile.card.placeholder_name') }}
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                    {{ Role::tryFrom($this->user?->roles()->first()?->name)->getLabel() }}
                                </span>
                            </div>

                            <!-- Email -->
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $this->user?->email ?? __('user.profile.card.placeholder_email') }}
                            </p>
                        </div>
                    </div>

                    <hr class="my-8 px-4 border-zinc-300 dark:border-zinc-700">

                    <a
                        href="{{ route('app.user.profile.edit') }}"
                        class="mb-4 inline-block w-full px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-100 text-sm font-medium rounded-lg text-center transition-colors"
                        aria-label="{{ __('pages.user.profile.edit') }}"
                    >
                        <span class="flex justify-center items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('pages.user.profile.edit') }}
                        </span>
                    </a>

                    <form
                        action="{{ route('auth.logout') }}"
                        method="POST"
                        class="inline-block w-full px-4 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-700 dark:hover:bg-red-600 text-red-800 dark:text-red-100 text-sm font-medium rounded-lg text-center transition-colors"
                        aria-label="{{ __('auth.login.sign_out') }}"
                    >
                        @csrf

                        <span class="flex justify-center items-center gap-2 ">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('auth.login.sign_out') }}
                        </span>
                    </form>
                </div>
            </div>

            <!-- Right Column - All Sections -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Settings Card -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
                    <!-- Localization Settings -->
                    <fieldset class="border border-zinc-200 dark:border-zinc-600 rounded-lg p-4 mb-6">
                        <legend class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider px-2">{{ __('user.profile.overview.localization.title') }}</legend>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.localization.language') }}</span>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{  Language::from($this->settings->localization->get('locale'))->getLabel() }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.localization.date_format') }}</span>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ Carbon\CarbonImmutable::now()->format($this->settings->localization->get('date_format')) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.localization.time_format') }}</span>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ Carbon\CarbonImmutable::now()->format($this->settings->localization->get('time_format')) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.localization.timezone') }}</span>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ Carbon\CarbonImmutable::now()->timezone($this->settings->localization->get('timezone'))->format('e') }}
                                </span>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Security Settings -->
                    <fieldset class="border border-zinc-200 dark:border-zinc-600 rounded-lg p-4 mb-6">
                        <legend class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider px-2">{{ __('user.profile.overview.security.title') }}</legend>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.security.two_factor_status') }}</span>
                                <span class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    @switch($this->settings->security->get('two_factor.status'))
                                        @case(TwoFactor::DISABLED->value)
                                            <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-700 px-3 py-1 text-xs font-medium text-zinc-600 dark:text-zinc-300 ring-1 ring-inset ring-zinc-500/10 dark:ring-zinc-600/20">
                                                <span class="inline-block h-2 w-2 rounded-full bg-zinc-400 dark:bg-zinc-500 mr-1.5"></span>
                                                {{  TwoFactor::DISABLED->getDescription() }}
                                            </span>
                                            @break

                                        @case(TwoFactor::ENABLED->value)
                                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-700 px-3 py-1 text-xs font-medium text-green-600 dark:text-green-300 ring-1 ring-inset ring-green-500/10 dark:ring-green-600/20">
                                                <span class="inline-block h-2 w-2 rounded-full bg-green-400 dark:bg-green-500 mr-1.5"></span>
                                                {{  TwoFactor::ENABLED->getDescription() }}
                                            </span>
                                            @break

                                        @default
                                            <span class="text-sm text-zinc-900 dark:text-white font-medium">{{ __('common.unknown') }}</span>
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.security.two_factor_provider') }}</span>
                                <span class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    @if($this->settings->security->get('two_factor.status') === \App\Enums\Settings\TwoFactor::DISABLED->value || !$this->settings->security->get('two_factor.provider'))
                                        <span class="text-sm text-zinc-800 dark:text-zinc-100">{{ __('common.unknown') }}</span>
                                    @else
                                        @switch($this->settings->security->get('two_factor.provider'))
                                            @case(\App\Enums\Settings\TwoFactorProvider::EMAIL->value)
                                                <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-700 px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-300 ring-1 ring-inset ring-blue-500/10 dark:ring-blue-600/20">
                                                    <span class="inline-block h-2 w-2 rounded-full bg-blue-400 dark:bg-blue-500 mr-1.5"></span>
                                                    {{ \App\Enums\Settings\TwoFactorProvider::EMAIL->getLabel() }}
                                                </span>
                                                @break
                                            @case(\App\Enums\Settings\TwoFactorProvider::AUTHENTICATOR->value)
                                                <span class="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-700 px-3 py-1 text-xs font-medium text-purple-600 dark:text-purple-300 ring-1 ring-inset ring-purple-500/10 dark:ring-purple-600/20">
                                                    <span class="inline-block h-2 w-2 rounded-full bg-purple-400 dark:bg-purple-500 mr-1.5"></span>
                                                    {{ \App\Enums\Settings\TwoFactorProvider::AUTHENTICATOR->getLabel() }}
                                                </span>
                                                @break
                                            @default
                                                <span class="text-sm text-zinc-900 dark:text-white font-medium">{{ __('common.unknown') }}</span>
                                        @endswitch
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.security.password_last_updated') }}</span>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ $this->settings->security->get('password_last_changed_at')
                                        ? Carbon\CarbonImmutable::parse($this->settings->security->get('password_last_changed_at'))->format($this->settings->localization->get('date_format') . ' ' . $this->settings->localization->get('time_format'))
                                        : __('common.never')
                                    }}
                                </span>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Display Settings -->
                    <fieldset class="border border-zinc-200 dark:border-zinc-600 rounded-lg p-4">
                        <legend class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider px-2">{{ __('user.profile.overview.display.title') }}</legend>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.display.appearance') }}</span>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ Appearance::from($this->settings->display->get('appearance'))->getLabel() }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.overview.display.theme') }}</span>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ Theme::from($this->settings->display->get('theme'))->getLabel() }}
                                </span>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
                    <!-- Header for Browser Sessions Card -->
                    <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-4 mb-4">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
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
                            <x-slot name="title">{{ __('common.modals.confirmable-password.title') }}</x-slot>
                            <x-slot name="description">{{ __('common.modals.confirmable-password.description') }}</x-slot>

                            <livewire:user.sessions.clear-browser-sessions name="confirmable-password" />

                        </x-modal>
                    </div>

                    <livewire:user.sessions.show-browser-sessions />
                </div>
            </div>
        </div>
    </div>
</div>


