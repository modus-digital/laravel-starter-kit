<div class="space-y-6">
    {{-- Description --}}
    <div>
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('admin.activities.modal.description') }}</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $activity->getTranslatedDescription() }}
        </p>
    </div>

    {{-- Event --}}
    <div>
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('admin.activities.modal.event') }}</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ ucfirst(str_replace(['_', '.'], ' ', $activity->event ?? 'Unknown')) }}
        </p>
    </div>

    {{-- Log Name --}}
    @if($activity->log_name)
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('admin.activities.modal.log_name') }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $activity->log_name }}
            </p>
        </div>
    @endif

    {{-- Causer --}}
    @if($activity->causer)
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('admin.activities.modal.causer') }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $activity->causer->name ?? $activity->causer->email ?? 'Unknown' }}
                <span class="text-gray-400">
                    ({{ class_basename($activity->causer_type) }})
                </span>
            </p>
        </div>
    @endif

    {{-- Subject --}}
    @if($activity->subject)
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('admin.activities.modal.subject') }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ class_basename($activity->subject_type) }}
                <span class="text-gray-400">
                    (ID: {{ $activity->subject_id }})
                </span>
            </p>
        </div>
    @endif

    {{-- Properties --}}
    @if($activity->properties && $activity->properties->count() > 0)
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('admin.activities.modal.properties') }}</h4>
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.activities.modal.properties_key') }}</th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.activities.modal.properties_value') }}</th>
                        </tr>
                    </thead>
                    <tbody
                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($activity->properties->toArray() as $key => $value)
                            <tr>
                                <td
                                    class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white align-top">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
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
                                                            {{ __('admin.activities.modal.properties_key') }}</th>
                                                        <th scope="col"
                                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                            {{ __('admin.activities.modal.properties_value') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    @foreach($level1Data as $level1Key => $level1Value)
                                                        <tr>
                                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white align-top">
                                                                {{ ucfirst(str_replace('_', ' ', $level1Key)) }}
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
                                                                                        {{ __('admin.activities.modal.properties_key') }}</th>
                                                                                    <th scope="col"
                                                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                                        {{ __('admin.activities.modal.properties_value') }}</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                                                @foreach($level2Data as $level2Key => $level2Value)
                                                                                    <tr>
                                                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white align-top">
                                                                                            {{ ucfirst(str_replace('_', ' ', $level2Key)) }}
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
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('admin.activities.modal.timestamp') }}</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $activity->created_at->format('M j, Y H:i:s') }}
            <span class="text-gray-400">
                ({{ $activity->created_at->diffForHumans() }})
            </span>
        </p>
    </div>
</div>

