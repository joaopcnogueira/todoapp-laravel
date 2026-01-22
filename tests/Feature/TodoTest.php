<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_todos(): void
    {
        $user = User::factory()->create();
        Todo::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('todos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('todos');
    }

    public function test_user_cannot_view_others_todos(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('todos.edit', $todo));

        $response->assertStatus(403);
    }

    public function test_user_can_create_todo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('todos.store'), [
            'title' => 'Nova tarefa',
            'description' => 'Descricao da tarefa',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('todos.index'));
        $this->assertDatabaseHas('todos', [
            'title' => 'Nova tarefa',
            'user_id' => $user->id,
        ]);
    }

    public function test_todo_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('todos.store'), [
            'description' => 'Apenas descricao',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_user_can_update_own_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('todos.update', $todo), [
            'title' => 'Titulo atualizado',
        ]);

        $response->assertRedirect(route('todos.index'));
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => 'Titulo atualizado',
        ]);
    }

    public function test_user_can_toggle_todo_status(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id, 'completed' => false]);

        $response = $this->actingAs($user)->patch(route('todos.toggle', $todo));

        $response->assertRedirect();
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'completed' => true,
        ]);
    }

    public function test_user_can_soft_delete_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('todos.destroy', $todo));

        $response->assertRedirect(route('todos.index'));
        $this->assertSoftDeleted('todos', ['id' => $todo->id]);
    }

    public function test_user_can_restore_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $todo->delete();

        $response = $this->actingAs($user)->patch(route('todos.restore', $todo->id));

        $response->assertRedirect(route('todos.trashed'));
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_can_force_delete_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $todo->delete();

        $response = $this->actingAs($user)->delete(route('todos.force-delete', $todo->id));

        $response->assertRedirect(route('todos.trashed'));
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_todo_filters_work_correctly(): void
    {
        $user = User::factory()->create();
        Todo::factory()->create(['user_id' => $user->id, 'completed' => false]);
        Todo::factory()->completed()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('todos.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertViewHas('todos', function ($todos) {
            return $todos->count() === 1 && !$todos->first()->completed;
        });
    }
}
