<x-layouts.base>
  
  <!-- Top nav -->
  <x-layouts.app.topnav />

  <!-- Sidebar -->
  <x-layouts.app.sidebar />

  <!-- Page content -->
  <div class="p-6 sm:ml-64 mt-14">
    {{ $slot }}
  </div>

</x-layouts.base>