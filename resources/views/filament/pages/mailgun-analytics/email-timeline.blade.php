@php
    use App\Enums\Modules\Mailgun\EmailEventType;
    use App\Models\Modules\Mailgun\EmailEvent;
    use App\Models\Modules\Mailgun\EmailMessage;
    
    /** @var EmailMessage $emailMessage */
    $events = $emailMessage->events()->orderBy('occurred_at', 'desc')->get();
@endphp

<div class="space-y-6">
    {{-- Email Details --}}
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold">{{ __('admin.mailgun.modal.email_details') }}</h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.mailgun.modal.from') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $emailMessage->from_name ? "{$emailMessage->from_name} <{$emailMessage->from_address}>" : $emailMessage->from_address }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.mailgun.modal.to') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $emailMessage->to_name ? "{$emailMessage->to_name} <{$emailMessage->to_address}>" : $emailMessage->to_address }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.mailgun.modal.subject') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $emailMessage->subject }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.mailgun.modal.status') }}</dt>
                <dd class="mt-1">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                        style="background-color: rgb(var(--{{ $emailMessage->status->getColor() }}-500) / 0.1); color: rgb(var(--{{ $emailMessage->status->getColor() }}-600));">
                        {{ $emailMessage->status->getLabel() }}
                    </span>
                </dd>
            </div>
            @if($emailMessage->mailable_class)
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.mailgun.modal.mailable') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ class_basename($emailMessage->mailable_class) }}</dd>
            </div>
            @endif
            @if($emailMessage->sent_at)
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.mailgun.modal.sent_at') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $emailMessage->sent_at->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Event Timeline --}}
    <div>
        <h3 class="mb-4 text-lg font-semibold">{{ __('admin.mailgun.modal.event_timeline') }}</h3>
        @if($events->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.mailgun.modal.no_events') }}</p>
        @else
            <div class="relative">
                @foreach($events as $index => $event)
                    @php
                        /** @var EmailEvent $event */
                    @endphp
                    <div class="relative flex gap-4 pb-8 {{ $loop->last ? '' : 'border-l-2 border-gray-200 dark:border-gray-700' }}">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 border-gray-200 dark:border-gray-700"
                            style="background-color: rgb(var(--{{ $event->event_type->getColor() }}-500) / 0.1); border-color: rgb(var(--{{ $event->event_type->getColor() }}-500));">
                            <div class="h-2 w-2 rounded-full"
                                style="background-color: rgb(var(--{{ $event->event_type->getColor() }}-600));"></div>
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $event->event_type->getLabel() }}
                                    </p>
                                    @if($event->reason)
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $event->reason }}</p>
                                    @endif
                                    @if($event->url)
                                        <p class="mt-1 text-sm">
                                            <a href="{{ $event->url }}" target="_blank" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                                {{ $event->url }}
                                            </a>
                                        </p>
                                    @endif
                                </div>
                                <time class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $event->occurred_at->format('Y-m-d H:i:s') }}
                                </time>
                            </div>
                            @if($event->ip_address || $event->user_agent)
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if($event->ip_address)
                                        <span>IP: {{ $event->ip_address }}</span>
                                    @endif
                                    @if($event->user_agent)
                                        <span class="ml-2">{{ $event->user_agent }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Raw Payload (Collapsible) --}}
    @if($events->isNotEmpty())
        <div x-data="{ open: false }">
            <button @click="open = !open" class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('admin.mailgun.modal.raw_payload') }}</span>
                <svg class="h-5 w-5 text-gray-500 transition-transform dark:text-gray-400" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                <pre class="overflow-x-auto text-xs text-gray-800 dark:text-gray-200">{{ json_encode($events->map(fn($e) => $e->raw_payload), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif
</div>

