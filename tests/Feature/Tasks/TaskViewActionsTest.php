<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\TaskView;
use App\Models\User;
use App\Services\TaskService;
use Database\Seeders\BootstrapApplicationSeeder;

beforeEach(function (): void {
    $this->seed(BootstrapApplicationSeeder::class);
});

describe('rename view', function (): void {
    it('renames a user-scoped view', function (): void {
        $user = User::factory()->create();
        $view = TaskView::factory()->for($user, 'taskable')->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->patch(route('tasks.views.update', $view), ['name' => 'New Name'])
            ->assertRedirect(route('tasks.index'));

        expect($view->fresh()->name)->toBe('New Name');
    });

    it('renames a client-scoped view when user is member', function (): void {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->users()->attach($user->id);

        $view = TaskView::factory()->for($client, 'taskable')->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->withSession(['current_client_id' => $client->id])
            ->patch(route('tasks.views.update', $view), ['name' => 'New Name'])
            ->assertRedirect(route('tasks.index'));

        expect($view->fresh()->name)->toBe('New Name');
    });

    it('forbids renaming a view the user cannot access', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $view = TaskView::factory()->for($otherUser, 'taskable')->create();

        $this->actingAs($user)
            ->patch(route('tasks.views.update', $view), ['name' => 'Hacked'])
            ->assertForbidden();
    });

    it('requires a valid name', function (): void {
        $user = User::factory()->create();
        $view = TaskView::factory()->for($user, 'taskable')->create();

        $this->actingAs($user)
            ->patch(route('tasks.views.update', $view), ['name' => ''])
            ->assertSessionHasErrors('name');
    });
});

describe('set default view', function (): void {
    it('sets a view as default and unsets others in the same scope', function (): void {
        $user = User::factory()->create();

        $view1 = TaskView::factory()->for($user, 'taskable')->create(['is_default' => true]);
        $view2 = TaskView::factory()->for($user, 'taskable')->create(['is_default' => false]);

        $this->actingAs($user)
            ->patch(route('tasks.views.makeDefault', $view2))
            ->assertRedirect(route('tasks.index'));

        expect($view1->fresh()->is_default)->toBeFalse();
        expect($view2->fresh()->is_default)->toBeTrue();
    });

    it('does not affect default views in a different scope', function (): void {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->users()->attach($user->id);

        $userView = TaskView::factory()->for($user, 'taskable')->create(['is_default' => true]);
        $clientView = TaskView::factory()->for($client, 'taskable')->create(['is_default' => false]);

        $this->actingAs($user)
            ->withSession(['current_client_id' => $client->id])
            ->patch(route('tasks.views.makeDefault', $clientView))
            ->assertRedirect(route('tasks.index'));

        // User's default should remain unchanged
        expect($userView->fresh()->is_default)->toBeTrue();
        // Client view is now default
        expect($clientView->fresh()->is_default)->toBeTrue();
    });

    it('forbids setting default on a view the user cannot access', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $view = TaskView::factory()->for($otherUser, 'taskable')->create();

        $this->actingAs($user)
            ->patch(route('tasks.views.makeDefault', $view))
            ->assertForbidden();
    });
});

describe('delete view', function (): void {
    it('deletes a non-default view', function (): void {
        $user = User::factory()->create();
        $view = TaskView::factory()->for($user, 'taskable')->create(['is_default' => false]);

        $this->actingAs($user)
            ->delete(route('tasks.views.delete', $view))
            ->assertRedirect(route('tasks.index'));

        expect(TaskView::find($view->id))->toBeNull();
        expect(TaskView::withTrashed()->find($view->id))->not->toBeNull();
    });

    it('forbids deleting the default view', function (): void {
        $user = User::factory()->create();
        $view = TaskView::factory()->for($user, 'taskable')->create(['is_default' => true]);

        $this->actingAs($user)
            ->delete(route('tasks.views.delete', $view))
            ->assertSessionHasErrors('taskView');

        expect(TaskView::find($view->id))->not->toBeNull();
    });

    it('forbids deleting a view the user cannot access', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $view = TaskView::factory()->for($otherUser, 'taskable')->create();

        $this->actingAs($user)
            ->delete(route('tasks.views.delete', $view))
            ->assertForbidden();
    });
});

describe('update view statuses', function (): void {
    it('updates the statuses for a view', function (): void {
        $user = User::factory()->create();
        $view = TaskView::factory()->for($user, 'taskable')->create();

        // Use unique names for statuses to avoid constraint violations
        $status1 = App\Models\Modules\Tasks\TaskStatus::firstOrCreate(
            ['name' => 'Test Status 1'],
            ['color' => '#ff0000']
        );
        $status2 = App\Models\Modules\Tasks\TaskStatus::firstOrCreate(
            ['name' => 'Test Status 2'],
            ['color' => '#00ff00']
        );

        $this->actingAs($user)
            ->patch(route('tasks.views.update', $view), ['status_ids' => [$status1->id, $status2->id]])
            ->assertRedirect(route('tasks.index'));

        $view->refresh();
        $viewStatusIds = $view->statuses->pluck('id')->toArray();

        expect($viewStatusIds)->toContain($status1->id);
        expect($viewStatusIds)->toContain($status2->id);
    });

    it('requires at least one status when updating statuses', function (): void {
        $user = User::factory()->create();
        $view = TaskView::factory()->for($user, 'taskable')->create();

        $this->actingAs($user)
            ->patch(route('tasks.views.update', $view), ['status_ids' => []])
            ->assertSessionHasErrors('status_ids');
    });

    it('validates status ids exist', function (): void {
        $user = User::factory()->create();
        $view = TaskView::factory()->for($user, 'taskable')->create();

        $this->actingAs($user)
            ->patch(route('tasks.views.update', $view), ['status_ids' => ['non-existent-uuid']])
            ->assertSessionHasErrors('status_ids.0');
    });
});

describe('view scoping', function (): void {
    it('shows both user and client views when client is selected', function (): void {
        $taskService = new TaskService();

        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->users()->attach($user->id);

        $userView = TaskView::factory()->for($user, 'taskable')->create();
        $clientView = TaskView::factory()->for($client, 'taskable')->create();
        $otherClientView = TaskView::factory()->for(Client::factory()->create(), 'taskable')->create();

        $accessibleViewIds = $taskService
            ->getTaskViewsForUser($user, $client->id)
            ->modelKeys();

        expect($accessibleViewIds)
            ->toContain($userView->id)
            ->toContain($clientView->id)
            ->not->toContain($otherClientView->id);
    });

    it('creates client-scoped view when client is selected', function (): void {
        $taskService = new TaskService();

        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->users()->attach($user->id);

        $view = $taskService->createTaskView(
            user: $user,
            name: 'Test View',
            currentClientId: $client->id,
        );

        expect($view->taskable_type)->toBe(Client::class);
        expect($view->taskable_id)->toBe($client->id);
    });

    it('creates user-scoped view when no client is selected', function (): void {
        $taskService = new TaskService();

        $user = User::factory()->create();

        $view = $taskService->createTaskView(
            user: $user,
            name: 'Test View',
        );

        expect($view->taskable_type)->toBe(User::class);
        expect($view->taskable_id)->toBe($user->id);
    });
});
