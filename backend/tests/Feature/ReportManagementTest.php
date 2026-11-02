<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_reports_and_update_status(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $report = Report::create([
            'user_id' => $admin->id,
            'title' => 'Plastik Kali Ciliwung',
            'description' => 'Sampah mengapung',
            'image_path' => 'reports/sample.jpg',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'waste_category' => 'Plastic Waste',
            'status' => 'pending'
        ]);

        // Search filter test
        $response = $this->actingAs($admin)->get('/admin/reports?search=Ciliwung');
        $response->assertStatus(200);
        $response->assertSee('Plastik Kali Ciliwung');

        // Status update test
        $updateResp = $this->actingAs($admin)->put("/admin/reports/{$report->id}/status", [
            'status' => 'in_progress',
            'admin_notes' => 'Petugas kebersihan telah dikirim.'
        ]);
        $updateResp->assertRedirect();
        
        $this->assertEquals('in_progress', $report->fresh()->status);
        $this->assertEquals('Petugas kebersihan telah dikirim.', $report->fresh()->admin_notes);
    }
}
