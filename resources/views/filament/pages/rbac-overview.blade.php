<x-filament::page>
    <div class="mb-6">
        @livewire(App\Filament\Widgets\RbacStats::class)
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 min-h-[800px]">
            @livewire(App\Filament\Widgets\UsersByRole::class)
        </div>
        <div class="lg:col-span-2 flex flex-col gap-6">
            @livewire(App\Filament\Widgets\RolePermissionMatrix::class)
            @livewire(App\Filament\Widgets\RecentRoleAssignments::class)
        </div>
    </div>
</x-filament::page>