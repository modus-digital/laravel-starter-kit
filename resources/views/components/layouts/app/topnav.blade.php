<nav class="fixed top-0 z-50 w-full bg-white border-b border-neutral-200 dark:bg-neutral-800 dark:border-neutral-700">
  <div class="px-3 py-3 lg:px-5 lg:pl-3">
    <div class="flex items-center justify-between">
      
      <!-- Left side button -->
      <div class="flex items-center justify-start rtl:justify-end">
        
        <!-- Open sidebar button -->
        <button 
          data-drawer-target="sidebar" 
          data-drawer-toggle="sidebar" 
          aria-controls="sidebar" 
          type="button" 
          class="inline-flex items-center p-2 text-sm text-neutral-500 rounded-lg sm:hidden hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-neutral-200 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:ring-neutral-600"
        >
          <span class="sr-only">Open sidebar</span>
          <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
          </svg>
        </button>

        <!-- Logo -->
        <a href="https://flowbite.com" class="flex ms-2 md:me-24">
          <img src="https://flowbite.com/docs/images/logo.svg" class="h-8 me-3" alt="FlowBite Logo" />
          <span class="self-center text-xl font-bold sm:text-2xl whitespace-nowrap dark:text-white font-clash-display">
            Modus Digital
            <span class="text-neutral-500 dark:text-neutral-400">- Starter Kit</span>
          </span>
        </a>
        
      </div>
      
      <!-- Right side buttons -->
      <div class="flex items-center">
        <div class="flex items-center ms-3">

          <!-- Theme toggle -->
          <button 
            id="theme-toggle" 
            data-tooltip-target="tooltip-toggle" 
            type="button" 
            class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5"
          >
            <svg id="theme-toggle-dark-icon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
            <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
          </button>
          
          <!-- User info button -->
          <div>
            <button 
              type="button" 
              class="flex text-sm bg-neutral-800 rounded-full focus:ring-4 focus:ring-neutral-300 dark:focus:ring-neutral-600" 
              aria-expanded="false" 
              data-dropdown-toggle="dropdown-user"
            >
              <span class="sr-only">Open user menu</span>
              <img class="w-8 h-8 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="user photo">
            </button>
          </div>

          <!-- User info dropdown menu -->
          <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-neutral-100 rounded-sm shadow-sm dark:bg-neutral-700 dark:divide-neutral-600" id="dropdown-user">
            <div class="px-4 py-3" role="none">
              <p class="text-sm text-neutral-900 dark:text-white" role="none">
                Gebruiker
              </p>
              <p class="text-sm font-medium text-neutral-900 truncate dark:text-neutral-300" role="none">
                gebruiker@example.com
              </p>
            </div>
            <ul class="py-1" role="none">
              <li>
                <a href="#" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-600 dark:hover:text-white" role="menuitem">Dashboard</a>
              </li>
              <li>
                <a href="#" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-600 dark:hover:text-white" role="menuitem">Settings</a>
              </li>
              <li>
                <a href="#" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-600 dark:hover:text-white" role="menuitem">Earnings</a>
              </li>
              <li>
                <a href="#" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-600 dark:hover:text-white" role="menuitem">Sign out</a>
              </li>
            </ul>
          </div>

        </div>
      </div>

    </div>
  </div>
</nav>