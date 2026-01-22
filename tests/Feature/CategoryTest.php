<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Trabalho',
            'color' => '#ff5733',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Trabalho',
            'user_id' => $user->id,
        ]);
    }

    public function test_category_name_unique_per_user(): void
    {
        $user = User::factory()->create();
        Category::factory()->create(['user_id' => $user->id, 'name' => 'Trabalho']);

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Trabalho',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_different_users_can_have_same_category_name(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Category::factory()->create(['user_id' => $user1->id, 'name' => 'Trabalho']);

        $response = $this->actingAs($user2)->post(route('categories.store'), [
            'name' => 'Trabalho',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Trabalho',
            'user_id' => $user2->id,
        ]);
    }

    public function test_deleting_category_nullifies_todos(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'category_id' => null,
        ]);
    }

    public function test_user_cannot_update_others_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }
}
