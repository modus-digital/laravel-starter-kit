<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TaskService;
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

        return Inertia::render(
            component: 'tasks/index', 
            props: [
                'tasks' => $tasks,
            ]
        );
    }
}
