<?php
    use function Laravel\Folio\{name, middleware};
    use Livewire\Volt\Component;
    use Livewire\Attributes\Validate;
    use Illuminate\Support\Facades\Auth;

    // Named route + middleware
    name('login');
    middleware('feature:auth.login');

    // Define the component using an anonymous class
    new class extends Component {
        #[Validate('required|email')]
        public string $email = '';

        #[Validate('required')]
        public string $password = '';

        public bool $remember = false;

        public function authenticate() {
            $this->validate();

            if (Auth::attempt($this->only('email', 'password'), $this->remember)) {
                // Updated redirect using Livewire's redirector for SPA navigation
                return $this->redirectRoute('application.dashboard', navigate: true);
            }

            $this->addError('email', __('auth.login.invalid_credentials'));
        }
    };

?>

<x-layouts.guest>
    @volt('login')
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <x-application-logo class="w-24 h-24 mr-3 text-gray-900 dark:text-gray-50" />
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    {{ __('auth.login.title') }}
                </h1>

                <form class="space-y-4 md:space-y-6" wire:submit="authenticate">
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('auth.login.email') }}</label>
                        <input wire:model="email" type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('ui.form.placeholders.email') }}">
                        @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('auth.login.password') }}</label>
                        <input wire:model="password" type="password" name="password" id="password" placeholder="{{ __('ui.form.placeholders.password') }}" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input wire:model="remember" id="remember" aria-describedby="remember" type="checkbox" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600 dark:ring-offset-gray-800">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="remember" class="text-gray-500 dark:text-gray-300">{{ __('auth.login.remember') }}</label>
                            </div>
                        </div>
                        @if (feature('app.features.auth.password_reset')->enabled())
                            @if($this->email)
                            <a href="{{ route('password.request', ['email' => $this->email]) }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-500">{{ __('auth.login.forgot_password') }}</a>
                            @else
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-500">{{ __('auth.login.forgot_password') }}</a>
                            @endif
                        @endif
                    </div>
                    <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">{{ __('auth.login.sign_in') }}</button>
                    @if (feature('app.features.auth.register')->enabled())
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            {{ __('auth.login.no_account') }} <a href="{{  route('register') }}" wire:navigate class="font-medium text-primary-600 hover:underline dark:text-primary-500">{{ __('auth.login.sign_up') }}</a>
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.guest>
