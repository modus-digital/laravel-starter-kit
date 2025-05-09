<?php

namespace App\Livewire\Profile\Sessions;

use Illuminate\Support\Collection;
use Jenssegers\Agent\Agent;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\On;

class ShowBrowserSessions extends Component
{
    public ?Collection $sessions = null;

    #[On('cleared-browser-sessions')]
    public function mount()
    {
        if (config(key: 'session.driver') !== 'database') return;
        if (!Auth::user()) return;

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

    private function createAgent(mixed $session)
    {
        return tap(
            value: new Agent,
            callback: fn($agent) => $agent->setUserAgent(userAgent: $session->user_agent)
        );
    }

    public function render()
    {
        return view('livewire.profile.sessions.show-browser-sessions');
    }
}
