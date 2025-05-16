@props([
    'title' => '',
])

<nav class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50">
    <div class="flex flex-wrap justify-between items-center">
      <div class="flex justify-start items-center">
        <button
          data-drawer-target="drawer-navigation"
          data-drawer-toggle="drawer-navigation"
          aria-controls="drawer-navigation"
          class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        >
          <svg
            aria-hidden="true"
            class="w-6 h-6"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
              clip-rule="evenodd"
            ></path>
          </svg>
          <svg
            aria-hidden="true"
            class="hidden w-6 h-6"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"
            ></path>
          </svg>
          <span class="sr-only">Toggle sidebar</span>
        </button>
        <a href="/" class="flex items-center justify-between mr-4">
          <x-application-logo class="w-8 h-8 mr-3 text-gray-900 dark:text-gray-50" />
          <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">{{ $title }}</span>
        </a>
      </div>
      <div class="flex items-center lg:order-2">
        <button
          type="button"
          class="flex mx-3 text-sm bg-gray-800 rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          id="user-menu-button"
          aria-expanded="false"
          data-dropdown-toggle="dropdown"
        >
          <span class="sr-only">{{ __('ui.navigation.header.open_user_menu') }}</span>
          <img
            class="w-8 h-8 rounded-full"
            src="{{ auth()->user() !== null ? (auth()->user()->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode(auth()->user()->initials())) : "https://ui-avatars.com/api/?name=" . urlencode(__('ui.navigation.header.guest')) }}"
            alt="user photo"
          />
        </button>

        <!-- User dropdown -->
        <div
          class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600"
          id="dropdown"
        >
          <div class="py-3 px-4">
            <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->user() !== null ? auth()->user()->name : __('ui.navigation.header.guest') }}</span>
            <span class="block text-sm text-gray-900 truncate dark:text-white">{{ auth()->user() !== null ? auth()->user()->email : __('ui.navigation.header.guest_email') }}</span>
          </div>
          <ul
            class="py-1 text-gray-700 dark:text-gray-300"
            aria-labelledby="dropdown"
          >
            <li>
              <a
                href="{{ route('user.profile') }}"
                class="block py-2 px-4 text-sm hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-400 dark:hover:text-white"
                >{{ __('ui.navigation.header.my_profile') }}</a
              >
            </li>
          </ul>
          <!-- End of User dropdown -->

          <!-- Logout button -->
          <ul class="py-1 text-gray-700 dark:text-gray-300" aria-labelledby="dropdown">
            <li>
                <form action="{{ route('auth.logout') }}" method="POST" class="w-full">
                    @csrf

                    <button type="submit" class="block w-full py-2 px-4 text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        {{ __('ui.navigation.header.sign_out') }}
                    </button>
                </form>
            </li>
          </ul>
          <!-- End of Logout button -->
        </div>
      </div>
    </div>
  </nav>
