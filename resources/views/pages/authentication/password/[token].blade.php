<?php
    use Illuminate\Support\Facades\Password;
    use Illuminate\Auth\Events\PasswordReset;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;

    use App\Models\User;

    use function Laravel\Folio\{name, middleware};
    use Livewire\Volt\Component;
    use Livewire\Attributes\Validate;

    // Named route
    name('password.reset');
    middleware('feature:auth.password_reset');

    new class extends Component {

        #[Validate('required', 'email')]
        public string $email;

        #[Validate('required')]
        public string $status;

        #[Validate('required')]
        public string $token;

        #[Validate('required', 'min:8', 'confirmed')]
        public string $password;
        public string $password_confirmation;

        public function mount(string $token): void {
            $this->email = request()->query('email', '');
            $this->token = $token;
        }

        public function resetPassword() {
            $this->validate();

            $status = Password::reset(
                $this->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            return $status === Password::PasswordReset
                ? redirect()->route('login')->with('status', __($status))
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
                    {{ __('auth.password_reset.token.title') }}
                </h1>

                @if ($this->status)
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-900 dark:text-green-400" role="alert">
                    <span class="font-medium">{{ $this->status }}</span>
                </div>
                @endif

                <form class="space-y-4 md:space-y-6" wire:submit="resetPassword">
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('auth.password_reset.token.email') }}</label>
                        <input wire:model="email" type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('ui.form.placeholders.email') }}">
                        @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('auth.password_reset.token.password') }}</label>
                        <input wire:model="password" type="password" name="password" id="password" placeholder="{{ __('ui.form.placeholders.password') }}" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('auth.password_reset.token.password_confirmation') }}</label>
                        <input wire:model="password_confirmation" type="password" name="password_confirmation" id="password_confirmation" placeholder="{{ __('ui.form.placeholders.password') }}" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        @error('password_confirmation') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        {{ __('auth.password_reset.token.reset_password') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.guest>
