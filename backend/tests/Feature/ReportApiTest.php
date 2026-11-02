<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_auth_and_report_submission_with_ai(): void
    {
        Storage::fake('public');

        // Mock ML service response
        Http::fake([
            '*/predict*' => Http::response([
                'status' => 'success',
                'data' => [
                    'label' => 'Plastic Waste',
                    'category' => 'Anorganik (Plastik)',
                    'confidence' => 0.9854,
                    'probabilities' => [
                        'Organic Waste' => 0.0100,
                        'Paper Waste' => 0.0046,
                        'Plastic Waste' => 0.9854,
                    ],
                ],
            ], 200),
        ]);

        // 1. Register API
        $regResp = $this->postJson('/api/register', [
            'name' => 'Mobile User',
            'email' => 'mobile@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '08123456789',
        ]);
        $regResp->assertStatus(201);
        $regResp->assertJsonPath('status', 'success');
        $token = $regResp->json('data.token');
        $this->assertNotEmpty($token);

        // 2. Submit Report with photo & GPS
        $file = UploadedFile::fake()->image('waste.jpg');
        $submitResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/reports', [
                'title' => 'Sampah Botol Plastik',
                'description' => 'Ditemukan di dekat taman',
                'image' => $file,
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address_note' => 'Taman Suropati',
            ]);

        $submitResp->assertStatus(201);
        $this->assertEquals('Plastic Waste', $submitResp->json('data.waste_category'));
        $this->assertEquals(0.9854, $submitResp->json('data.ai_confidence'));
        $reportId = $submitResp->json('data.id');

        // Verify storage has file
        $imagePath = $submitResp->json('data.image_path');
        Storage::disk('public')->assertExists($imagePath);

        // 3. Get Report Detail API
        $detailResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/reports/{$reportId}");
        $detailResp->assertStatus(200);
        $this->assertEquals('Sampah Botol Plastik', $detailResp->json('data.title'));

        // 4. Get Report List API
        $listResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/reports');
        $listResp->assertStatus(200);
        $this->assertCount(1, $listResp->json('data'));

        // 5. AI Proxy Standalone Endpoint
        $detectResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/detect-waste', [
                'image' => UploadedFile::fake()->image('detect.jpg'),
            ]);
        $detectResp->assertStatus(200);
        $this->assertEquals('Plastic Waste', $detectResp->json('data.label'));

        // 6. Dashboard Stats Endpoint
        $statsResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard/stats');
        $statsResp->assertStatus(200);
        $this->assertArrayHasKey('total_reports', $statsResp->json('data'));

        // 7. Logout API
        $logoutResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');
        $logoutResp->assertStatus(200);
    }

    public function test_api_login_validation_and_failure(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Wrong password
        $failResp = $this->postJson('/api/login', [
            'email' => 'existing@test.com',
            'password' => 'wrongpass',
        ]);
        $failResp->assertStatus(422);

        // Correct password
        $successResp = $this->postJson('/api/login', [
            'email' => 'existing@test.com',
            'password' => 'secret123',
        ]);
        $successResp->assertStatus(200);
        $this->assertNotEmpty($successResp->json('data.token'));
    }

    public function test_public_feed_does_not_leak_user_phone_or_email_to_citizens(): void
    {
        $citizen = User::create([
            'name' => 'Citizen Reporter',
            'email' => 'secret_citizen@test.com',
            'phone' => '08999999999',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $viewer = User::create([
            'name' => 'Viewer Citizen',
            'email' => 'viewer@test.com',
            'phone' => '08111111111',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        Report::create([
            'user_id' => $citizen->id,
            'title' => 'Tumpukan Sampah Plastik',
            'description' => 'Di pinggir jalan',
            'image_path' => 'reports/test.jpg',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'waste_category' => 'Plastic Waste',
            'status' => 'pending',
        ]);

        $viewerToken = $viewer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$viewerToken}")
            ->getJson('/api/reports?public_feed=true');

        $response->assertStatus(200);
        $data = $response->json('data.0');
        $this->assertNotNull($data);
        $this->assertEquals('Citizen Reporter', $data['user']['name']);
        // Crucial security assertions: email and phone must be omitted from public feed
        $this->assertArrayNotHasKey('email', $data['user']);
        $this->assertArrayNotHasKey('phone', $data['user']);
    }

    public function test_rejects_invalid_upload_mime_types(): void
    {
        $user = User::create([
            'name' => 'Uploader',
            'email' => 'uploader@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        // Try uploading a plain text file pretending to be an image
        $fakeTxt = UploadedFile::fake()->create('malicious.txt', 100, 'text/plain');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/reports', [
                'title' => 'Test Malicious File',
                'image' => $fakeTxt,
                'latitude' => -6.2,
                'longitude' => 106.8,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }
}

