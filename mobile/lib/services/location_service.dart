import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';

class LocationResult {
  final double latitude;
  final double longitude;
  final bool isFallback;
  final String? errorMessage;

  LocationResult({
    required this.latitude,
    required this.longitude,
    this.isFallback = false,
    this.errorMessage,
  });
}

class LocationService {
  Future<LocationResult> getCurrentLocation() async {
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        return LocationResult(
          latitude: AppConstants.defaultLatitude,
          longitude: AppConstants.defaultLongitude,
          isFallback: true,
          errorMessage: 'Layanan lokasi (GPS) tidak aktif. Menggunakan lokasi default.',
        );
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          return LocationResult(
            latitude: AppConstants.defaultLatitude,
            longitude: AppConstants.defaultLongitude,
            isFallback: true,
            errorMessage: 'Izin lokasi ditolak. Menggunakan lokasi default.',
          );
        }
      }

      if (permission == LocationPermission.deniedForever) {
        return LocationResult(
          latitude: AppConstants.defaultLatitude,
          longitude: AppConstants.defaultLongitude,
          isFallback: true,
          errorMessage: 'Izin lokasi ditolak permanen. Menggunakan lokasi default.',
        );
      }

      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 10),
      );

      return LocationResult(
        latitude: position.latitude,
        longitude: position.longitude,
        isFallback: false,
      );
    } catch (e) {
      debugPrint('[LocationService] Exception acquiring location: $e');
      return LocationResult(
        latitude: AppConstants.defaultLatitude,
        longitude: AppConstants.defaultLongitude,
        isFallback: true,
        errorMessage: 'Gagal membaca GPS: ${e.toString()}',
      );
    }
  }
}
