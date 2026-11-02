<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_and_update_role(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $citizen = User::create([
            'name' => 'Citizen User',
            'email' => 'citizen@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Citizen User');

        $updateResp = $this->actingAs($admin)->put("/admin/users/{$citizen->id}/role", [
            'role' => 'admin'
        ]);
        $updateResp->assertRedirect();
        $this->assertEquals('admin', $citizen->fresh()->role);
    }
}
