<?php

declare(strict_types=1);

namespace App\Livewire\User\Sessions;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Livewire\Attributes\On;
use Livewire\Component;

final class ShowBrowserSessions extends Component
{
    /**
     * @var Collection<int, Collection<string, mixed>>|null
     */
    public ?Collection $sessions = null;

    #[On('refresh-sessions')]
    public function mount(): void
    {
        if (config('session.driver') !== 'database' || ! Auth::user()) {
            return;
        }

        $connection = config('session.connection', null);
        $table = config('session.table', 'sessions');

        $this->sessions = DB::connection($connection)
            ->table($table)
            ->where('user_id', Auth::user()->id)
            ->get()
            ->map(
                callback: function ($session): Collection {
                    $agent = $this->createAgent($session);

                    return collect([
                        'device' => [
                            'browser' => $agent->browser(),
                            'desktop' => $agent->isDesktop(),
                            'mobile' => $agent->isMobile(),
                            'tablet' => $agent->isTablet(),
                            'platform' => $agent->platform(),
                        ],
                        'ip_address' => $session->ip_address,
                        'is_current_device' => $session->id === request()->session()->getId(),
                        'last_active' => CarbonImmutable::createFromTimestamp($session->last_activity)->diffForHumans(),
                    ]);
                }
            );
    }

    public function render(): View
    {
        return view('livewire.user.sessions.show-browser-sessions');
    }

    private function createAgent(mixed $session): Agent
    {
        return tap(
            value: new Agent(),
            callback: fn (Agent $agent) => $agent->setUserAgent(
                userAgent: $session->user_agent
            )
        );
    }
}
