<?php
    use Illuminate\Support\Facades\Password;

    use function Laravel\Folio\name;
    use Livewire\Volt\Component;
    use Livewire\Attributes\Validate;

    // Named route
    name('password.request');

    new class extends Component {

        #[Validate('required', 'email')]
        public string $email;

        public string $status;

        public function mount() {
            $this->email = request()->query('email', '');
        }

        public function forgotPassword() {
            $this->validate();

            $status = Password::sendResetLink($this->only('email'));

            return $status === Password::ResetLinkSent
                ? $this->status = __($status)
                : $this->addError('email', __($status));
        }

    }

?>

<x-layouts.guest>
    @volt
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <x-application-logo class="w-24 h-24 mr-3 text-gray-900 dark:text-gray-50" />
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    {{ __('auth.password_reset.request.title') }}
                </h1>

                @if ($this->status)
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-900 dark:text-green-400" role="alert">
                    <span class="font-medium">{{ $this->status }}</span>
                </div>
                @endif

                <form class="space-y-4 md:space-y-6" wire:submit="forgotPassword">
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('auth.password_reset.request.email') }}
                        </label>
                        <input wire:model="email" type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('ui.form.placeholders.email') }}">
                        @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        {{ __('auth.password_reset.request.reset_password') }}
                    </button>

                    @if (feature('features.auth.login')->enabled())
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            {{ __('auth.password_reset.request.already_have_account') }} <a href="{{  route('login') }}" wire:navigate class="font-medium text-primary-600 hover:underline dark:text-primary-500">
                                {{ __('auth.password_reset.request.sign_in') }}
                            </a>
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.guest>
