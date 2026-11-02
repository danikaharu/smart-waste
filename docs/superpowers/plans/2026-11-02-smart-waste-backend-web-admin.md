# Smart Waste Backend & Web Admin Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build, test, and deliver the Laravel 11 Backend REST API, MySQL database, and responsive Web Admin & User Dashboard with Leaflet GPS mapping, Chart.js analytics, role-based auth, and ML FastAPI integration.

**Architecture:** Monolithic Laravel 11 application in `backend/`. Uses MySQL `smart_waste_db`, Laravel Sanctum for mobile token authentication, standard session auth for web portals, Blade templates styled with modern Tailwind CSS, Alpine.js, Chart.js for statistics, Leaflet.js for interactive GIS mapping, and an integrated HTTP client connecting to the FastAPI ML service (`http://127.0.0.1:8000/predict`).

**Tech Stack:** PHP 8.4, Laravel 11, MySQL 8, Laravel Sanctum, Tailwind CSS, Alpine.js, Chart.js, Leaflet.js, PHPUnit.

**Spec:** [docs/superpowers/specs/2026-11-02-smart-waste-backend-web-admin-design.md](file:///c:/laragon/www/smart-waste/docs/superpowers/specs/2026-11-02-smart-waste-backend-web-admin-design.md)

## Global Constraints
- Database must be MySQL `smart_waste_db` running on 127.0.0.1:3306.
- Role-based authorization must strictly isolate `admin` (management, users, status updates) and `user` (citizen reporting and history).
- Reports must support status transitions (`pending`, `in_progress`, `completed`, `rejected`).
- AI integration must proxy to `http://127.0.0.1:8000/predict` and automatically classify images if category is omitted.
- REST API must return consistent JSON: `{"status": "success"|"error", "data": ...}`.
- All code must include tests verifying behavior before marking complete.

## Review Focus
- **Unauthenticated API Access**: Protected endpoints (`/api/reports`, `/api/user`, `/api/detect-waste`) return HTTP 401 Unauthorized when Bearer token is missing.
- **Unauthorized Role Escalation**: Citizen user attempting to access `/admin/*` routes or update report status is blocked with HTTP 403 Forbidden.
- **Malformed Report Submission**: Submitting a report without photo or coordinates returns HTTP 422 with validation errors.
- **ML Service Unreachable**: If FastAPI ML service is offline during report creation, report saves gracefully with `waste_category = 'Unclassified'` and logs a warning rather than crashing.
- **File Storage Symlink**: Image URLs returned by both Web and API point to accessible `/storage/reports/` public URLs via symlink.

---

### Task 1: Initialize Laravel 11 Application & Database Configuration

**Files:**
- Create: `backend/` (Laravel 11 project scaffold via composer)
- Modify: `backend/.env`
- Test: `backend/tests/Feature/EnvironmentTest.php`

**Interfaces:**
- Produces: Running Laravel 11 application in `backend/` connected to MySQL `smart_waste_db`.

- [ ] **Step 1: Create database `smart_waste_db` in MySQL**

```powershell
mysql -u root -e "CREATE DATABASE IF NOT EXISTS smart_waste_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

- [ ] **Step 2: Scaffold Laravel 11 in `backend/`**

Run: `composer create-project laravel/laravel:^11.0 backend --prefer-dist`

- [ ] **Step 3: Configure `.env` database parameters and app settings**

Configure `backend/.env`:
```ini
APP_NAME="Smart Waste Management"
APP_URL=http://localhost:8000
ML_SERVICE_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_waste_db
DB_USERNAME=root
DB_PASSWORD=
```

- [ ] **Step 4: Write failing environment test**

```php
// backend/tests/Feature/EnvironmentTest.php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    public function test_database_connection_and_environment(): void
    {
        $pdo = DB::connection()->getPdo();
        $this->assertNotNull($pdo);
        $this->assertEquals('smart_waste_db', DB::connection()->getDatabaseName());
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `cd c:\laragon\www\smart-waste\backend && php artisan test --filter=EnvironmentTest`
Expected: PASS

- [ ] **Step 6: Commit Task 1**

```bash
git add backend/
git commit -m "chore(backend): initialize Laravel 11 app and configure MySQL smart_waste_db"
```

---

### Task 2: Database Schema, Migrations, Eloquent Models & Seeders

**Files:**
- Modify: `backend/database/migrations/0001_01_01_000000_create_users_table.php`
- Create: `backend/database/migrations/2026_11_02_000001_create_reports_table.php`
- Modify: `backend/app/Models/User.php`
- Create: `backend/app/Models/Report.php`
- Modify: `backend/database/seeders/DatabaseSeeder.php`
- Test: `backend/tests/Feature/DatabaseModelTest.php`

**Interfaces:**
- Produces: `User` and `Report` models, migrations with role and report fields, realistic seeders with 8 initial reports.

- [ ] **Step 1: Write test for models and relationships**

```php
// backend/tests/Feature/DatabaseModelTest.php
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
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DatabaseModelTest`
Expected: FAIL (Report model / columns not defined)

- [ ] **Step 3: Update `users` migration, create `reports` migration, and implement Models**

In `users` table: add `phone` (string nullable), `role` (enum: 'admin', 'user' default 'user'), `is_active` (boolean default true).
In `reports` table: add all columns defined in spec Section 3.1.
In `User.php`: add relations `hasMany(Report::class)` and methods `isAdmin()`, `isCitizen()`.
In `Report.php`: fillable attributes, relations `belongsTo(User::class)`, and scopes `scopePending()`, `scopeCompleted()`, etc.

- [ ] **Step 4: Create DatabaseSeeder with realistic dummy data**

Seed:
- Admin: `admin@smartwaste.com` / `password`
- Citizen: `user@smartwaste.com` / `password`
- 8 Reports across Jakarta/Indonesia coordinates, with real waste categories and statuses.

- [ ] **Step 5: Run migration, seed, and run test to verify it passes**

Run:
```powershell
php artisan migrate:fresh --seed
php artisan test --filter=DatabaseModelTest
```
Expected: PASS

- [ ] **Step 6: Commit Task 2**

```bash
git add backend/database/ backend/app/Models/ backend/tests/Feature/DatabaseModelTest.php
git commit -m "feat(backend): add database schema, Report model, and realistic seeders"
```

---

### Task 3: Role-Based Authentication & Redirection Workflow (Web)

**Files:**
- Create: `backend/app/Http/Middleware/RoleMiddleware.php`
- Create: `backend/app/Http/Controllers/Auth/LoginController.php`
- Create: `backend/app/Http/Controllers/Auth/RegisterController.php`
- Create: `backend/resources/views/layouts/auth.blade.php`
- Create: `backend/resources/views/auth/login.blade.php`
- Create: `backend/resources/views/auth/register.blade.php`
- Test: `backend/tests/Feature/AuthTest.php`

**Interfaces:**
- Produces: Session authentication, role middleware (`role:admin`, `role:user`), login/register views, automatic role-based redirection.

- [ ] **Step 1: Write feature test for authentication and role redirection**

```php
// backend/tests/Feature/AuthTest.php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_admin_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::create([
            'name' => 'Citizen',
            'email' => 'citizen@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuthTest`
Expected: FAIL

- [ ] **Step 3: Implement RoleMiddleware, Controllers, and Blade Views**

- Register `role` middleware alias in `bootstrap/app.php`.
- Build sleek `layouts/auth.blade.php` and `views/auth/login.blade.php` with environmental aesthetics (emerald/teal gradients, modern typography, responsive cards).
- Implement login and registration with validation and role redirection.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AuthTest`
Expected: PASS

- [ ] **Step 5: Commit Task 3**

```bash
git add backend/app/Http/Controllers/Auth backend/app/Http/Middleware backend/resources/views/auth backend/resources/views/layouts/auth.blade.php backend/tests/Feature/AuthTest.php backend/routes/web.php backend/bootstrap/app.php
git commit -m "feat(backend): implement role-based authentication and redirection"
```

---

### Task 4: Admin Dashboard, Statistics, Chart.js & Interactive Leaflet Map

**Files:**
- Create: `backend/app/Http/Controllers/Admin/DashboardController.php`
- Create: `backend/resources/views/layouts/app.blade.php`
- Create: `backend/resources/views/admin/dashboard.blade.php`
- Test: `backend/tests/Feature/AdminDashboardTest.php`

**Interfaces:**
- Produces: Visual stats (metric cards, donut chart of waste categories, trend line chart, Leaflet GPS interactive map with report markers).

- [ ] **Step 1: Write test for Admin Dashboard data and access**

```php
// backend/tests/Feature/AdminDashboardTest.php
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
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AdminDashboardTest`
Expected: FAIL

- [ ] **Step 3: Implement DashboardController and Dashboard Blade View**

- Controller aggregates counts for `total`, `pending`, `in_progress`, `completed`, `rejected`, breakdown of categories (`Organic Waste`, `Plastic Waste`, `Paper Waste`), and monthly chart data.
- Layout `layouts/app.blade.php` includes responsive navbar, sidebar, Tailwind CDN, Chart.js CDN, Leaflet.js CSS/JS CDN.
- Dashboard view renders metric cards, Chart.js canvas for category distribution & monthly trend, and a Leaflet map container plotting all geolocated reports with colored markers and popups.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AdminDashboardTest`
Expected: PASS

- [ ] **Step 5: Commit Task 4**

```bash
git add backend/app/Http/Controllers/Admin/DashboardController.php backend/resources/views/admin/dashboard.blade.php backend/resources/views/layouts/app.blade.php backend/tests/Feature/AdminDashboardTest.php
git commit -m "feat(backend): implement admin dashboard with Chart.js analytics and Leaflet GIS map"
```

---

### Task 5: Waste Reports Management (CRUD, Filter, Search & Status Update Workflow)

**Files:**
- Create: `backend/app/Http/Controllers/Admin/ReportController.php`
- Create: `backend/app/Http/Controllers/User/UserReportController.php`
- Create: `backend/resources/views/admin/reports/index.blade.php`
- Create: `backend/resources/views/admin/reports/show.blade.php`
- Create: `backend/resources/views/user/dashboard.blade.php`
- Create: `backend/resources/views/user/reports/create.blade.php`
- Test: `backend/tests/Feature/ReportManagementTest.php`

**Interfaces:**
- Produces: CRUD report management for admin and citizens, live keyword filter, status update modal with admin notes.

- [ ] **Step 1: Write test for report search, filtering, and status update workflow**

```php
// backend/tests/Feature/ReportManagementTest.php
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

        // Search filter
        $response = $this->actingAs($admin)->get('/admin/reports?search=Ciliwung');
        $response->assertStatus(200);
        $response->assertSee('Plastik Kali Ciliwung');

        // Status update
        $updateResp = $this->actingAs($admin)->put("/admin/reports/{$report->id}/status", [
            'status' => 'in_progress',
            'admin_notes' => 'Petugas kebersihan telah dikirim.'
        ]);
        $updateResp->assertRedirect();
        
        $this->assertEquals('in_progress', $report->fresh()->status);
        $this->assertEquals('Petugas kebersihan telah dikirim.', $report->fresh()->admin_notes);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReportManagementTest`
Expected: FAIL

- [ ] **Step 3: Implement ReportController, UserReportController, and Blade Views**

- `Admin/ReportController`:
  - `index()`: Paginated list with search and filters by status and category.
  - `show($id)`: High-res photo preview, reporter information, GPS Leaflet map pin, AI confidence badge, and status timeline.
  - `updateStatus($request, $id)`: Validates transition to `in_progress`, `completed`, or `rejected`, sets `admin_notes`, and updates `resolved_at` when completed.
- `User/UserReportController`:
  - `index()`: Citizen's own report history.
  - `create()` & `store()`: Web submission form with drag-and-drop image upload, geolocation picker, and auto AI categorization.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ReportManagementTest`
Expected: PASS

- [ ] **Step 5: Commit Task 5**

```bash
git add backend/app/Http/Controllers/Admin/ReportController.php backend/app/Http/Controllers/User/ backend/resources/views/admin/reports/ backend/resources/views/user/ backend/tests/Feature/ReportManagementTest.php
git commit -m "feat(backend): implement reports CRUD, multi-criteria filtering, and status transition workflow"
```

---

### Task 6: User Management (Admin Portal)

**Files:**
- Create: `backend/app/Http/Controllers/Admin/UserController.php`
- Create: `backend/resources/views/admin/users/index.blade.php`
- Test: `backend/tests/Feature/UserManagementTest.php`

**Interfaces:**
- Produces: User listing, role toggle (`admin` $\leftrightarrow$ `user`), active/inactive toggle.

- [ ] **Step 1: Write test for user listing and role updates**

```php
// backend/tests/Feature/UserManagementTest.php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=UserManagementTest`
Expected: FAIL

- [ ] **Step 3: Implement UserController and views**

- `index()`: Paginated list of users with search, role badges, report count, and action buttons.
- `updateRole()` & `toggleActive()`: Actions to adjust role and account status.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=UserManagementTest`
Expected: PASS

- [ ] **Step 5: Commit Task 6**

```bash
git add backend/app/Http/Controllers/Admin/UserController.php backend/resources/views/admin/users/ backend/tests/Feature/UserManagementTest.php
git commit -m "feat(backend): implement user management and role assignment"
```

---

### Task 7: REST API for Mobile App & ML Service Dual Integration

**Files:**
- Create: `backend/app/Services/WasteAiService.php`
- Create: `backend/app/Http/Controllers/Api/AuthApiController.php`
- Create: `backend/app/Http/Controllers/Api/ReportApiController.php`
- Create: `backend/app/Http/Controllers/Api/AiDetectionApiController.php`
- Create: `backend/app/Http/Controllers/Api/DashboardApiController.php`
- Modify: `backend/routes/api.php`
- Test: `backend/tests/Feature/ReportApiTest.php`

**Interfaces:**
- Produces: Complete REST API for Flutter:
  - `POST /api/register`
  - `POST /api/login`
  - `GET /api/user`
  - `POST /api/logout`
  - `GET /api/reports`
  - `POST /api/reports` (with automatic AI detection fallback)
  - `GET /api/reports/{id}`
  - `POST /api/detect-waste` (ML proxy)
  - `GET /api/dashboard/stats`

- [ ] **Step 1: Write comprehensive API feature test**

```php
// backend/tests/Feature/ReportApiTest.php
<?php

namespace Tests\Feature;

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
            'http://127.0.0.1:8000/predict' => Http::response([
                'status' => 'success',
                'data' => [
                    'label' => 'Plastic Waste',
                    'category' => 'Anorganik (Plastik)',
                    'confidence' => 0.9854
                ]
            ], 200),
        ]);

        // 1. Register API
        $regResp = $this->postJson('/api/register', [
            'name' => 'Mobile User',
            'email' => 'mobile@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '08123456789'
        ]);
        $regResp->assertStatus(201);
        $token = $regResp->json('data.token');

        // 2. Submit Report with photo & GPS
        $file = UploadedFile::fake()->image('waste.jpg');
        $submitResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/reports', [
                'title' => 'Sampah Botol Plastik',
                'description' => 'Ditemukan di dekat taman',
                'image' => $file,
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address_note' => 'Taman Suropati'
            ]);

        $submitResp->assertStatus(201);
        $this->assertEquals('Plastic Waste', $submitResp->json('data.waste_category'));
        $this->assertEquals(0.9854, $submitResp->json('data.ai_confidence'));

        // 3. AI Proxy Endpoint
        $detectResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/detect-waste', [
                'image' => UploadedFile::fake()->image('detect.jpg')
            ]);
        $detectResp->assertStatus(200);
        $this->assertEquals('Plastic Waste', $detectResp->json('data.label'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReportApiTest`
Expected: FAIL

- [ ] **Step 3: Implement WasteAiService and API Controllers**

- `WasteAiService`: Sends file multipart request to `config('services.ml.url', 'http://127.0.0.1:8000') . '/predict'`. Gracefully handles service timeout or offline states with default fallback.
- `AuthApiController`: Implements registration, login, logout, and user profile using Sanctum plainTextToken.
- `ReportApiController`: Handles report listing, submission with file storage in `public/reports`, automatic AI categorization, and report detail.
- `AiDetectionApiController`: Proxy endpoint for standalone AI detection.
- `DashboardApiController`: Returns compact stats for mobile home screen.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ReportApiTest`
Expected: PASS

- [ ] **Step 5: Setup storage link and run full backend test suite**

Run:
```powershell
php artisan storage:link
php artisan test
```
Expected: ALL TESTS PASS

- [ ] **Step 6: Commit Task 7**

```bash
git add backend/app/Services/ backend/app/Http/Controllers/Api/ backend/routes/api.php backend/tests/Feature/ReportApiTest.php
git commit -m "feat(backend): implement REST API for mobile app and dual ML service integration"
```
