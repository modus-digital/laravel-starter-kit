<?php
    use Illuminate\Support\Facades\Password;

    use function Laravel\Folio\{name, middleware};
    use Livewire\Volt\Component;
    use Livewire\Attributes\Validate;

    name('password.confirm');
    middleware('feature:auth.password_reset');

    new class extends Component {
        #[Validate('required', 'current_password')]
        public string $password;

        public function confirmPassword() {
            $this->validate();

            session()->put('auth.password_confirmed_at', time());

            return redirect()->intended('/');
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
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">Confirm Password</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Please confirm your password before continuing.</p>

                <form class="space-y-4 md:space-y-6" wire:submit="confirm">
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                        <input wire:model="password" type="password" name="password" id="password" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="••••••••">
                        @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Confirm Password</button>
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.guest>
