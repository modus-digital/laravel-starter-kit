@php
    $user = filament()->auth()->user();
@endphp

<div class="flex items-center flex-col gap-8">
    @if (session()->has('impersonation'))
        <x-filament-widgets::widget class="fi-account-widget w-full">
            <x-filament::section>
                <x-filament-panels::avatar.user size="lg" :user="$user" loading="lazy" />

                <div class="fi-account-widget-main">
                    <h2 class="fi-account-widget-heading">
                        {{ __('navigation.labels.currently_viewing_as') }}
                    </h2>

                    <p class="fi-account-widget-user-name">
                        {{ filament()->getUserName($user) }}
                    </p>
                </div>

                <form action="{{ route('impersonate.leave') }}" method="post" class="fi-account-widget-logout-form">
                    @csrf

                    <x-filament::button color="gray" :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle"
                        :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON" labeled-from="sm"
                        tag="button" type="submit">
                        {{ __('navigation.labels.leave_impersonation') }}
                    </x-filament::button>
                </form>
            </x-filament::section>
        </x-filament-widgets::widget>
    @else
        <div class="w-full mb-8">
            @livewire(Filament\Widgets\AccountWidget::class)
        </div>
    @endif
</div>