<?php

namespace Tests\Feature\Api;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TodoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_authentication(): void
    {
        $response = $this->getJson('/api/todos');

        $response->assertStatus(401);
    }

    public function test_api_lists_todos_with_pagination(): void
    {
        $user = User::factory()->create();
        Todo::factory()->count(20)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/todos');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'completed', 'priority'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'stats' => ['total', 'completed', 'pending', 'overdue'],
        ]);
    }

    public function test_api_creates_todo(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/todos', [
            'title' => 'API Todo',
            'priority' => 'high',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['title' => 'API Todo']);
        $this->assertDatabaseHas('todos', ['title' => 'API Todo']);
    }

    public function test_api_updates_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/todos/{$todo->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_api_toggles_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id, 'completed' => false]);
        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/todos/{$todo->id}/toggle");

        $response->assertStatus(200);
        $response->assertJsonFragment(['completed' => true]);
    }

    public function test_api_soft_deletes_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('todos', ['id' => $todo->id]);
    }

    public function test_api_lists_trashed_todos(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $todo->delete();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/todos/trashed');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $todo->id]);
    }

    public function test_api_restores_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $todo->delete();
        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/todos/{$todo->id}/restore");

        $response->assertStatus(200);
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'deleted_at' => null,
        ]);
    }

    public function test_api_force_deletes_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $todo->delete();
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/todos/{$todo->id}/force");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_api_cannot_access_others_todo(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/todos/{$todo->id}");

        $response->assertStatus(403);
    }
}
