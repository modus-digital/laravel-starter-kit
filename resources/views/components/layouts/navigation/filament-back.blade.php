<nav class="fi-sidebar-nav flex-grow flex flex-col justify-end mb-2 px-6 pt-8" style="scrollbar-gutter: stable">
    <ul class="fi-sidebar-nav-groups -mx-2 flex flex-col gap-y-7">
        <li class="fi-sidebar-item">
            <a href="/dashboard" x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()" class="fi-sidebar-item-button relative flex items-center justify-center gap-x-3 rounded-lg px-2 py-2 outline-none transition duration-75 hover:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/5 dark:focus-visible:bg-white/5">
                <x-heroicon-o-arrow-left class="fi-sidebar-item-icon h-6 w-6 text-gray-400 dark:text-gray-500" />
                <span class="fi-sidebar-item-label flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ __("admin.navigation.back_to_app_button") }}
                </span>
            </a>
        </li>
    </ul>
</nav>
