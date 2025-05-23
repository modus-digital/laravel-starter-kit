<?php

namespace App\Livewire\Profile\Sessions;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowBrowserSessions extends Component
{
    public ?Collection $sessions = null;

    /**
     * Mount the component and set the sessions.
     *
     * If the event 'cleared-browser-sessions' is dispatched, this component will be re-rendered.
     */
    #[On('cleared-browser-sessions')]
    public function mount(): void
    {
        if (config(key: 'session.driver') !== 'database') {
            return;
        }

        if (! Auth::user()) {
            return;
        }

        $connection = config(key: 'session.connection', default: null);
        $table = config(key: 'session.table', default: 'sessions');

        $this->sessions = DB::connection($connection)->table($table)
            ->where(column: 'user_id', operator: '=', value: Auth::user()->getAuthIdentifier())
            ->get()
            ->map(callback: function ($session): Collection {
                $agent = $this->createAgent($session);
                $sessionInfo = [
                    'device' => [
                        'browser' => $agent->browser(),
                        'desktop' => $agent->isDesktop(),
                        'mobile' => $agent->isMobile(),
                        'tablet' => $agent->isTablet(),
                        'platform' => $agent->platform(),
                    ],
                    'ip_address' => $session->ip_address,
                    'is_current_device' => $session->id === request()->session()->getId(),
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];

                return collect($sessionInfo);
            });
    }

    /**
     * Create an agent for the session.
     *
     * @param  mixed  $session
     * @return Agent
     */
    private function createAgent(mixed $session)
    {
        return tap(
            value: new Agent(),
            callback: fn ($agent) => $agent->setUserAgent(userAgent: $session->user_agent)
        );
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render()
    {
        return view('livewire.profile.sessions.show-browser-sessions');
    }
}
