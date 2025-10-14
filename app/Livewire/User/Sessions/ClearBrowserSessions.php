<?php

declare(strict_types=1);

namespace App\Livewire\User\Sessions;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Masmerise\Toaster\Toastable;

final class ClearBrowserSessions extends Component
{
    use Toastable;

    #[Rule('required|current_password')]
    public string $password = '';

    public function clearBrowserSessions(): void
    {
        $this->validate();

        if (config(key: 'session.driver') !== 'database' || ! Auth::user()) {
            return;
        }

        $connection = config(key: 'session.connection', default: null);
        $table = config(key: 'session.table', default: 'sessions');

        // Check if there are any other sessions to clear
        $sessions = DB::connection($connection)->table($table)
            ->where(column: 'user_id', operator: '=', value: Auth::user()->getAuthIdentifier())
            ->where(column: 'id', operator: '!=', value: request()->session()->getId());

        if ($sessions->count() === 0) {
            $this->warning(
                message: __('user.sessions.messages.none_to_clear')
            );

            $this->dispatch('close-modal');

            return;
        }

        try {
            DB::transaction(function () use ($sessions) {
                $sessions->delete();

                $this->success(
                    message: __('user.sessions.messages.cleared')
                );
            });
        } catch (Exception $e) {
            $this->error(
                message: __('user.sessions.messages.failed')
            );

            report($e);
        } finally {
            $this->dispatch('close-modal');
            $this->dispatch('refresh-sessions');
        }
    }

    public function render(): View
    {
        return view('livewire.user.sessions.clear-browser-sessions');
    }
}
