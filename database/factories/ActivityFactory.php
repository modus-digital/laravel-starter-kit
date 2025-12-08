<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
final class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $events = ['created', 'updated', 'deleted', 'login', 'logout', 'failed_login'];
        $subjects = [User::class, null];
        $causers = [User::class, null];

        $event = fake()->randomElement($events);
        $subjectType = fake()->randomElement($subjects);
        $causerType = fake()->randomElement($causers);

        return [
            'log_name' => 'default',
            'description' => "activity.{$event}",
            'event' => $event,
            'subject_type' => $subjectType,
            'subject_id' => $subjectType ? fake()->uuid() : null,
            'causer_type' => $causerType,
            'causer_id' => $causerType ? fake()->uuid() : null,
            'properties' => [
                'issuer' => [
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                ],
            ],
        ];
    }

    /**
     * Create an activity for a specific user.
     */
    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }

    /**
     * Create an activity with a specific event.
     */
    public function withEvent(string $event): self
    {
        return $this->state(fn (array $attributes): array => [
            'event' => $event,
            'description' => "activity.{$event}",
        ]);
    }
}
