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

    <x-modal name="setup-two-factor" size="sm">
        <x-slot name="title">{{ __('notifications.modals.two-factor.enable.title') }}</x-slot>
        <x-slot name="description">{{ __('notifications.modals.two-factor.enable.description') }}</x-slot>

        <livewire:profile.two-factor.enable-two-factor />
    </x-modal>

    <x-modal name="disable-two-factor" size="sm">
        <x-slot name="title">{{ __('notifications.modals.two-factor.disable.title') }}</x-slot>
        <x-slot name="description">{{ __('notifications.modals.two-factor.disable.description') }}</x-slot>

        <livewire:profile.two-factor.disable-two-factor />
    </x-modal>

    <x-modal name="regenerate-backup-codes" size="sm">
        <x-slot name="title">{{ __('notifications.modals.two-factor.backup-codes.title') }}</x-slot>
        <x-slot name="description">{{ __('notifications.modals.two-factor.backup-codes.description') }}</x-slot>

        <livewire:profile.two-factor.regenerate-backup-codes />
    </x-modal>
</x-layouts.app>

