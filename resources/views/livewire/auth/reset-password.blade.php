<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Models\User;

new #[Layout('components.layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token)
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword()
    {
        $throttleKey = 'reset-password:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $this->validate([
            'token' => 'required',
            'email' => 'required|string|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            credentials: $this->only('email', 'password', 'password_confirmation', 'token'),
            callback: function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('email', __($status));

            return;
        }

        RateLimiter::clear($throttleKey);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        session()->flash('success', __('auth.reset_password.password_reset_success'));
        $this->redirect(route('login'), navigate: true);
    }
};

?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
    <x-slot name="title">{{ __('auth.reset_password.page_title') }}</x-slot>
    <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">
        <x-application-logo class="w-24 h-24 mr-3 text-zinc-900 dark:text-zinc-50" />
    </a>
    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-zinc-900 md:text-2xl dark:text-white">
                {{ __('auth.reset_password.title') }}
            </h1>

            <form class="space-y-4 md:space-y-6" wire:submit="resetPassword">
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">{{ __('auth.reset_password.email') }}</label>
                    <input wire:model="email" type="email" name="email" id="email" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('common.placeholders.email') }}">
                    @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">{{ __('auth.reset_password.password') }}</label>
                    <input wire:model="password" type="password" name="password" id="password" placeholder="{{ __('common.placeholders.password') }}" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">{{ __('auth.reset_password.password_confirmation') }}</label>
                    <input wire:model="password_confirmation" type="password" name="password_confirmation" id="password_confirmation" placeholder="{{ __('common.placeholders.password') }}" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('password_confirmation') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                    {{ __('auth.reset_password.reset_password') }}
                </button>
            </form>
        </div>
    </div>
</div>
