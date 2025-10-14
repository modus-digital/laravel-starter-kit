<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-700 p-6">
    <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-4">{{ __('user.profile.two_factor.title') }}</h2>

    <!-- 2FA status -->
    <div class="flex items-center justify-between py-2">
        <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.two_factor.status') }}</span>
        <span class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
            @switch($currentStatus)
                @case(\App\Enums\Settings\TwoFactor::DISABLED->value)
                    <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-700 px-3 py-1 text-xs font-medium text-zinc-600 dark:text-zinc-300 ring-1 ring-inset ring-zinc-500/10 dark:ring-zinc-600/20">
                        <span class="inline-block h-2 w-2 rounded-full bg-zinc-400 dark:bg-zinc-500 mr-1.5"></span>
                        {{ \App\Enums\Settings\TwoFactor::DISABLED->getDescription() }}
                    </span>
                    @break
                @case(\App\Enums\Settings\TwoFactor::ENABLED->value)
                    <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-700 px-3 py-1 text-xs font-medium text-green-600 dark:text-green-300 ring-1 ring-inset ring-green-500/10 dark:ring-green-600/20">
                        <span class="inline-block h-2 w-2 rounded-full bg-green-400 dark:bg-green-500 mr-1.5"></span>
                        {{ \App\Enums\Settings\TwoFactor::ENABLED->getDescription() }}
                    </span>
                    @break
                @default
                    <span class="text-sm text-zinc-900 dark:text-white font-medium">{{ __('common.unknown') }}</span>
            @endswitch
        </span>
    </div>

    @if ($currentStatus === \App\Enums\Settings\TwoFactor::ENABLED->value)
        <!-- 2FA Provider -->
        <div class="flex items-center justify-between py-2">
            <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.two_factor.provider') }}</span>
            <span class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                @if($currentStatus === \App\Enums\Settings\TwoFactor::DISABLED->value || !$provider)
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('common.unknown') }}</span>
                @else
                    @switch($provider)
                        @case(\App\Enums\Settings\TwoFactorProvider::EMAIL)
                            <span class="text-zinc-900 dark:text-white">
                                {{ \App\Enums\Settings\TwoFactorProvider::EMAIL->getLabel() }}
                            </span>
                            @break
                        @case(\App\Enums\Settings\TwoFactorProvider::AUTHENTICATOR)
                            <span class="text-zinc-900 dark:text-white">
                                {{ \App\Enums\Settings\TwoFactorProvider::AUTHENTICATOR->getLabel() }}
                            </span>
                            @break
                        @default
                            <span class="text-sm text-zinc-900 dark:text-white font-medium">{{ __('common.unknown') }}</span>
                    @endswitch
                @endif
            </span>
        </div>

        <!-- Recovery codes used -->
        <div class="flex items-center justify-between py-2">
            <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ __('user.profile.two_factor.recovery_codes') }}</span>
            <span class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                <span class="text-sm text-zinc-900 dark:text-white">
                    {{ __('enums.settings.two_factor.recovery_codes.used', ['used' => $recoveryCodes['used'], 'total' => $recoveryCodes['total']]) }}
                </span>
            </span>
        </div>
    @endif

    <!-- CTA -->
    <div class="flex items-center justify-end gap-3 mt-8">
            @if ($currentStatus === \App\Enums\Settings\TwoFactor::ENABLED->value)
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-modal', { name: 'disable-two-factor-confirmation' })"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-900 hover:bg-red-800 dark:bg-red-200 dark:text-red-900 dark:hover:bg-red-300 rounded-md"
                >
                    {{ __('enums.settings.two_factor.action.disabled') }}
                </button>
            @else
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-modal', { name: 'setup-two-factor-wizzard' })"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md"
                >
                    {{ __('enums.settings.two_factor.action.enabled') }}
                </button>
            @endif
    </div>

    <x-modal name="setup-two-factor-wizzard" title="{{ __('enums.settings.two_factor.action.enabled') }}" size="lg">
        <livewire:actions.setup-two-factor-wizzard />
    </x-modal>

    <x-modal name="disable-two-factor-confirmation" title="{{ __('enums.settings.two_factor.action.disabled') }}" size="md">
        <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-6">
            {{ __('auth.two_factor.messages.disable_confirmation') }}
        </p>

        <div class="flex justify-end gap-3">
            <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-600 transition-colors"
                x-on:click="$dispatch('close-modal', { name: 'disable-two-factor-confirmation' })"
            >
                {{ __('common.cancel') }}
            </button>

            <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-white bg-red-900 hover:bg-red-800 dark:bg-red-200 dark:text-red-900 dark:hover:bg-red-300 rounded-md"
                wire:click="disable"
            >
                {{ __('enums.settings.two_factor.action.disabled') }}
            </button>
        </div>
    </x-modal>
</div>


