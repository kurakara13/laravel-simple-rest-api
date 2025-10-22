<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserCreatedNotification;
use App\Notifications\AdminUserCreatedNotification;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_data()
    {
        Notification::fake();

        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'name' => 'Test User'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'email',
                'name',
                'created_at'
            ])
            ->assertJson([
                'email' => 'test@example.com',
                'name' => 'Test User'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }

    public function test_cannot_create_user_without_right_data()
    {
        $response = $this->postJson('/api/users', [
            'email' => 'not-an-email',
            'password' => 'short',
            'name' => 'Te'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password',
                'name'
            ]);
    }

    public function test_create_user_email_notification()
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new UserCreatedNotification($user));

        Notification::assertSentTo($user, UserCreatedNotification::class, function ($notification, $channels) use ($user) {
            $this->assertContains('mail', $channels); // Ensure it's sent via mail channel

            $mailMessage = $notification->toMail($user);

            return true;
        });
    }

    public function test_create_admin_email_notification()
    {
        Notification::fake();

        $user = User::factory()->create();
        $userAdmin = User::factory()->create();

        $userAdmin->notify(new AdminUserCreatedNotification($user->name, $user->email));

        Notification::assertSentTo($userAdmin, AdminUserCreatedNotification::class, function ($notification, $channels) use ($userAdmin) {
            $this->assertContains('mail', $channels); // Ensure it's sent via mail channel

            $mailMessage = $notification->toMail($userAdmin);

            return true;
        });
    }
}
