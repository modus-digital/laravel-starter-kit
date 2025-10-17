<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

new #[Layout('components.layouts.guest')] class extends Component
{
    #[Validate('required|string')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $throttleKey = 'register:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $this->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        RateLimiter::hit($throttleKey, 60);

        $user = User::create(attributes: $this->only('name', 'email', 'password'));

        event(new Registered($user));

        Auth::login($user);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        RateLimiter::clear($throttleKey);

        return redirect()->route('app.dashboard');
    }
};
?>
<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
    <x-slot name="title">{{ __('auth.register.page_title') }}</x-slot>
    <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">
        <x-application-logo class="w-24 h-24 mr-3 text-zinc-900 dark:text-zinc-50" />
    </a>
    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-zinc-900 md:text-2xl dark:text-white">
                {{ __('auth.register.title') }}
            </h1>

            @renderHook(App\Enums\Hooks::AUTH_REGISTER_FORM_BEFORE)

            <form class="space-y-4 md:space-y-6" wire:submit="register">
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">{{ __('auth.register.name') }}</label>
                    <input wire:model="name" type="text" name="name" id="name" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('common.placeholders.name') }}">
                    @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">{{ __('auth.register.email') }}</label>
                    <input wire:model="email" type="email" name="email" id="email" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('common.placeholders.email') }}">
                    @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">{{ __('auth.register.password') }}</label>
                    <input wire:model="password" type="password" name="password" id="password" placeholder="{{ __('common.placeholders.password') }}" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white">{{ __('auth.register.password_confirmation') }}</label>
                    <input wire:model="password_confirmation" type="password" name="password_confirmation" id="password_confirmation" placeholder="{{ __('common.placeholders.password') }}" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('password_confirmation') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                    {{ __('auth.register.sign_up') }}
                </button>

                <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">
                    {{ __('auth.register.already_have_account') }} <a href="{{ route('login') }}" wire:navigate class="font-medium text-primary-600 hover:underline dark:text-primary-500">{{ __('auth.register.sign_in') }}</a>
                </p>
            </form>

            @renderHook(App\Enums\Hooks::AUTH_REGISTER_FORM_AFTER)
        </div>
    </div>
</div>
