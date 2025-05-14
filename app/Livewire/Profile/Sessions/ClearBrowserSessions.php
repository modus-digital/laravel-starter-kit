<?php

namespace App\Livewire\Profile\Sessions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

class ClearBrowserSessions extends Component
{
    use Toastable;

    #[Rule('required|current_password')]
    public string $password = '';

    public function clearBrowserSessions(): void
    {
        $this->validate();

        if (config(key: 'session.driver') !== 'database') {
            return;
        }

        if (! Auth::user()) {
            return;
        }

        $connection = config(key: 'session.connection', default: null);
        $table = config(key: 'session.table', default: 'sessions');

        // Check if there are any other sessions to clear
        $sessions = DB::connection($connection)->table($table)
            ->where(column: 'user_id', operator: '=', value: Auth::user()->getAuthIdentifier())
            ->where(column: 'id', operator: '!=', value: request()->session()->getId());

        if ($sessions->count() === 0) {
            $this
                ->warning(message: __('notifications.toasts.sessions.no_sessions'))
                ->duration(milliseconds: 3000);

            return;
        }

        try {
            DB::transaction(function () use ($sessions) {
                $sessions->delete();
            });

            $this->reset('password');
            $this->dispatch(event: 'cleared-browser-sessions');
            $this->success(message: __('notifications.toasts.sessions.cleared'));
        }
        catch (\Throwable $e) {

            $this->error(message: __('notifications.toasts.sessions.cleared_error'));
        }
        finally {
            $this->dispatch(event: 'close-modal');
        }
    }

    public function render()
    {
        return view('livewire.profile.sessions.clear-browser-sessions');
    }
}
