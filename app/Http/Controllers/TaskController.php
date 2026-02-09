<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Modules\Tasks\TaskViewType;
use App\Events\Comments\CommentAdded;
use App\Http\Requests\Tasks\AddCommentRequest;
use App\Http\Requests\Tasks\CreateTaskRequest;
use App\Http\Requests\Tasks\CreateTaskViewRequest;
use App\Http\Requests\Tasks\DeleteTaskRequest;
use App\Http\Requests\Tasks\DeleteTaskViewRequest;
use App\Http\Requests\Tasks\MakeDefaultTaskViewRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskViewRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskView;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity as ActivityFacade;

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
            component: 'modules/tasks/index',
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
            ->get()
            ->map(fn (Activity $activity): array => [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'translated_description' => $activity->getTranslatedDescription(),
                'translation' => $activity->getTranslationPayload(),
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'event' => $activity->event,
                'causer_type' => $activity->causer_type,
                'causer_id' => $activity->causer_id,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at,
                'updated_at' => $activity->updated_at,
                'causer' => $activity->causer ? [
                    'id' => $activity->causer->id,
                    'name' => $activity->causer->name,
                    'email' => $activity->causer->email,
                ] : null,
            ]);

        return Inertia::render(
            component: 'modules/tasks/show',
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

        $task = $this->taskService->createNewTask(
            user: $request->user(),
            data: $request->validated(),
            currentClientId: session()->get('current_client_id'),
        );

        return redirect()->route('tasks.show', $task);
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

        return redirect()->back();
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

    public function addComment(AddCommentRequest $request, Task $task): RedirectResponse
    {
        abort_if(boolean: $request->user() === null, code: 401);

        $user = $request->user();
        $comment = $request->validated('comment');

        ActivityFacade::inLog('tasks')
            ->event('tasks.comments.created')
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties([
                'comment' => $comment,
            ])
            ->log('tasks.comments.created');

        // Load relationships for event
        $task->load(['assignedTo', 'createdBy']);

        // Dispatch CommentAdded event
        Event::dispatch(new CommentAdded(
            task: $task,
            commenter: $user,
            comment: $comment,
        ));

        return redirect()->back();
    }

    /**
     * Get activities for a task (API endpoint for dialogs).
     */
    public function activities(Task $task): \Illuminate\Http\JsonResponse
    {
        abort_if(boolean: request()->user() === null, code: 401);

        $user = request()->user();
        $this->taskService->ensureUserCanAccessTask(
            user: $user,
            task: $task,
            currentClientId: session()->get('current_client_id'),
        );

        $activities = Activity::query()
            ->where('log_name', 'tasks')
            ->where('subject_type', Task::class)
            ->where('subject_id', $task->getKey())
            ->with(['causer'])
            ->latest('created_at')
            ->get();

        return response()->json(['activities' => ActivityResource::collection($activities)->toArray(request())]);
    }
}
