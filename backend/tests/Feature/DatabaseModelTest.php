<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_roles_and_reports(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCitizen());

        $report = Report::create([
            'user_id' => $admin->id,
            'title' => 'Botol Plastik Menumpuk',
            'description' => 'Banyak botol plastik di selokan.',
            'image_path' => 'reports/sample.jpg',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'waste_category' => 'Plastic Waste',
            'ai_confidence' => 0.9556,
            'status' => 'pending'
        ]);

        $this->assertEquals($admin->id, $report->user->id);
        $this->assertCount(1, $admin->reports);
        $this->assertEquals('pending', $report->status);
    }
}
