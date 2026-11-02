# Smart Waste Mobile Application Design Specification

- **Project**: Smart Waste Management System — Component B: Mobile Application
- **Author**: Antigravity & User
- **Date**: 2026-11-02
- **Status**: Draft / Awaiting Review
- **Subsystem**: `mobile/` (Flutter Application)

---

## 1. Executive Summary & Goals

The Smart Waste Mobile Application provides citizens with an intuitive, responsive, and intelligent tool to actively participate in urban waste management. The mobile app interfaces seamlessly with the Laravel 11 Backend API and the FastAPI ML Microservice to deliver:

1. **Secure Citizen Authentication**: Registration and Login using Laravel Sanctum API tokens persisted in encrypted local storage.
2. **Interactive Citizen Dashboard**: Metric overview of personal reports (Total, Pending, In Progress, Selesai, Ditolak), quick actions, and recent activity.
3. **Smart Waste Reporting Workflow**:
   - High-resolution photo capture via camera or gallery selection.
   - Real-time GPS coordinate acquisition via device geolocation with interactive OpenStreetMap verification.
   - Automatic waste type classification (Organic Waste, Plastic Waste, Paper Waste) powered by the MobileNetV2 ML service with confidence scoring.
   - Custom report title, description, and manual address landmark notes.
4. **Report Tracking & History**:
   - Status tracking with badge indicators (`Menunggu`, `Sedang Diproses`, `Selesai`, `Ditolak`).
   - Filterable list with pull-to-refresh.
   - Detailed inspection view showing full-resolution photo, admin notes, GPS location pin, and status timeline.
5. **Standalone AI Waste Scanner**:
   - Camera/gallery scanner for on-the-spot waste identification before or independently of filing an official report.

---

## 2. Technology Stack & Key Dependencies

| Component | Choice | Rationale |
|---|---|---|
| **Framework** | Flutter 3.47+ / Dart 3.13+ | Cross-platform (Android, iOS, Web, Desktop) with high performance 60/120fps UI. |
| **State Management** | `provider` (~6.1.0) | Standard Flutter state management, highly testable, predictable, minimal boilerplate. |
| **HTTP Client** | `http` (~1.2.0) | Lightweight, dependable HTTP client with multipart file upload support. |
| **Local Storage** | `shared_preferences` (~2.3.0) | Persistent local storage for Sanctum Bearer tokens and cached user profiles. |
| **Camera & Gallery** | `image_picker` (~1.1.0) | Robust multi-platform photo capture from camera or album selection. |
| **GPS Geolocation** | `geolocator` (~12.0.0) | Accurate device location queries with graceful permission request handling. |
| **Map Rendering** | `flutter_map` (~7.0.0) + `latlong2` (~0.9.0) | OpenStreetMap tile renderer, zero API key requirements, free and privacy-respecting. |
| **UI Polish** | `google_fonts`, `intl`, `flutter_svg` | Typography, date formatting (Indonesian locale), and vector iconography. |

---

## 3. Architecture & Directory Structure

The mobile app follows Clean Architecture principles with separation of concerns:

```
mobile/
├── android/
├── ios/
├── lib/
│   ├── main.dart                          # App entry point, MultiProvider registration, theme setup
│   ├── config/
│   │   ├── app_constants.dart             # API base URLs, colors, asset paths
│   │   └── app_theme.dart                 # Emerald eco-theme, typography, button styles
│   ├── models/
│   │   ├── user_model.dart                # User entity & JSON serialization
│   │   ├── report_model.dart              # Waste report entity with status helpers
│   │   └── ai_detection_model.dart        # AI inference result & probabilities
│   ├── providers/
│   │   ├── auth_provider.dart             # Login, register, logout, token persistence
│   │   ├── report_provider.dart           # Fetching, filtering, and submitting reports
│   │   └── ai_provider.dart               # Standalone AI classification state
│   ├── services/
│   │   ├── api_service.dart               # HTTP requests, Bearer token injection, error handling
│   │   ├── auth_service.dart              # /api/login, /api/register, /api/logout, /api/user
│   │   ├── report_service.dart            # /api/reports, /api/reports/{id}, multipart submission
│   │   ├── ai_service.dart                # /api/detect-waste proxy call
│   │   └── location_service.dart          # Geolocator permission handling & coordinate fetching
│   ├── views/
│   │   ├── auth/
│   │   │   ├── login_screen.dart          # Citizen login with quick demo credentials
│   │   │   └── register_screen.dart       # Account registration form
│   │   ├── dashboard/
│   │   │   ├── home_screen.dart           # Stats cards, quick actions, recent reports
│   │   │   └── main_navigation_screen.dart# Bottom navigation bar (Home, Scan, Riwayat, Profil)
│   │   ├── reports/
│   │   │   ├── create_report_screen.dart  # Camera, GPS map picker, AI detection, submission
│   │   │   ├── report_list_screen.dart    # Filtered list of reports with pull-to-refresh
│   │   │   └── report_detail_screen.dart  # Detailed report view with Leaflet/OSM map & timeline
│   │   ├── scanner/
│   │   │   └── ai_scanner_screen.dart     # Standalone AI waste classifier preview
│   │   └── profile/
│   │       └── profile_screen.dart        # User account info, stats summary, logout
│   └── widgets/
│       ├── status_badge.dart              # Status chip with color coding
│       ├── report_card.dart               # Compact report card with thumbnail & status
│       ├── stat_card.dart                 # Dashboard metric counter card
│       ├── map_preview.dart               # Mini OpenStreetMap widget with marker pin
│       └── loading_overlay.dart           # Polished async loading spinner
├── test/
│   ├── models_test.dart                   # Unit tests for JSON serialization
│   └── widget_test.dart                   # Smoke test for app initialization
└── pubspec.yaml                           # Flutter metadata and dependencies
```

---

## 4. Screen Flows & Detailed Specifications

### 4.1. Authentication Flow
- **Login Screen**:
  - Input: Email & Password.
  - Buttons: "Masuk", "Daftar Akun Baru".
  - Quick Login helper buttons (fills Citizen demo `user@smartwaste.com` / `password` for instant evaluation).
  - Validation: Email format, password length $\ge 6$.
  - Behavior: Stores Sanctum Bearer token in `SharedPreferences`, loads user profile, navigates to `MainNavigationScreen`.
- **Register Screen**:
  - Input: Full Name, Email, Phone Number, Password, Password Confirmation.
  - Behavior: Calls `POST /api/register`, automatically logs the user in upon success.

### 4.2. Main Navigation & Dashboard
- **Main Navigation**: Bottom Navigation Bar with 4 tabs:
  1. `Beranda` (Home icon)
  2. `Scan AI` (Auto-awesome / Camera icon)
  3. `Laporan Saya` (Clipboard list icon)
  4. `Profil` (Person icon)
- **Home Dashboard Screen**:
  - Header: Citizen greeting (`Halo, Dani`), date, avatar.
  - Summary Metric Cards:
    - Total Laporan
    - Menunggu (Pending - Amber)
    - Diproses (In Progress - Blue)
    - Selesai (Completed - Green)
  - Floating / Hero Quick Action: "Buat Laporan Baru" button with camera icon.
  - Section: "Laporan Terbaru" (up to 5 recent submissions with status chips and click-to-view detail).

### 4.3. Waste Report Submission Flow (`CreateReportScreen`)
- **Step 1: Photo Capture**:
  - Options: "Buka Kamera" or "Pilih dari Galeri".
  - Displays selected image preview.
  - **Auto-AI Detection Trigger**: As soon as an image is selected, the app calls `POST /api/detect-waste` in the background.
  - Renders AI Detection Result Card:
    - Identified Label: `Plastic Waste` / `Organic Waste` / `Paper Waste`.
    - Indonesian Category: `Anorganik (Plastik)` / `Organik (Sisa Makanan/Daun)` / `Daur Ulang (Kertas/Karton)`.
    - Confidence score bar (e.g. `98.5% Yakin`).
    - Option for user to override category if desired.
- **Step 2: Geolocation**:
  - Automatically fetches current device GPS coordinates via `Geolocator`.
  - Shows latitude and longitude coordinates.
  - Displays live mini OpenStreetMap centered at current location with a red pin.
  - Text input for landmark / address notes (e.g., "Depan pos satpam Blok C").
- **Step 3: Details & Submission**:
  - Title (e.g., "Tumpukan sampah plastik di selokan").
  - Description.
  - Submit Button: Sends multipart `POST /api/reports`.
  - Displays success dialog with animation and redirects to `ReportDetailScreen` or `ReportListScreen`.

### 4.4. Standalone AI Scanner Screen (`AiScannerScreen`)
- Allows users to test the AI model without submitting a report.
- Pick photo $\to$ View classification breakdown with animated progress bars for all 3 categories (Organic, Plastic, Paper).
- Action Button: "Buat Laporan dari Foto Ini" $\to$ forwards photo and detected label directly into `CreateReportScreen`.

### 4.5. Report List & Detail Screen
- **Report List**:
  - Filter chips: `Semua`, `Menunggu`, `Diproses`, `Selesai`, `Ditolak`.
  - Pull-to-refresh (`RefreshIndicator`).
  - Cards show photo thumbnail, title, category badge, date, and status badge.
- **Report Detail**:
  - Full-width image preview.
  - Status Timeline:
    - `Menunggu Verifikasi` $\to$ `Sedang Ditangani` $\to$ `Selesai` (or `Ditolak`).
  - AI Classification result and confidence score.
  - Full GPS Map with interactive zoom and pan.
  - Admin notes section (if admin provided resolution feedback or rejection reason).

---

## 5. API Integration Mapping

| Mobile Screen / Action | Backend REST Endpoint | Method | Payload / Headers |
|---|---|---|---|
| Register Account | `/api/register` | `POST` | `name`, `email`, `password`, `password_confirmation`, `phone` |
| Citizen Login | `/api/login` | `POST` | `email`, `password` |
| Citizen Profile | `/api/user` | `GET` | Header: `Authorization: Bearer <token>` |
| Logout | `/api/logout` | `POST` | Header: `Authorization: Bearer <token>` |
| Dashboard Stats | `/api/dashboard/stats` | `GET` | Header: `Authorization: Bearer <token>` |
| List Reports | `/api/reports` | `GET` | Query: `?status=...&category=...` |
| Create Report | `/api/reports` | `POST` (Multipart) | `title`, `description`, `image`, `latitude`, `longitude`, `address_note`, `waste_category` |
| Report Detail | `/api/reports/{id}` | `GET` | Header: `Authorization: Bearer <token>` |
| Real-time AI Scan | `/api/detect-waste` | `POST` (Multipart) | `image` |

---

## 6. Verification & Testing Strategy

1. **Unit & Model Tests**:
   - `models_test.dart`: Serialization and deserialization of `UserModel`, `ReportModel`, and `AiDetectionModel`.
2. **Widget Tests**:
   - `widget_test.dart`: Test widget tree rendering and navigation smoke tests.
3. **End-to-End Build & Compilation Verification**:
   - Verify zero compile errors via `flutter analyze`.
   - Verify debug build readiness via `flutter build apk --debug` or `flutter build web` / `flutter test`.

---

## 7. Approval Gates

- [ ] **Section 1**: Architectural Scope & Dependencies (`provider`, `flutter_map`, `image_picker`, `geolocator`, `http`).
- [ ] **Section 2**: UI/UX Screens & User Flows (Auth, Dashboard, Report Creation with AI & GPS, History, Scanner).
- [ ] **Section 3**: API Mapping & Verification Strategy.
