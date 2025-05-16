<?php

use function Laravel\Folio\name;

name('user.profile.settings');

?>

<x-layouts.app title="{{ __('settings.header') }}">
    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Display settings --}}
        <livewire:profile.edit.display />

        {{-- Localization settings --}}
        <livewire:profile.edit.localization />
    </div>

</x-layouts.app>

