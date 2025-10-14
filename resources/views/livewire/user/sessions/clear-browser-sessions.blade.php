<form wire:submit="clearBrowserSessions">
    <div class="space-y-4">
        <div>
            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('user.sessions.password') }}
            </label>
            <input type="password" id="password" wire:model="password" class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500" />
            @error('password')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 transition">
                {{ __('user.sessions.logout_other_sessions') }}
            </button>
        </div>
    </div>
</form>
