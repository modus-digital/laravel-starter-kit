<?php

    use Livewire\Volt\Component;
    use Livewire\Attributes\Layout;
    use Illuminate\Support\Facades\Password;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Validation\ValidationException;

    new #[Layout('components.layouts.guest')] class extends Component
    {
        public string $email = '';

        public function sendResetLink()
        {
            $throttleKey = 'forgot-password:'.request()->ip();

            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                throw ValidationException::withMessages([
                    'email' => __('auth.throttle', ['seconds' => $seconds]),
                ]);
            }

            $this->validate([
                'email' => 'required|string|email',
            ]);

            $status = Password::sendResetLink($this->only('email'));

            RateLimiter::hit($throttleKey, 60);

            if ($status === Password::RESET_LINK_SENT) {
                session()->flash('success', __($status));
                return;
            }

            $this->addError('email', __($status));
        }
    };
?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
    <x-slot name="title">{{ __('auth.forgot_password.page_title') }}</x-slot>
    <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">
        <x-application-logo class="w-24 h-24 mr-3 text-zinc-900 dark:text-zinc-50" />
    </a>
    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-zinc-900 md:text-2xl dark:text-white">
                {{ __('auth.forgot_password.title') }}
            </h1>

            @if (session()->has('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-zinc-900 dark:text-green-400" role="alert">
                <span class="font-medium">{{ session()->get('success') }}</span>
            </div>
            @endif

            @renderHook(App\Enums\Hooks::AUTH_FORGOT_PASSWORD_FORM_BEFORE)

            <form class="space-y-4 md:space-y-6" wire:submit="sendResetLink">
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">
                        {{ __('auth.forgot_password.email') }}
                    </label>
                    <input wire:model="email" type="email" name="email" id="email" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('common.placeholders.email') }}">
                    @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                    {{ __('auth.forgot_password.send_reset_link') }}
                </button>

                <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">
                    {{ __('auth.forgot_password.already_have_account') }} <a href="{{  route('login') }}" wire:navigate class="font-medium text-primary-600 hover:underline dark:text-primary-500">
                        {{ __('auth.forgot_password.sign_in') }}
                    </a>
                </p>
            </form>

            @renderHook(App\Enums\Hooks::AUTH_FORGOT_PASSWORD_FORM_AFTER)
        </div>
    </div>
</div>
