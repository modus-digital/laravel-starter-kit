<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Activity Log
        </x-slot>

        <x-slot name="description">
            Click on the title to show the details
        </x-slot>

        <x-slot name="afterHeader">
            {{ $this->form }}
        </x-slot>

        {{-- Timeline --}}
        <div class="relative">
            {{-- Vertical line --}}
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-300 dark:bg-gray-700"></div>

            <div class="space-y-6">
                @forelse($this->activities as $activity)
                    <div class="relative flex items-start gap-4" wire:key="activity-{{ $activity->id }}">
                        {{-- Icon circle --}}
                        <div
                            class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 ring-4 ring-white dark:ring-gray-900">
                            @php
                                $event = $activity->event ?? 'default';
                                $icon = match (true) {
                                    str_contains($event, 'created') => 'heroicon-o-plus-circle',
                                    str_contains($event, 'updated') => 'heroicon-o-pencil',
                                    str_contains($event, 'deleted') => 'heroicon-o-trash',
                                    str_contains($event, 'login') => 'heroicon-o-arrow-right-on-rectangle',
                                    str_contains($event, 'logout') => 'heroicon-o-arrow-left-on-rectangle',
                                    str_contains($event, 'branding') => 'heroicon-o-palette',
                                    str_contains($event, 'rbac') => 'heroicon-o-shield-check',
                                    str_contains($event, 'impersonate') => 'icon-impersonation',
                                    default => 'heroicon-o-information-circle',
                                };
                            @endphp
                            <x-filament::icon :icon="$icon" class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <button type="button" wire:click="openActivityModal({{ $activity->id }})"
                                        class="text-left hover:text-primary-600 dark:hover:text-primary-400 transition-colors cursor-pointer group">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white group-hover:underline">
                                            {{ $activity->getTranslatedDescription() }}
                                        </h4>
                                    </button>
                                    <div class="mt-1">
                                        @php
                                            $event = $activity->event ?? 'unknown';
                                        @endphp
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                            event: {{ $event }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $activity->created_at->diffForHumans() }}
                                    <div class="text-[10px] mt-0.5">
                                        {{ $activity->created_at->format('M j, Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No activities found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div x-data="{ open: @entangle('selectedActivityId').live }" x-show="open !== null" x-cloak
            x-on:keydown.escape.window="@this.closeActivityModal()" class="fixed inset-0 z-50 overflow-hidden"
            style="display: none;">
            {{-- Backdrop --}}
            <div x-show="open !== null" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/75 transition-opacity"
                x-on:click="@this.closeActivityModal()"></div>

            {{-- Sidebar Panel --}}
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="open !== null" x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                    class="w-screen max-w-4xl">
                    <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-gray-800 shadow-xl">
                        {{-- Header --}}
                        <div
                            class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Activity Details
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    View detailed information about this activity
                                </p>
                            </div>
                            <button type="button" x-on:click="@this.closeActivityModal()"
                                class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                <span class="sr-only">Close</span>
                                <x-filament::icon icon="heroicon-o-x-mark" class="h-6 w-6" />
                            </button>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 overflow-y-auto px-6 py-4">
                            @if($this->selectedActivity)
                                <div class="space-y-6">
                                    {{-- Description --}}
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Description</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $this->selectedActivity->getTranslatedDescription() }}
                                        </p>
                                    </div>

                                    {{-- Event --}}
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Event</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ ucfirst(str_replace(['_', '.'], ' ', $this->selectedActivity->event ?? 'Unknown')) }}
                                        </p>
                                    </div>

                                    {{-- Log Name --}}
                                    @if($this->selectedActivity->log_name)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Log Name</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $this->selectedActivity->log_name }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Causer --}}
                                    @if($this->selectedActivity->causer)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Causer</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $this->selectedActivity->causer->name ?? $this->selectedActivity->causer->email ?? 'Unknown' }}
                                                <span class="text-gray-400">
                                                    ({{ class_basename($this->selectedActivity->causer_type) }})
                                                </span>
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Subject --}}
                                    @if($this->selectedActivity->subject)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Subject</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ class_basename($this->selectedActivity->subject_type) }}
                                                <span class="text-gray-400">
                                                    (ID: {{ $this->selectedActivity->subject_id }})
                                                </span>
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Properties --}}
                                    @if($this->selectedActivity->properties && $this->selectedActivity->properties->count() > 0)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Properties</h4>
                                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                                        <tr>
                                                            <th scope="col"
                                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                Key</th>
                                                            <th scope="col"
                                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody
                                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                        @foreach($this->selectedActivity->properties->toArray() as $key => $value)
                                                            <tr>
                                                                <td
                                                                    class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white align-top">
                                                                    {{ $key }}
                                                                </td>
                                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                                    @if(is_array($value) || is_object($value))
                                                                        @php $level1Data = is_object($value) ? (array) $value : $value; @endphp
                                                                        {{-- Level 1 Nested Table --}}
                                                                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                                                <thead class="bg-gray-50 dark:bg-gray-800">
                                                                                    <tr>
                                                                                        <th scope="col"
                                                                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                                            Key</th>
                                                                                        <th scope="col"
                                                                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                                            Value</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                                                    @foreach($level1Data as $level1Key => $level1Value)
                                                                                        <tr>
                                                                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white align-top">
                                                                                                {{ $level1Key }}
                                                                                            </td>
                                                                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                                                                @if(is_array($level1Value) || is_object($level1Value))
                                                                                                    @php $level2Data = is_object($level1Value) ? (array) $level1Value : $level1Value; @endphp
                                                                                                    {{-- Level 2 Nested Table --}}
                                                                                                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                                                                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                                                                            <thead class="bg-gray-50 dark:bg-gray-800">
                                                                                                                <tr>
                                                                                                                    <th scope="col"
                                                                                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                                                                        Key</th>
                                                                                                                    <th scope="col"
                                                                                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                                                                        Value</th>
                                                                                                                </tr>
                                                                                                            </thead>
                                                                                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                                                                                @foreach($level2Data as $level2Key => $level2Value)
                                                                                                                    <tr>
                                                                                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white align-top">
                                                                                                                            {{ $level2Key }}
                                                                                                                        </td>
                                                                                                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                                                                                            @if(is_array($level2Value) || is_object($level2Value))
                                                                                                                                {{-- Beyond level 2, show as JSON --}}
                                                                                                                                <pre class="text-xs whitespace-pre-wrap break-all bg-gray-50 dark:bg-gray-900 p-2 rounded-lg border border-gray-200 dark:border-gray-700">{{ json_encode($level2Value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                                                            @elseif(is_bool($level2Value))
                                                                                                                                <span class="inline-flex items-center rounded px-2 py-1 text-xs font-medium {{ $level2Value ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                                                                                                    {{ $level2Value ? 'true' : 'false' }}
                                                                                                                                </span>
                                                                                                                            @elseif(is_null($level2Value))
                                                                                                                                <span class="text-gray-400 dark:text-gray-500 italic">null</span>
                                                                                                                            @else
                                                                                                                                {{ $level2Value }}
                                                                                                                            @endif
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                                                @endforeach
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </div>
                                                                                                @elseif(is_bool($level1Value))
                                                                                                    <span class="inline-flex items-center rounded px-2 py-1 text-xs font-medium {{ $level1Value ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                                                                        {{ $level1Value ? 'true' : 'false' }}
                                                                                                    </span>
                                                                                                @elseif(is_null($level1Value))
                                                                                                    <span class="text-gray-400 dark:text-gray-500 italic">null</span>
                                                                                                @else
                                                                                                    {{ $level1Value }}
                                                                                                @endif
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    @elseif(is_bool($value))
                                                                        <span class="inline-flex items-center rounded px-2 py-1 text-xs font-medium {{ $value ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                                            {{ $value ? 'true' : 'false' }}
                                                                        </span>
                                                                    @elseif(is_null($value))
                                                                        <span class="text-gray-400 dark:text-gray-500 italic">null</span>
                                                                    @else
                                                                        {{ $value }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Timestamp --}}
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Timestamp</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $this->selectedActivity->created_at->format('M j, Y H:i:s') }}
                                            <span class="text-gray-400">
                                                ({{ $this->selectedActivity->created_at->diffForHumans() }})
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                            <x-filament::button x-on:click="@this.closeActivityModal()" color="gray">
                                Close
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end">
                <x-filament::button :href="$this->getFullPageUrl()" tag="a">
                    View Full Activity Log
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::section>
</x-filament-widgets::widget>