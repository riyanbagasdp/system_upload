<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskComment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending','in_progress','completed']),
            'priority' => $this->faker->randomElement(['low','medium','high']),
            'assigned_user_id' => User::inRandomOrder()->first()->id ?? null,
            'created_by' => User::inRandomOrder()->first()->id ?? 1,
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ];
    }
}

