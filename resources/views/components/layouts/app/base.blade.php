<x-layouts.base>
  
  <!-- Top nav -->
  <x-layouts.app.topnav />

  <!-- Sidebar -->
  <x-layouts.app.sidebar />

  <!-- Page content -->
  <div class="p-4 sm:ml-64 mt-16 min-h-[calc(100vh-4rem)] flex flex-col">
    <div class="p-6 rounded-lg bg-white border border-neutral-200 flex-1">
      {{ $slot }}
    </div>
  </div>

</x-layouts.base>