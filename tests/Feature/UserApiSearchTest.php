<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserApiSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_users_list()
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        User::factory()->count(5)->create(['active' => true]);

        $this->be($admin);
        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'page',
                'users' => [
                    '*' => [
                        'id',
                        'email',
                        'name',
                        'role',
                        'created_at',
                        'orders_count',
                        'can_edit'
                    ]
                ]
            ]);
    }

    public function test_can_search_users()
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        User::factory()->create([
            'name' => 'Test User',
            'active' => true
        ]);
        User::factory()->create([
            'name' => 'Test User 2',
            'active' => true
        ]);

        $this->be($admin);
        $response = $this->getJson('/api/users?search=Test User 2');

        $response->assertStatus(200);

        $users = $response->json('users');
        $this->assertCount(1, $users);
        $this->assertEquals('Test User 2', $users[0]['name']);
    }

    public function test_can_sort_users()
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        User::factory()->create(['name' => 'ATest User', 'active' => true]);
        User::factory()->create(['name' => 'CTest User', 'active' => true]);
        User::factory()->create(['name' => 'BTest User', 'active' => true]);

        $this->be($admin);
        $response = $this->getJson('/api/users?sortBy=name');

        $response->assertStatus(200);

        $users = $response->json('users');
        $names = array_column($users, 'name');

        $this->assertEquals('ATest User', $names[0]);
        $this->assertEquals('BTest User', $names[1]);
    }
}
