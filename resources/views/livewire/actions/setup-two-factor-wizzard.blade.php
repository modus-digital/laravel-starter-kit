<div
    class="space-y-6 px-6 py-4"
    x-data
    x-on:open-modal.window="if ($event.detail.name === 'setup-two-factor-wizzard') { $wire.call('resetWizard') }"
    x-on:download-recovery-codes.window="(() => {
        const { filename, codes } = $event.detail;
        const blob = new Blob([codes.join('\n')], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    })()"
>
    <!-- Progress Steps -->
    <div class="flex items-center justify-between mb-6">
        @for ($i = 1; $i <= 4; $i++)
            <div class="flex items-center {{ $i < 4 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all',
                        'bg-primary-600 text-white' => $currentStep >= $i,
                        'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' => $currentStep < $i,
                    ])>
                        @if ($currentStep > $i)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            {{ $i }}
                        @endif
                    </div>
                    <span class="text-xs mt-2 text-zinc-600 dark:text-zinc-400 hidden sm:block">
                        @switch($i)
                            @case(1) {{ __('auth.two_factor.wizard.steps.provider') }} @break
                            @case(2) {{ __('auth.two_factor.wizard.steps.verify') }} @break
                            @case(3) {{ __('auth.two_factor.wizard.steps.backup') }} @break
                            @case(4) {{ __('auth.two_factor.wizard.steps.done') }} @break
                        @endswitch
                    </span>
                </div>

                @if ($i < 4)
                    <div @class([
                        'flex-1 h-1 mx-2 transition-all',
                        'bg-primary-600' => $currentStep > $i,
                        'bg-zinc-200 dark:bg-zinc-700' => $currentStep <= $i,
                    ])></div>
                @endif
            </div>
        @endfor
    </div>

    <!-- Step 1: Select Provider -->
    @if ($currentStep === 1)
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                    {{ __('auth.two_factor.wizard.provider.title') }}
                </h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('auth.two_factor.wizard.provider.description') }}
                </p>
            </div>

            <div class="grid gap-4">
                <!-- Email Option -->
                <button
                    type="button"
                    wire:click="selectProvider('{{ \App\Enums\Settings\TwoFactorProvider::EMAIL->value }}')"
                    class="flex items-start p-4 border-2 border-zinc-300 dark:border-zinc-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-left group"
                >
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('auth.two_factor.wizard.provider.options.email.title') }}</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            {{ __('auth.two_factor.wizard.provider.options.email.description') }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-zinc-400 group-hover:text-primary-500 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <!-- Authenticator App Option -->
                <button
                    type="button"
                    wire:click="selectProvider('{{ \App\Enums\Settings\TwoFactorProvider::AUTHENTICATOR->value }}')"
                    class="flex items-start p-4 border-2 border-zinc-300 dark:border-zinc-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-left group"
                >
                    <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('auth.two_factor.wizard.provider.options.authenticator.title') }}</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            {{ __('auth.two_factor.wizard.provider.options.authenticator.description') }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-zinc-400 group-hover:text-primary-500 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 2: Verification -->
    @if ($currentStep === 2)
        <div class="space-y-4">
            @if ($provider === \App\Enums\Settings\TwoFactorProvider::EMAIL->value)
                <!-- Email Verification -->
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                        {{ __('auth.two_factor.wizard.email.title') }}
                    </h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('auth.two_factor.wizard.email.description') }}
                    </p>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="ml-3 text-sm text-blue-700 dark:text-blue-300">
                            {{ __('auth.two_factor.wizard.email.helper') }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="sendEmailCode"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md transition-colors"
                >
                    {{ __('auth.two_factor.wizard.email.send_code') }}
                </button>

                <div class="space-y-2">
                    <label for="email-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('auth.two_factor.wizard.email.input_label') }}
                    </label>
                    <input
                        type="text"
                        id="email-code"
                        wire:model="verificationCode"
                        placeholder="{{ __('auth.two_factor.wizard.email.placeholder') }}"
                        maxlength="6"
                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-zinc-700 dark:text-white"
                    />
                    @error('verificationCode')
                        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

            @elseif ($provider === \App\Enums\Settings\TwoFactorProvider::AUTHENTICATOR->value)
                <!-- Authenticator App Setup -->
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                        {{ __('auth.two_factor.wizard.authenticator.title') }}
                    </h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('auth.two_factor.wizard.authenticator.description') }}
                    </p>
                </div>

                <div class="flex flex-col items-center space-y-4">
                    <div class="bg-white p-4 rounded-lg border-2 border-zinc-200 dark:border-zinc-700">
                        {!! $qrCodeUrl !!}
                    </div>

                    <div class="text-center">
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mb-2">{{ __('auth.two_factor.wizard.authenticator.manual') }}</p>
                        <code class="bg-zinc-100 dark:bg-zinc-700 px-3 py-1 rounded text-sm font-mono text-zinc-900 dark:text-white">
                            {{ $secret }}
                        </code>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="auth-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('auth.two_factor.wizard.authenticator.input_label') }}
                    </label>
                    <input
                        type="text"
                        id="auth-code"
                        wire:model="verificationCode"
                        placeholder="{{ __('auth.two_factor.verify.placeholder') }}"
                        maxlength="6"
                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-zinc-700 dark:text-white"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    />
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('auth.two_factor.wizard.authenticator.helper') }}
                    </p>
                    @error('verificationCode')
                        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex justify-between pt-4">
                <button
                    type="button"
                    wire:click="previousStep"
                    class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-600 transition-colors"
                >
                    {{ __('auth.two_factor.wizard.actions.back') }}
                </button>
                <button
                    type="button"
                    wire:click="verifyCode"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md transition-colors"
                >
                    {{ $provider === \App\Enums\Settings\TwoFactorProvider::AUTHENTICATOR->value ? __('auth.two_factor.wizard.actions.verify_continue') : ($isEmailCodeVerified ? __('auth.two_factor.wizard.actions.continue') : __('auth.two_factor.wizard.actions.verify_continue')) }}
                </button>
            </div>
        </div>
    @endif

    <!-- Step 3: Recovery Codes -->
    @if ($currentStep === 3)
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                    {{ __('auth.two_factor.wizard.recovery.title') }}
                </h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('auth.two_factor.wizard.recovery.description') }}
                </p>
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-300">{{ __('auth.two_factor.wizard.recovery.notice_title') }}</p>
                        <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                            {{ __('auth.two_factor.wizard.recovery.notice_description') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                    @foreach ($recoveryCodes as $code)
                        <div class="bg-white dark:bg-zinc-800 px-3 py-2 rounded border border-zinc-200 dark:border-zinc-700 text-center text-zinc-900 dark:text-white">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
            </div>

            <button
                type="button"
                wire:click="downloadRecoveryCodes"
                class="w-full px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-600 transition-colors flex items-center justify-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('auth.two_factor.wizard.recovery.download') }}
            </button>

            <div class="flex justify-between pt-4">
                <button
                    type="button"
                    wire:click="previousStep"
                    class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-600 transition-colors"
                >
                    {{ __('auth.two_factor.wizard.actions.back') }}
                </button>
                <button
                    type="button"
                    wire:click="nextStep"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md transition-colors"
                >
                    {{ __('auth.two_factor.wizard.recovery.confirm') }}
                </button>
            </div>
        </div>
    @endif

    <!-- Step 4: Confirmation -->
    @if ($currentStep === 4)
        <div class="space-y-4">
            <div class="text-center py-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                    {{ __('auth.two_factor.wizard.confirmation.title') }}
                </h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('auth.two_factor.wizard.confirmation.description') }}
                </p>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 dark:text-green-300">
                            {{ __('auth.two_factor.wizard.confirmation.protected_message', ['provider' => $provider === \App\Enums\Settings\TwoFactorProvider::EMAIL->value ? __('auth.two_factor.wizard.provider.options.email.title') : __('auth.two_factor.wizard.provider.options.authenticator.title')]) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">{{ __('auth.two_factor.wizard.confirmation.next_steps_title') }}</p>
                        <ul class="text-sm text-blue-700 dark:text-blue-400 mt-1 list-disc list-inside space-y-1">
                            <li>{{ __('auth.two_factor.wizard.confirmation.next_steps.store_codes') }}</li>
                            <li>{{ __('auth.two_factor.wizard.confirmation.next_steps.next_login') }}</li>
                            <li>{{ __('auth.two_factor.wizard.confirmation.next_steps.settings') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <button
                type="button"
                wire:click="confirmSetup"
                class="w-full px-4 py-2 text-sm font-medium text-white bg-primary-900 hover:bg-primary-800 dark:bg-primary-200 dark:text-primary-900 dark:hover:bg-primary-300 rounded-md transition-colors"
            >
                {{ __('auth.two_factor.wizard.confirmation.finish') }}
            </button>
        </div>
    @endif
</div>
