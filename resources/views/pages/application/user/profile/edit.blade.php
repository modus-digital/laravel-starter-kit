<?php

use function Laravel\Folio\name;

name('user.profile.edit');

?>

<x-layouts.app title="profile">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Personal Information -->
        <livewire:profile.update-personal-information />

        <!-- Password -->
        <livewire:profile.update-password />

        <!-- Two-Factor Authentication -->
        <livewire:profile.two-factor-status />

        <!-- Delete Account -->
        <livewire:profile.delete-account />
    </div>
</x-layouts.app>

