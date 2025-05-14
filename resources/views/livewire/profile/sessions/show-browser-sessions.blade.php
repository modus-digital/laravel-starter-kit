<div class="space-y-3" x-on:cleared-browser-sessions.window="$wire.$refresh()">
    @foreach($sessions as $session)
    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">

        @if($session['device']['mobile'])
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
            </svg>

        @else
            <svg class="h-8 w-8 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
            </svg>
        @endif

        <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $session['device']['platform'] }} - {{ $session['device']['browser'] }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400">
                {{ $session['ip_address'] }} -
                <span
                    @class([
                        'text-green-500' => $session['is_current_device'],
                        'font-semibold'
                    ])
                >
                    {{ $session['is_current_device']
                        ? __('user.sessions.this_device')
                        : __('user.sessions.last_active') . ' ' . $session['last_active']
                    }}
                </span>
            </p>
        </div>
    </div>
    @endforeach
</div>
