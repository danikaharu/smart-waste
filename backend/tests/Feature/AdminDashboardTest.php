<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_with_stats(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Report::create([
            'user_id' => $admin->id,
            'title' => 'Sample Report',
            'description' => 'Test Desc',
            'image_path' => 'reports/sample.jpg',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'waste_category' => 'Plastic Waste',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sample Report');
        $response->assertViewHas('stats');
        $response->assertViewHas('categoryCounts');
        $response->assertViewHas('geolocatedReports');
    }
}
