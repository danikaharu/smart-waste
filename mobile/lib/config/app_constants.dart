import 'package:flutter/foundation.dart';

class AppConstants {
  static const String appName = 'Smart Waste';
  static const String appTagline = 'Kelola Sampah Cerdas Bersama Warga';

  /// Base API URL. In Android Emulator, host machine is accessible via 10.0.2.2.
  /// On Web/Windows desktop, host is 127.0.0.1 (or localhost).
  static String get apiBaseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api';
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return 'http://10.0.2.2:8000/api';
      case TargetPlatform.iOS:
      case TargetPlatform.windows:
      case TargetPlatform.macOS:
      case TargetPlatform.linux:
      default:
        return 'http://127.0.0.1:8000/api';
    }
  }

  /// Base URL for public storage assets (uploaded photos)
  static String get storageBaseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/storage';
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return 'http://10.0.2.2:8000/storage';
      case TargetPlatform.iOS:
      case TargetPlatform.windows:
      case TargetPlatform.macOS:
      case TargetPlatform.linux:
      default:
        return 'http://127.0.0.1:8000/storage';
    }
  }

  // Categories
  static const List<String> categories = [
    'Organic Waste',
    'Plastic Waste',
    'Paper Waste',
  ];

  static const Map<String, String> categoryLabelsId = {
    'Organic Waste': 'Organik (Sisa Makanan/Daun)',
    'Plastic Waste': 'Anorganik (Plastik/Botol)',
    'Paper Waste': 'Daur Ulang (Kertas/Karton)',
    'Unclassified': 'Belum Terklasifikasi',
  };

  // Statuses
  static const String statusPending = 'pending';
  static const String statusInProgress = 'in_progress';
  static const String statusCompleted = 'completed';
  static const String statusRejected = 'rejected';

  static const Map<String, String> statusLabelsId = {
    statusPending: 'Menunggu',
    statusInProgress: 'Diproses',
    statusCompleted: 'Selesai',
    statusRejected: 'Ditolak',
  };

  // SharedPreferences Keys
  static const String prefTokenKey = 'auth_token';
  static const String prefUserKey = 'auth_user';

  // Fallback Coordinates (Jakarta Pusat)
  static const double defaultLatitude = -6.2088;
  static const double defaultLongitude = 106.8456;
}
