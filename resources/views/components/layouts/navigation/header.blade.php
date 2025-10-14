@props(["title" => ""])

<nav
    class="fixed top-0 right-0 left-0 z-50 border-b border-zinc-200 bg-white px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"
>
    <div class="flex flex-wrap items-center justify-between">
        <div class="flex items-center justify-start">
            <button
                data-drawer-target="drawer-navigation"
                data-drawer-toggle="drawer-navigation"
                aria-controls="drawer-navigation"
                class="mr-2 cursor-pointer rounded-lg p-2 text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus:bg-zinc-100 focus:ring-2 focus:ring-zinc-100 md:hidden dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-white dark:focus:bg-zinc-700 dark:focus:ring-zinc-700"
            >
                <svg aria-hidden="true" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>

                <svg aria-hidden="true" class="hidden h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">{{ __("navigation.header.toggle_sidebar") }}</span>
            </button>

            <a href="/" class="mr-4 flex items-center justify-between">
                <x-application-logo class="mr-3 h-8 w-8 text-zinc-900 dark:text-zinc-50" />

                @if ($title)
                    <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">{{ $title }}</span>
                @endif
            </a>
        </div>

        <div class="flex items-center lg:order-2">
            @if (session()->has("impersonating_user_id"))
                <form action="{{ route("impersonate.leave") }}" method="POST">
                    @csrf

                    <button
                        type="submit"
                        class="cursor-pointer rounded-lg px-4 py-2 text-sm text-zinc-700 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 focus:ring-4 focus:ring-zinc-300 md:mr-0 dark:text-zinc-300 dark:hover:bg-zinc-700 dark:hover:text-white dark:focus:ring-zinc-600"
                    >
                        <div class="flex items-center">
                            <x-heroicon-o-arrow-left-on-rectangle class="mr-2 h-4 w-4" />
                            <span>{{ __("navigation.header.leave_impersonation") }}</span>
                        </div>
                    </button>
                </form>
            @endif

            <button
                type="button"
                class="mx-3 flex rounded-full bg-zinc-800 text-sm focus:ring-4 focus:ring-zinc-300 md:mr-0 dark:focus:ring-zinc-600"
                id="user-menu-button"
                aria-expanded="false"
                data-dropdown-toggle="user-dropdown"
            >
                <span class="sr-only">{{ __("navigation.header.open_user_menu") }}</span>
                <livewire:avatar size="h-8 w-8" />
            </button>

            <!-- User dropdown -->
            <div class="z-50 my-4 hidden w-56 list-none divide-y divide-zinc-100 rounded bg-white text-base shadow dark:divide-zinc-600 dark:bg-zinc-700" id="user-dropdown">
                <div class="px-4 py-3">
                    <span class="block text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ auth()->user() !== null ? auth()->user()->name : __("navigation.header.guest") }}
                    </span>
                    <span class="block truncate text-sm text-zinc-900 dark:text-white">
                        {{ auth()->user() !== null ? auth()->user()->email : __("navigation.header.guest_email") }}
                    </span>
                </div>
                <ul class="py-1 text-zinc-700 dark:text-zinc-300" aria-labelledby="dropdown" >
                    <li>
                        <a href="{{ route("app.user.profile") }}" class="block px-4 py-2 text-sm hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-600 dark:hover:text-white">
                            {{ __("navigation.header.my_profile") }}
                        </a>
                    </li>
                </ul>
                <!-- End of User dropdown -->

                <!-- Logout button -->
                <ul class="py-1 text-zinc-700 dark:text-zinc-300" aria-labelledby="dropdown">
                    <li>
                        <form action="{{ route("auth.logout") }}" method="POST" class="w-full">
                            @csrf

                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-600 dark:hover:text-white">
                                {{ __("navigation.header.sign_out") }}
                            </button>
                        </form>
                    </li>
                </ul>
                <!-- End of Logout button -->
            </div>
        </div>
    </div>
</nav>
