<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

new #[Layout('components.layouts.guest')] class extends Component
{
    public function resend()
    {
        $throttleKey = 'email-verification:'.Auth::id();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('app.dashboard');
        }

        Auth::user()->sendEmailVerificationNotification();

        RateLimiter::hit($throttleKey, 60);

        session()->flash('success', __('auth.verification.link_sent'));
    }
};
?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
    <x-slot name="title">{{ __('auth.verification.page_title') }}</x-slot>

    <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">
        <x-application-logo class="w-24 h-24 mr-3 text-zinc-900 dark:text-zinc-50" />
    </a>

    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <div class="space-y-2">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-zinc-900 md:text-2xl dark:text-white">
                    {{ __('auth.verification.title') }}
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ __('auth.verification.message') }}
                </p>
            </div>

            @if (session()->has('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-zinc-900 dark:text-green-400" role="alert">
                <span class="font-medium">{{ session()->get('success') }}</span>
            </div>
            @endif

            @error('email')
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-zinc-900 dark:text-red-400" role="alert">
                    <span class="font-medium">{{ $message }}</span>
                </div>
            @enderror

            @renderHook(App\Enums\Hooks::AUTH_VERIFY_EMAIL_FORM_BEFORE)

            <button wire:click="resend" type="button" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                {{ __('auth.verification.resend') }}
            </button>

            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="w-full text-zinc-900 bg-zinc-200 hover:bg-zinc-300 focus:ring-4 focus:outline-none focus:ring-zinc-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:focus:ring-zinc-800">
                    {{ __('auth.logout') }}
                </button>
            </form>

            @renderHook(App\Enums\Hooks::AUTH_VERIFY_EMAIL_FORM_AFTER)
        </div>
    </div>
</div>

