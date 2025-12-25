<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Modules\Tasks\TaskViewType;
use App\Http\Requests\Tasks\CreateTaskRequest;
use App\Http\Requests\Tasks\CreateTaskViewRequest;
use App\Http\Requests\Tasks\DeleteTaskRequest;
use App\Http\Requests\Tasks\DeleteTaskViewRequest;
use App\Http\Requests\Tasks\MakeDefaultTaskViewRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskViewRequest;
use App\Models\Activity;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskView;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(): Response
    {
        abort_if(boolean: request()->user() === null, code: 401);

        $tasks = $this->taskService->getAccessibleTasksForUser(
            user: request()->user(),
            currentClientId: session()->get('current_client_id') ?? null);

        $taskViews = $this->taskService->getTaskViewsForUser(
            user: request()->user(),
            currentClientId: session()->get('current_client_id') ?? null,
        );

        $statuses = $this->taskService->getStatuses();

        return Inertia::render(
            component: 'tasks/index',
            props: [
                'tasks' => $tasks,
                'taskViews' => $taskViews,
                'statuses' => $statuses,
            ]
        );
    }

    public function show(Task $task): Response
    {
        abort_if(boolean: request()->user() === null, code: 401);

        $user = request()->user();
        $this->taskService->ensureUserCanAccessTask(
            user: $user,
            task: $task,
            currentClientId: session()->get('current_client_id'),
        );

        $statuses = $this->taskService->getStatuses();

        $activities = Activity::query()
            ->where('log_name', 'tasks')
            ->where('subject_type', Task::class)
            ->where('subject_id', $task->getKey())
            ->with(['causer'])
            ->latest('created_at')
            ->get();

        return Inertia::render(
            component: 'tasks/show',
            props: [
                'task' => $task,
                'statuses' => $statuses,
                'activities' => $activities,
            ]
        );
    }

    public function store(CreateTaskRequest $request): RedirectResponse
    {
        abort_if(boolean: $request->user() === null, code: 401);

        $this->taskService->createNewTask(
            user: $request->user(),
            data: $request->validated(),
            currentClientId: session()->get('current_client_id'),
        );

        return redirect()->route('tasks.index');
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        abort_if(boolean: $request->user() === null, code: 401);

        $this->taskService->updateTask(
            user: $request->user(),
            task: $task,
            data: $request->validated(),
            currentClientId: session()->get('current_client_id'),
        );

        return redirect()->route('tasks.index');
    }

    public function destroy(DeleteTaskRequest $request, Task $task): RedirectResponse
    {
        abort_if(boolean: $request->user() === null, code: 401);

        $this->taskService->deleteTask(
            user: $request->user(),
            task: $task,
            currentClientId: session()->get('current_client_id'),
        );

        return redirect()->route('tasks.index');
    }

    public function createView(CreateTaskViewRequest $request): RedirectResponse
    {
        abort_if(boolean: request()->user() === null, code: 401);

        $this->taskService->createTaskView(
            user: request()->user(),
            name: $request->validated('name'),
            type: TaskViewType::from($request->validated('type')),
            statusIds: $request->validated('status_ids', []),
            currentClientId: session()->get('current_client_id'),
        );

        return redirect()->route('tasks.index');
    }

    public function updateView(UpdateTaskViewRequest $request, TaskView $taskView): RedirectResponse
    {
        if ($request->has('name')) {
            $this->taskService->renameTaskView(
                taskView: $taskView,
                name: $request->validated('name'),
            );
        }

        if ($request->has('status_ids')) {
            $this->taskService->updateTaskViewStatuses(
                taskView: $taskView,
                statusIds: $request->validated('status_ids'),
            );
        }

        return redirect()->route('tasks.index');
    }

    public function makeDefaultView(MakeDefaultTaskViewRequest $request, TaskView $taskView): RedirectResponse
    {
        $this->taskService->setDefaultTaskView(taskView: $taskView);

        return redirect()->route('tasks.index');
    }

    public function deleteView(DeleteTaskViewRequest $request, TaskView $taskView): RedirectResponse
    {
        $this->taskService->deleteTaskView(taskView: $taskView);

        return redirect()->route('tasks.index');
    }
}
