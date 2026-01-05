<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\UserNotificationService;
use Illuminate\Database\Seeder;

final class NotificationSeeder extends Seeder
{
    private int $MIN_NOTIFICATIONS_PER_USER = 3;

    private int $MAX_NOTIFICATIONS_PER_USER = 10;

    public function __construct(
        private readonly UserNotificationService $notifier,
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($users->isEmpty()) {
                return;
            }

            $notificationsToCreate = random_int(
                min: $this->MIN_NOTIFICATIONS_PER_USER,
                max: $this->MAX_NOTIFICATIONS_PER_USER
            );

            for ($index = 0; $index < $notificationsToCreate; $index++) {
                $payload = $this->createPayload($index);

                $this->notifier->notify(
                    user: $user,
                    title: $payload['title'],
                    body: $payload['body'],
                    actionUrl: $payload['action_url'],
                );
            }
        }
    }

    /**
     * @return array{title: string, body: string|null, action_url: string|null}
     */
    private function createPayload(int $index): array
    {
        $templates = [
            [
                'title' => 'Welcome to Modus',
                'body' => 'Thanks for joining! Start by creating your first project.',
                'action_url' => '/projects',
            ],
            [
                'title' => 'Complete your profile',
                'body' => 'Add your name and avatar so your teammates can recognise you.',
                'action_url' => '/profile',
            ],
            [
                'title' => 'New notification',
                'body' => 'You have a new notification waiting in your inbox.',
                'action_url' => '/notifications',
            ],
            [
                'title' => 'Action needed',
                'body' => 'Please review the latest changes and confirm everything looks good.',
                'action_url' => '/reviews',
            ],
            [
                'title' => 'Security alert',
                'body' => 'We noticed a new sign-in to your account. If this wasn’t you, update your password.',
                'action_url' => '/settings/security',
            ],
            [
                'title' => 'Billing reminder',
                'body' => 'Your subscription will renew soon. Review your billing details if anything has changed.',
                'action_url' => '/billing',
            ],
            [
                'title' => 'New team invitation',
                'body' => 'You’ve been invited to join a team. Accept the invitation to start collaborating.',
                'action_url' => '/teams',
            ],
            [
                'title' => 'System update completed',
                'body' => 'We’ve just deployed an update. Everything should continue working as expected.',
                'action_url' => null,
            ],
            [
                'title' => 'Comment on your activity',
                'body' => 'Someone left a comment on your recent activity.',
                'action_url' => '/activity',
            ],
            [
                'title' => 'Weekly summary',
                'body' => 'Here’s a quick overview of what happened in your workspace this week.',
                'action_url' => '/reports/weekly',
            ],
            [
                'title' => 'New feature available',
                'body' => 'We’ve added a new feature to help you stay on top of your work.',
                'action_url' => '/changelog',
            ],
            [
                'title' => 'Maintenance notice',
                'body' => null,
                'action_url' => null,
            ],
        ];

        $template = $templates[$index % count($templates)];

        if ($template['body'] !== null && random_int(1, 100) <= 5) {
            $template['body'] = null;
        }

        return $template;
    }
}
