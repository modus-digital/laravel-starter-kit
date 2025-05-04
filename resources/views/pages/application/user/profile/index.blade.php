<?php

use function Laravel\Folio\name;

name('user.profile');

?>

<x-layouts.app title="profile">
    <div class="container mx-auto p-4">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <!-- Header Row -->
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h2>
                 <a href="{{ route('user.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 transition">
                    Edit Profile
                </a>
            </div>

            {{-- Main content row --}}
            <div class="flex flex-col sm:flex-row items-center w-full space-y-4 sm:space-y-0 mb-6 sm:mb-0 sm:gap-x-4">
                {{-- Block 1: Avatar and Name/Role (Equal Width on sm+) --}}
                <div class="sm:flex-1 flex items-center justify-start space-x-4 w-full sm:w-auto">
                    {{-- Avatar --}}
                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                        <img
                            src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=random' }}"
                            alt="{{ auth()->user()->name }}'s profile picture"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    {{-- Name & Role --}}
                    <div class="flex-shrink-0 text-left">
                        <span class="mb-1 inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-700/10 dark:ring-blue-300/20">
                            Administrator {{-- Static Role --}}
                        </span>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h1>
                    </div>
                </div>

                 {{-- Spacer removed --}}

                {{-- Block 2: Email (Equal Width on sm+) --}}
                <div class="sm:flex-1 flex justify-start items-center sm:justify-center w-full sm:w-auto">
                    {{-- Mobile View (Icon + Text) --}}
                    <div class="flex sm:hidden items-center space-x-2">
                         <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91A2.25 2.25 0 0 1 2.25 6.993v-.243" /></svg>
                         <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->email }}</span>
                    </div>
                    {{-- Desktop View (Icon Box + Label/Text) --}}
                    <div class="hidden sm:flex sm:items-center sm:space-x-4">
                        <span class="inline-flex items-center justify-center h-20 w-20 rounded-md bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                            <svg class="h-12 w-12 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91A2.25 2.25 0 0 1 2.25 6.993v-.243" /></svg>
                        </span>
                        <div class="flex flex-col items-start">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email</span>
                            <span class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>

                {{-- Block 3: Phone (Equal Width on sm+) --}}
                <div class="sm:flex-1 flex justify-start items-center sm:justify-center w-full sm:w-auto">
                    {{-- Mobile View (Icon + Text) --}}
                    <div class="flex sm:hidden items-center space-x-2">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">+1 234 567 890</span>
                    </div>
                    {{-- Desktop View (Icon Box + Label/Text) --}}
                    <div class="hidden sm:flex sm:items-center sm:space-x-4">
                        <span class="inline-flex items-center justify-center h-20 w-20 rounded-md bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                            <svg class="h-12 w-12 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        </span>
                         <div class="flex flex-col items-start">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone</span>
                            <span class="text-lg font-medium text-gray-700 dark:text-gray-300">+1 234 567 890</span> {{-- Static phone number --}}
                        </div>
                    </div>
                </div>

                {{-- Block 4: Joined Date (Equal Width on sm+) --}}
                <div class="sm:flex-1 flex justify-start items-center sm:justify-center w-full sm:w-auto">
                    {{-- Mobile View (Icon + Text) --}}
                    <div class="flex sm:hidden items-center space-x-2">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->created_at->format('M Y') }}</span>
                    </div>
                     {{-- Desktop View (Icon Box + Label/Text + Badge) --}}
                    <div class="hidden sm:flex sm:items-center sm:space-x-4">
                        <span class="inline-flex items-center justify-center h-20 w-20 rounded-md bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                            <svg class="h-12 w-12 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </span>
                        <div class="flex flex-col items-start">
                             <div class="flex items-center mb-1">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Joined</span>
                                @if(auth()->user()->created_at->diffInDays(now()) <= 7)
                                    <span class="ml-1 inline-flex items-center rounded-md bg-green-50 dark:bg-green-900 px-1.5 py-0.5 text-xs font-medium text-green-700 dark:text-green-300 ring-1 ring-inset ring-green-600/20 dark:ring-green-400/30">
                                        New
                                    </span>
                                @endif
                            </div>
                            <span class="text-lg font-medium text-gray-700 dark:text-gray-300">
                                {{ auth()->user()->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Block 5: Edit Button Removed from here --}}

            </div>

            {{-- Profile Information Section Removed --}}
        </div>
    </div>

    <div class="mt-1 pt-6 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Settings Card (Styled like screenshot) -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <!-- Header for Settings Card -->
                <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Settings</h2>
                    <a href="{{ route('user.profile.settings') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 transition">
                        Edit Settings
                    </a>
                </div>

                <!-- Container for Fieldsets -->
                <div> <!-- Removed space-y-4 -->

                    <!-- Group 1: Locale -->
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-md p-4 mb-6">
                        <legend class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400 px-1">Locale</legend>
                        <div> <!-- Removed space-y-0 -->
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Selected language</span>
                                <span class="text-sm text-gray-900 dark:text-white font-medium">English</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Date format</span>
                                <span class="text-sm text-gray-900 dark:text-white font-medium">31-01-2025 23:59</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Timezone</span>
                                <span class="text-sm text-gray-900 dark:text-white font-medium">Europe/Amsterdam</span>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Group 2: Security -->
                     <fieldset class="border border-gray-200 dark:border-gray-700 rounded-md p-4 mb-6">
                         <legend class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400 px-1">Security</legend>
                          <div> <!-- Removed space-y-0 -->
                             <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Two-factor status</span>
                                <span class="flex items-center">
                                    <span class="inline-block h-2 w-2 rounded-full bg-gray-400 dark:bg-gray-500 mr-2"></span> <!-- Indicator Dot -->
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">Disabled</span>
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Password last changed</span>
                                <span class="text-sm text-gray-900 dark:text-white font-medium">28-04-2025 16:15</span>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Group 3: Display -->
                     <fieldset class="border border-gray-200 dark:border-gray-700 rounded-md p-4"> <!-- Removed mb-6 -->
                        <legend class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400 px-1">Display</legend>
                         <div> <!-- Removed space-y-0 -->
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Appearance</span>
                                <span class="text-sm text-gray-900 dark:text-white font-medium">System</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Theme</span>
                                <span class="text-sm text-gray-900 dark:text-white font-medium">Indigo</span>
                            </div>
                        </div>
                    </fieldset>

                </div>
            </div>

            <!-- Browser Sessions Card (Styled like screenshot) -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                 <!-- Header for Browser Sessions Card -->
                 <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Browser Sessions</h2>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 transition">
                        Log out other browser sessions
                    </button>
                </div>

                <div class="space-y-3"> <!-- List container - flex-grow removed -->

                    <!-- Session Item 1 (Current Device) -->
                    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                         {{-- Icon: heroicons/outline/computer-desktop --}}
                        <svg class="h-8 w-8 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Windows - Chrome</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">127.0.0.1 - This device</p>
                        </div>
                    </div>

                    <!-- Session Item 2 -->
                    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        {{-- Icon: heroicons/outline/computer-desktop --}}
                        <svg class="h-8 w-8 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                        </svg>
                         <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Windows - Chrome</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">127.0.0.1 - Last active 3 days ago</p> {{-- Static Example --}}
                        </div>
                    </div>

                     <!-- Session Item 3 -->
                    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        {{-- Icon: heroicons/outline/computer-desktop --}}
                         <svg class="h-8 w-8 text-gray-500 dark:text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                        </svg>
                         <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">macOS - Safari</p> {{-- Static Example --}}
                            <p class="text-xs text-gray-600 dark:text-gray-400">192.168.1.10 - Last active yesterday</p> {{-- Static Example --}}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-layouts.app>


