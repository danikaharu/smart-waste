# Design Spec: Smart Waste Management - Sub-project 2: Backend API & Web Admin Dashboard

- **Date:** 2026-11-02
- **Status:** Approved by User
- **Scope:** Sub-project 2 of Smart Waste Management System (Laravel 11 Backend REST API, Web Admin & User Dashboard, MySQL Database)

---

## 1. Context & Subsystem Role

Sub-project 2 is the central backend hub of the Smart Waste Management System, located in `backend/`. It fulfills two essential roles:
1. **Web Admin & Citizen Dashboard**: A responsive, interactive web application built with Laravel 11 Blade, Tailwind CSS, Alpine.js, Chart.js, and Leaflet.js.
2. **RESTful API Engine**: A secure API powered by Laravel Sanctum for the Flutter Mobile Application (Sub-project 3), offering authentication, report submissions with GPS coordinates and images, and dual integration with the FastAPI Machine Learning Service (Sub-project 1).

---

## 2. Requirements & Success Criteria

### 2.1. Web Dashboard Requirements
- **Role-Based Authentication**:
  - `Admin`: Full access to management dashboard, user management, status updates, and system-wide statistics.
  - `User` (Citizen): Access to personal report history, submission of new waste reports via web, and profile settings.
- **Statistics Dashboard (Admin)**:
  - Metric Summary Cards: Total Reports, Pending, In Progress, Completed, Rejected.
  - Interactive Distribution Charts: Donut chart of waste categories (`Organic Waste`, `Plastic Waste`, `Paper Waste`) powered by Chart.js.
  - Trend Analysis: Monthly/weekly report volume trend line chart.
  - Interactive GIS Map: Leaflet.js map with custom color markers representing report GPS locations and clickable popups with thumbnails and statuses.
- **Waste Reports Management (CRUD)**:
  - Paginated data table with live keyword search.
  - Multi-criteria filters: Filter by status (`pending`, `in_progress`, `completed`, `rejected`), category (`Organic Waste`, `Plastic Waste`, `Paper Waste`), and date range.
  - Detailed Report View: High-resolution image preview, reporter details, GPS coordinates, interactive pin on Leaflet map, AI detection confidence badge, and chronological status history.
  - Status Update Workflow: Action modal to transition statuses (`pending` $\to$ `in_progress` $\to$ `completed` / `rejected`) requiring admin follow-up notes.
- **User Management**:
  - List of registered users with role badges, report counters, and account status toggles (`active` / `inactive`).
- **Responsive & Premium UI**:
  - Fully responsive layout (desktop, tablet, mobile) using Tailwind CSS with a clean, modern green/emerald environmental aesthetic.

### 2.2. REST API Requirements (for Flutter Mobile App)
- **Authentication (Sanctum Tokens)**:
  - `POST /api/register`: Name, email, password, phone $\to$ Bearer token + User profile.
  - `POST /api/login`: Email, password $\to$ Bearer token + User profile + Role.
  - `POST /api/logout`: Revokes current token.
  - `GET /api/user`: Returns authenticated user profile.
- **Report Management**:
  - `GET /api/reports`: Paginated list of reports (filterable by status and user).
  - `POST /api/reports`: Accepts multipart image upload, title, description, latitude, longitude, and optional pre-detected category. If category is not supplied, automatically triggers AI detection via ML Service.
  - `GET /api/reports/{id}`: Detailed information of a specific report.
- **AI Detection Integration**:
  - `POST /api/detect-waste`: Proxies image upload to FastAPI ML Service (`http://127.0.0.1:8000/predict`), returning predicted waste category, confidence score, and Indonesian label.
- **Dashboard Stats API**:
  - `GET /api/dashboard/stats`: Summary counts for mobile home screen.

---

## 3. Database Schema (MySQL: `smart_waste_db`)

### 3.1. Tables & Columns

#### Table: `users`
| Column | Type | Attributes / Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Unique user identifier |
| `name` | VARCHAR(255) | NOT NULL | User's full name |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Authentication email |
| `password` | VARCHAR(255) | NOT NULL | Hashed password (Bcrypt) |
| `phone` | VARCHAR(30) | NULLABLE | Contact telephone / WhatsApp |
| `role` | ENUM('admin', 'user') | DEFAULT 'user', NOT NULL | Role authorization |
| `is_active` | BOOLEAN | DEFAULT TRUE, NOT NULL | Account active status |
| `remember_token`| VARCHAR(100) | NULLABLE | Web session remember token |
| `created_at` | TIMESTAMP | NULLABLE | Creation timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Modification timestamp |

#### Table: `reports`
| Column | Type | Attributes / Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Unique report ID |
| `user_id` | BIGINT UNSIGNED | FK $\to$ `users.id`, CASCADE | Reporter user reference |
| `title` | VARCHAR(255) | NOT NULL | Short report title |
| `description` | TEXT | NOT NULL | Detailed problem description |
| `image_path` | VARCHAR(255) | NOT NULL | Path in `storage/app/public/reports/` |
| `latitude` | DECIMAL(10, 8) | NOT NULL | GPS Latitude |
| `longitude` | DECIMAL(11, 8) | NOT NULL | GPS Longitude |
| `address_note` | VARCHAR(255) | NULLABLE | Human-readable address / landmark |
| `waste_category`| ENUM(...) | DEFAULT 'Unclassified' | 'Organic Waste', 'Plastic Waste', 'Paper Waste', 'Unclassified' |
| `ai_confidence` | DECIMAL(5, 4) | NULLABLE | Confidence score from ML (e.g. 0.9556) |
| `status` | ENUM(...) | DEFAULT 'pending' | 'pending', 'in_progress', 'completed', 'rejected' |
| `admin_notes` | TEXT | NULLABLE | Notes from admin on resolution or rejection |
| `resolved_at` | TIMESTAMP | NULLABLE | Timestamp when status was set to completed |
| `created_at` | TIMESTAMP | NULLABLE | Creation timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Modification timestamp |

#### Table: `personal_access_tokens`
- Standard Laravel Sanctum token storage table for API bearer authentication.

### 3.2. Initial Seeders
- **Admin**: `admin@smartwaste.com` / `password` (Role: `admin`)
- **User**: `user@smartwaste.com` / `password` (Role: `user`)
- **Initial Reports**: 6–8 realistic sample waste reports spread across classes (`Organic Waste`, `Plastic Waste`, `Paper Waste`) and statuses (`pending`, `in_progress`, `completed`), pinned to realistic coordinates with sample images.

---

## 4. API Specification & Payloads

### 4.1. Authentication
- **`POST /api/login`**
  - Request: `{"email": "user@smartwaste.com", "password": "password"}`
  - Response (`200 OK`):
    ```json
    {
      "status": "success",
      "message": "Login successful",
      "data": {
        "token": "1|sanctum_token_string...",
        "user": {
          "id": 2,
          "name": "Citizen User",
          "email": "user@smartwaste.com",
          "role": "user"
        }
      }
    }
    ```

### 4.2. Waste Report Submission with AI Detection
- **`POST /api/reports`**
  - Content-Type: `multipart/form-data`
  - Body:
    - `title`: "Tumpukan Sampah Plastik di Pinggir Jalan"
    - `description`: "Ditemukan botol dan gelas plastik berserakan."
    - `image`: [binary image file]
    - `latitude`: -6.2088
    - `longitude`: 106.8456
    - `address_note`: "Jl. Sudirman No. 12"
  - Processing:
    1. Validates input and stores image in `storage/app/public/reports/`.
    2. Sends image to ML service (`http://127.0.0.1:8000/predict`).
    3. Populates `waste_category` (e.g. `Plastic Waste`) and `ai_confidence` (e.g. `0.9854`).
    4. Saves report in MySQL with default status `pending`.
  - Response (`201 Created`):
    ```json
    {
      "status": "success",
      "message": "Report submitted successfully",
      "data": {
        "id": 1,
        "title": "Tumpukan Sampah Plastik di Pinggir Jalan",
        "waste_category": "Plastic Waste",
        "ai_confidence": 0.9854,
        "status": "pending",
        "image_url": "http://127.0.0.1:8000/storage/reports/filename.jpg",
        "latitude": -6.2088,
        "longitude": 106.8456,
        "created_at": "2026-11-02T13:00:00Z"
      }
    }
    ```

---

## 5. Web Interface Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── Admin/DashboardController.php
│   │   │   ├── Admin/ReportController.php
│   │   │   ├── Admin/UserController.php
│   │   │   ├── User/UserReportController.php
│   │   │   └── Api/
│   │   │       ├── AuthApiController.php
│   │   │       ├── ReportApiController.php
│   │   │       └── AiDetectionApiController.php
│   │   ├── Middleware/RoleMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Report.php
│   └── Services/
│       └── WasteAiService.php       # Handles communication with ML FastAPI
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php        # Main responsive layout with navbar & sidebar
│       │   └── auth.blade.php       # Clean authentication layout
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php  # Statistics, Chart.js, Leaflet map
│       │   ├── reports/
│       │   │   ├── index.blade.php  # Filterable CRUD table
│       │   │   └── show.blade.php   # Detail, GPS map pin, status update modal
│       │   └── users/
│       │       └── index.blade.php  # User management
│       └── user/
│           ├── dashboard.blade.php  # Citizen portal
│           └── reports/create.blade.php # Web report form
├── routes/
│   ├── web.php                      # Web dashboard routes
│   └── api.php                      # Sanctum REST API routes
```

---

## 6. Testing & Quality Gates
- **Database Migrations & Seeders**: Execute `php artisan migrate:fresh --seed` without errors.
- **Automated Feature Tests**:
  - `AuthTest`: Tests login, logout, role redirection (admin to `/admin/dashboard`, user to `/user/dashboard`).
  - `ReportManagementTest`: Tests CRUD, search filter, status update authorization.
  - `ReportApiTest`: Tests API registration, login, report submission with multipart image, and AI detection.
- **UI Responsiveness & Accessibility**:
  - Verified on desktop, tablet, and mobile breakpoints with working Leaflet GPS maps and Chart.js visuals.
