<div>
    <div class="flex flex-col items-center justify-center">
        <div class="bg-gray-100 p-4 rounded-lg">
            <div class="grid grid-cols-2 gap-8">
                @foreach(array_chunk($backupCodes, 5) as $column)
                    <div class="flex flex-col">
                        @foreach($column as $code)
                            <span class="text-sm font-mono font-semibold pb-2">{{ $code }}</span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <button
            wire:click="downloadBackupCodes"
            class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 active:bg-primary-600 transition"
        >
            {{ __('pages.user.profile.actions.download_backup_codes') }}
        </button>
    </div>
</div>
