@use('App\Enums\RBAC\Permission')

<aside class="fixed top-0 left-0 z-40 w-64 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidenav" id="drawer-navigation" >
    <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
        <ul class="space-y-2">
            <x-layouts.navigation.nav-link href="{{  route('application.dashboard') }}" :active="request()->routeIs('application.dashboard')">
                <x-slot:icon>
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-800 dark:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" >
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                    </svg>
                </x-slot:icon>

                {{ __('ui.navigation.dashboard') }}
            </x-layouts.navigation.nav-link>
        </ul>
    </div>

    @if(auth()->user()->hasPermissionTo(Permission::HAS_ACCESS_TO_ADMIN_PANEL))
    <div class="hidden absolute bottom-0 left-0 justify-start p-4 w-full lg:flex bg-white dark:bg-gray-800 z-20 border-r border-gray-200 dark:border-gray-700">
        <ul class="space-y-2">
            <x-layouts.navigation.nav-link class="w-full" href="{{ route('filament.admin.pages.dashboard') }}">
                <x-slot:icon>
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-800 dark:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" >
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                    </svg>
                </x-slot:icon>

                {{ __('ui.navigation.admin_portal') }}
            </x-layouts.navigation.nav-link>
        </ul>
    </div>
    @endif
</aside>
