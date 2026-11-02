# Smart Waste Mobile Application (Flutter) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a production-ready, beautiful, cross-platform Flutter mobile application (`mobile/`) for the Smart Waste Management System with role-based citizen authentication, interactive statistics dashboard, waste report submission with camera capture, GPS OpenStreetMap integration, automated AI classification (MobileNetV2), status tracking history, and standalone AI scanner.

**Architecture:** Clean Architecture with separation into Models, Services (HTTP with Sanctum Bearer tokens & Geolocator), State Management Providers (`provider`), and reactive UI Views. Reusable atomic UI widgets with an Emerald eco-system theme (`#059669` / `#10b981`), `flutter_map` OpenStreetMap pins, and resilient offline/error handling.

**Tech Stack:** Flutter 3.47+ / Dart 3.13+, Provider, HTTP, Shared Preferences, Image Picker, Geolocator, Flutter Map, LatLong2, Google Fonts, Intl.

**Spec:** [`docs/superpowers/specs/2026-11-02-smart-waste-mobile-app-design.md`](file:///c:/laragon/www/smart-waste/docs/superpowers/specs/2026-11-02-smart-waste-mobile-app-design.md)

## Global Constraints
- Target directory: `mobile/` in project root monorepo.
- Minimum SDK: Flutter 3.47 / Dart 3.13.
- No paid Google Maps API keys required: Use `flutter_map` (OpenStreetMap) + `latlong2`.
- Default API Host: `http://10.0.2.2:8000/api` for Android Emulator, `http://localhost:8000/api` for Desktop/Web/iOS, with dynamic override in constants.
- Strict TDD: Every task writes and verifies unit/widget tests before committing.

## Review Focus
1. **API Host Connectivity & Device Fallback**: Localhost vs Android emulator `10.0.2.2` vs LAN IP handled seamlessly.
2. **Missing GPS Permission / Location Disabled**: Graceful fallback coordinates (Jakarta center `-6.2088, 106.8456`) and clear user prompt if permission is denied.
3. **ML Service Timeout or Unreachable**: Graceful fallback to `Unclassified` with user ability to select waste category manually.
4. **Token Expiration / Invalidation**: 401 response auto-clears saved preferences and routes back to login.
5. **Photo Compression / Large Images**: Handled without UI stuttering or payload rejection.

---

### Task 1: Flutter Project Scaffolding & Configuration

**Files:**
- Create: `mobile/pubspec.yaml`
- Create: `mobile/lib/config/app_constants.dart`
- Create: `mobile/lib/config/app_theme.dart`
- Create: `mobile/lib/main.dart`
- Test: `mobile/test/scaffold_test.dart`

**Interfaces:**
- Produces: `AppConstants` (Base URLs, route names, colors, categories), `AppTheme` (Emerald eco palette, card themes, input decorations).

- [ ] **Step 1: Scaffold Flutter project inside `mobile/`**

Run:
```powershell
flutter create --org com.smartwaste --project-name smart_waste_mobile mobile
```

- [ ] **Step 2: Update `mobile/pubspec.yaml` with required dependencies**

Add to `mobile/pubspec.yaml`:
```yaml
dependencies:
  flutter:
    sdk: flutter
  provider: ^6.1.2
  http: ^1.2.2
  shared_preferences: ^2.3.2
  image_picker: ^1.1.2
  geolocator: ^12.0.0
  flutter_map: ^7.0.2
  latlong2: ^0.9.1
  google_fonts: ^6.2.1
  intl: ^0.19.0

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^4.0.0
  http: ^1.2.2
```

- [ ] **Step 3: Write test for project configuration & theme**

Create `mobile/test/scaffold_test.dart`:
```dart
import 'package:flutter_test/flutter_test.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';

void main() {
  test('AppConstants contains valid API endpoints and categories', () {
    expect(AppConstants.appName, equals('Smart Waste'));
    expect(AppConstants.categories, contains('Organic Waste'));
    expect(AppConstants.categories, contains('Plastic Waste'));
    expect(AppConstants.categories, contains('Paper Waste'));
  });

  test('AppTheme defines primary eco green color', () {
    expect(AppTheme.primaryColor.value, isNotNull);
  });
}
```

- [ ] **Step 4: Run test to verify it fails**

Run: `flutter test test/scaffold_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 5: Implement `app_constants.dart` and `app_theme.dart`**

Implement `mobile/lib/config/app_constants.dart` and `mobile/lib/config/app_theme.dart`.

- [ ] **Step 6: Run test to verify it passes**

Run: `flutter test test/scaffold_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 7: Commit Task 1**

```bash
git add mobile/
git commit -m "chore(mobile): scaffold Flutter project with eco-theme and core dependencies"
```

---

### Task 2: Data Models & Serialization

**Files:**
- Create: `mobile/lib/models/user_model.dart`
- Create: `mobile/lib/models/report_model.dart`
- Create: `mobile/lib/models/ai_detection_model.dart`
- Test: `mobile/test/models_test.dart`

**Interfaces:**
- Produces: `UserModel.fromJson()`, `ReportModel.fromJson()`, `AiDetectionModel.fromJson()`, status display helpers (`statusLabel`, `statusColor`).

- [ ] **Step 1: Write test for model parsing and serialization**

Create `mobile/test/models_test.dart` testing `UserModel`, `ReportModel`, and `AiDetectionModel` from/to JSON.

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/models_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 3: Implement `UserModel`, `ReportModel`, and `AiDetectionModel`**

Implement the three models with robust null-safety, Indonesian helper labels, and computed getters.

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/models_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 5: Commit Task 2**

```bash
git add mobile/lib/models/ mobile/test/models_test.dart
git commit -m "feat(mobile): implement User, Report, and AiDetection data models with JSON serialization"
```

---

### Task 3: Services Layer (API, Auth, Reports, AI, Location)

**Files:**
- Create: `mobile/lib/services/api_service.dart`
- Create: `mobile/lib/services/auth_service.dart`
- Create: `mobile/lib/services/report_service.dart`
- Create: `mobile/lib/services/ai_service.dart`
- Create: `mobile/lib/services/location_service.dart`
- Test: `mobile/test/services_test.dart`

**Interfaces:**
- Consumes: Models from Task 2, `http`, `shared_preferences`, `geolocator`.
- Produces:
  - `AuthService.login(email, password)`
  - `AuthService.register(name, email, password, phone)`
  - `AuthService.logout()`
  - `ReportService.getReports({status, category})`
  - `ReportService.getDashboardStats()`
  - `ReportService.submitReport(...)` (multipart)
  - `AiService.detectWaste(imageBytes or filePath)`
  - `LocationService.getCurrentCoordinates()`

- [ ] **Step 1: Write test for services with Mock HTTP client**

Create `mobile/test/services_test.dart`.

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/services_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 3: Implement Services**

Implement `api_service.dart`, `auth_service.dart`, `report_service.dart`, `ai_service.dart`, and `location_service.dart`.

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/services_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 5: Commit Task 3**

```bash
git add mobile/lib/services/ mobile/test/services_test.dart
git commit -m "feat(mobile): implement network services with Bearer token injection and multipart uploads"
```

---

### Task 4: State Management Providers

**Files:**
- Create: `mobile/lib/providers/auth_provider.dart`
- Create: `mobile/lib/providers/report_provider.dart`
- Create: `mobile/lib/providers/ai_provider.dart`
- Test: `mobile/test/providers_test.dart`

**Interfaces:**
- Consumes: Services from Task 3.
- Produces: `ChangeNotifier` state objects providing reactive UI bindings and loading states.

- [ ] **Step 1: Write test for providers**

Create `mobile/test/providers_test.dart`.

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/providers_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 3: Implement Providers**

Implement `AuthProvider`, `ReportProvider`, and `AiProvider`.

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/providers_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 5: Commit Task 4**

```bash
git add mobile/lib/providers/ mobile/test/providers_test.dart
git commit -m "feat(mobile): implement Provider state management for auth, reports, and AI classification"
```

---

### Task 5: UI Components & Authentication Screens

**Files:**
- Create: `mobile/lib/widgets/stat_card.dart`
- Create: `mobile/lib/widgets/status_badge.dart`
- Create: `mobile/lib/widgets/report_card.dart`
- Create: `mobile/lib/widgets/custom_button.dart`
- Create: `mobile/lib/widgets/custom_text_field.dart`
- Create: `mobile/lib/views/auth/login_screen.dart`
- Create: `mobile/lib/views/auth/register_screen.dart`
- Test: `mobile/test/auth_screen_test.dart`

**Interfaces:**
- Produces: Polished login and registration flows with demo autofill chips.

- [ ] **Step 1: Write widget test for login screen**

Create `mobile/test/auth_screen_test.dart`.

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/auth_screen_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 3: Implement UI widgets and auth screens**

Implement `login_screen.dart` and `register_screen.dart` with demo account buttons, validation, and error alerts.

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/auth_screen_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 5: Commit Task 5**

```bash
git add mobile/lib/widgets/ mobile/lib/views/auth/ mobile/test/auth_screen_test.dart
git commit -m "feat(mobile): implement UI component library and citizen authentication screens"
```

---

### Task 6: Main Navigation, Dashboard & User Profile

**Files:**
- Create: `mobile/lib/views/dashboard/main_navigation_screen.dart`
- Create: `mobile/lib/views/dashboard/home_screen.dart`
- Create: `mobile/lib/views/profile/profile_screen.dart`
- Test: `mobile/test/dashboard_screen_test.dart`

**Interfaces:**
- Produces: Bottom navigation shell (Home, AI Scan, Riwayat Laporan, Profil), metric cards, and profile actions.

- [ ] **Step 1: Write test for dashboard and navigation shell**

Create `mobile/test/dashboard_screen_test.dart`.

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/dashboard_screen_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 3: Implement navigation shell, home screen, and profile screen**

Implement `main_navigation_screen.dart`, `home_screen.dart`, and `profile_screen.dart`.

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/dashboard_screen_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 5: Commit Task 6**

```bash
git add mobile/lib/views/dashboard/ mobile/lib/views/profile/ mobile/test/dashboard_screen_test.dart
git commit -m "feat(mobile): implement bottom navigation shell, home stats dashboard, and profile management"
```

---

### Task 7: Smart Report Creation with Camera, GPS & Auto AI Detection

**Files:**
- Create: `mobile/lib/widgets/map_preview.dart`
- Create: `mobile/lib/views/reports/create_report_screen.dart`
- Test: `mobile/test/create_report_screen_test.dart`

**Interfaces:**
- Produces: Complete waste reporting workflow with photo selection, instant AI classification confidence bar, GPS coordinate capture, OpenStreetMap pin preview, and submission.

- [ ] **Step 1: Write test for report creation form**

Create `mobile/test/create_report_screen_test.dart`.

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/create_report_screen_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 3: Implement `map_preview.dart` and `create_report_screen.dart`**

Implement `map_preview.dart` (using `flutter_map` with OpenStreetMap tile provider) and `create_report_screen.dart`.

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/create_report_screen_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 5: Commit Task 7**

```bash
git add mobile/lib/widgets/map_preview.dart mobile/lib/views/reports/create_report_screen.dart mobile/test/create_report_screen_test.dart
git commit -m "feat(mobile): implement smart report creation with camera, OpenStreetMap GPS, and auto AI categorization"
```

---

### Task 8: Standalone AI Scanner, Report History/Detail & Verification

**Files:**
- Create: `mobile/lib/views/scanner/ai_scanner_screen.dart`
- Create: `mobile/lib/views/reports/report_list_screen.dart`
- Create: `mobile/lib/views/reports/report_detail_screen.dart`
- Modify: `mobile/lib/main.dart`
- Test: `mobile/test/scanner_and_history_test.dart`

**Interfaces:**
- Produces: Standalone AI waste scanner, filterable report history, detailed report view with timeline, and full app integration.

- [ ] **Step 1: Write test for scanner and history screens**

Create `mobile/test/scanner_and_history_test.dart`.

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/scanner_and_history_test.dart` in `mobile/`
Expected: FAIL

- [ ] **Step 3: Implement AI scanner screen, report list, report detail, and wire `main.dart`**

Implement `ai_scanner_screen.dart`, `report_list_screen.dart`, `report_detail_screen.dart`, and update `main.dart` with providers and initial route.

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/scanner_and_history_test.dart` in `mobile/`
Expected: PASS

- [ ] **Step 5: Run full Flutter analyzer & entire test suite**

Run:
```powershell
flutter analyze
flutter test
```
Expected: 0 errors, ALL TESTS PASS.

- [ ] **Step 6: Commit Task 8**

```bash
git add mobile/
git commit -m "feat(mobile): implement standalone AI scanner, report history tracking, and complete app assembly"
```
