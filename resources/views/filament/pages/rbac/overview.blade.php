<x-filament-panels::page>
    {{-- Stats Widget - Full Width --}}
    <div class="mb-6">
        @livewire(App\Livewire\Widgets\RbacStats::class)
    </div>

    {{-- Three Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Chart --}}
        <div class="lg:col-span-1 min-h-[800px]">
            @livewire(App\Livewire\Widgets\UsersByRole::class)
        </div>

        {{-- Right Two Columns - Stacked Tables --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            @livewire(App\Livewire\Widgets\RolePermissionMatrix::class)
            @livewire(App\Livewire\Widgets\RecentRoleAssignments::class)
        </div>
    </div>
</x-filament-panels::page>
