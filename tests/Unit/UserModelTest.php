<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_edit_any_user()
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $targetUser = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->canEdit($targetUser));
    }

    public function test_manager_can_edit_users()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($manager->canEdit($user));
    }

    public function test_manager_cannot_edit_administrators()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $admin = User::factory()->create(['role' => 'administrator']);

        $this->assertFalse($manager->canEdit($admin));
    }

    public function test_user_can_edit_themselves()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($user->canEdit($user));
    }

    public function test_user_cannot_edit_other_users()
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->canEdit($otherUser));
    }
}
