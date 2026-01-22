<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'completed' => false,
            'priority' => fake()->randomElement(Priority::cases()),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function withCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::factory()->create(['user_id' => $attributes['user_id']])->id,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => false,
            'due_date' => now()->subDays(5),
        ]);
    }
}
