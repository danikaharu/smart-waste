import 'package:flutter_test/flutter_test.dart';
import 'package:smart_waste_mobile/models/user_model.dart';
import 'package:smart_waste_mobile/models/report_model.dart';
import 'package:smart_waste_mobile/models/ai_detection_model.dart';

void main() {
  group('UserModel', () {
    test('parses JSON correctly', () {
      final json = {
        'id': 1,
        'name': 'Dani Citizen',
        'email': 'user@smartwaste.com',
        'phone': '08123456789',
        'role': 'user',
        'is_active': true,
      };

      final user = UserModel.fromJson(json);

      expect(user.id, equals(1));
      expect(user.name, equals('Dani Citizen'));
      expect(user.email, equals('user@smartwaste.com'));
      expect(user.phone, equals('08123456789'));
      expect(user.role, equals('user'));
      expect(user.isActive, isTrue);
      expect(user.isAdmin, isFalse);

      final encoded = user.toJson();
      expect(encoded['email'], equals('user@smartwaste.com'));
    });
  });

  group('ReportModel', () {
    test('parses JSON correctly and resolves status labels and image URL', () {
      final json = {
        'id': 10,
        'user_id': 1,
        'title': 'Sampah Plastik Menumpuk',
        'description': 'Dekat pintu masuk taman',
        'image_path': 'reports/sample.jpg',
        'image_url': 'http://127.0.0.1:8000/storage/reports/sample.jpg',
        'latitude': -6.2088,
        'longitude': 106.8456,
        'address_note': 'Taman Suropati',
        'waste_category': 'Plastic Waste',
        'ai_confidence': 0.9542,
        'status': 'pending',
        'admin_notes': null,
        'created_at': '2026-11-02T10:00:00.000000Z',
      };

      final report = ReportModel.fromJson(json);

      expect(report.id, equals(10));
      expect(report.title, equals('Sampah Plastik Menumpuk'));
      expect(report.wasteCategory, equals('Plastic Waste'));
      expect(report.aiConfidence, equals(0.9542));
      expect(report.status, equals('pending'));
      expect(report.statusLabel, equals('Menunggu'));
      expect(report.formattedConfidence, equals('95.4%'));
      expect(report.latitude, equals(-6.2088));
      expect(report.longitude, equals(106.8456));
    });
  });

  group('AiDetectionModel', () {
    test('parses detection result and probabilities correctly', () {
      final json = {
        'label': 'Organic Waste',
        'category': 'Organik (Sisa Makanan/Daun)',
        'confidence': 0.9820,
        'probabilities': {
          'Organic Waste': 0.9820,
          'Plastic Waste': 0.0110,
          'Paper Waste': 0.0070,
        },
      };

      final detection = AiDetectionModel.fromJson(json);

      expect(detection.label, equals('Organic Waste'));
      expect(detection.confidence, equals(0.9820));
      expect(detection.confidencePercent, equals('98.2%'));
      expect(detection.probabilities['Organic Waste'], equals(0.9820));
    });
  });
}
