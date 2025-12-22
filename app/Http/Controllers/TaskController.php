<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Modules\Clients\Client;
use Inertia\Inertia;
use Inertia\Response;

final class TaskController extends Controller
{
    public function index(): Response
    {
        $client = Client::find(session()->get('current_client_id'));
        $tasks = $client?->tasks()->get();

        return Inertia::render(component: 'tasks/index', props: [
            'tasks' => $tasks,
        ]);
    }
}
