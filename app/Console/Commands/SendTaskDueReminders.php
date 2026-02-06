<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\Tasks\TaskDueApproaching;
use App\Models\Modules\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

final class SendTaskDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-due-reminders {--days=1 : Number of days before due date to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for tasks that are due soon';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysUntilDue = (int) $this->option('days');
        $dueDate = Carbon::today()->addDays($daysUntilDue);

        $tasks = Task::query()
            ->whereNotNull('due_date')
            ->whereNull('completed_at')
            ->whereDate('due_date', $dueDate)
            ->with(['assignedTo', 'createdBy'])
            ->get();

        foreach ($tasks as $task) {
            // Dispatch event once per task (listener will handle notifying all users)
            Event::dispatch(new TaskDueApproaching(
                task: $task,
                daysUntilDue: $daysUntilDue,
            ));
        }

        $this->info("Dispatched due date reminder events for {$tasks->count()} tasks.");

        return self::SUCCESS;
    }
}
