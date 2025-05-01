<?php

use function Laravel\Folio\{name, middleware};
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Livewire\Volt\Component;
use Livewire\Attributes\On;

name('verification.notice');
middleware(['feature:auth.email_verification', 'auth', 'throttle:6,1']);

new class extends Component {

    #[On('resent')]
    public function showResentMessage()
    {
        session()->flash('resent', true);
    }

    public function resendVerificationEmail() {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return $this->redirectIntended(default: route('application.dashboard'), navigate: true);
        }

        $user->sendEmailVerificationNotification();

        $this->dispatch('resent');
    }
};

?>

<x-layouts.guest>
    @volt
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <x-application-logo class="w-24 h-24 mr-3 text-gray-900 dark:text-gray-50" />
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">Verify your email</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Please confirm your email before continuing.</p>

                @if (session('resent'))
                    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-900 dark:text-green-400" role="alert">
                        <span class="font-medium">A new verification link has been sent to your email address.</span>
                    </div>
                @endif

                <div class="text-sm leading-6 text-gray-700 dark:text-gray-400">
                    <p>
                        Before proceeding, please check your email for a verification link. If you did not receive the email. <br> <br>
                        <a
                            wire:click="resendVerificationEmail"
                            class="text-gray-700 underline transition duration-150 ease-in-out cursor-pointer dark:text-gray-300 hover:text-gray-600 focus:outline-none focus:underline"
                        >click here to request another</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.guest>
