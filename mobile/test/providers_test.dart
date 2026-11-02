import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:smart_waste_mobile/providers/auth_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/providers/ai_provider.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/auth_service.dart';
import 'package:smart_waste_mobile/services/report_service.dart';
import 'package:smart_waste_mobile/services/ai_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  group('AuthProvider', () {
    test('login updates isAuthenticated and currentUser', () async {
      final mockClient = MockClient((request) async {
        return http.Response(
          jsonEncode({
            'status': 'success',
            'data': {
              'token': 'auth-test-token',
              'user': {
                'id': 1,
                'name': 'Dani User',
                'email': 'dani@test.com',
                'role': 'user',
                'is_active': true,
              },
            },
          }),
          200,
          headers: {'content-type': 'application/json'},
        );
      });

      final api = ApiService(client: mockClient);
      final authService = AuthService(apiService: api);
      final provider = AuthProvider(authService: authService);

      expect(provider.isAuthenticated, isFalse);

      final success = await provider.login('dani@test.com', 'secret');
      expect(success, isTrue);
      expect(provider.isAuthenticated, isTrue);
      expect(provider.currentUser?.name, equals('Dani User'));
    });
  });

  group('ReportProvider', () {
    test('fetchReports updates reports list', () async {
      final mockClient = MockClient((request) async {
        return http.Response(
          jsonEncode({
            'status': 'success',
            'data': [
              {
                'id': 101,
                'user_id': 1,
                'title': 'Sampah Plastik Sungai',
                'latitude': -6.2100,
                'longitude': 106.8500,
                'waste_category': 'Plastic Waste',
                'status': 'in_progress',
              }
            ],
          }),
          200,
          headers: {'content-type': 'application/json'},
        );
      });

      final api = ApiService(client: mockClient);
      final reportService = ReportService(apiService: api);
      final provider = ReportProvider(reportService: reportService);

      await provider.fetchReports();

      expect(provider.reports.length, equals(1));
      expect(provider.reports.first.id, equals(101));
      expect(provider.isLoading, isFalse);
    });
  });

  group('AiProvider', () {
    test('classifyImage updates detection result', () async {
      final mockClient = MockClient((request) async {
        return http.Response(
          jsonEncode({
            'status': 'success',
            'data': {
              'label': 'Paper Waste',
              'category': 'Daur Ulang (Kertas/Karton)',
              'confidence': 0.9650,
              'probabilities': {
                'Paper Waste': 0.9650,
                'Plastic Waste': 0.0200,
                'Organic Waste': 0.0150,
              },
            },
          }),
          200,
          headers: {'content-type': 'application/json'},
        );
      });

      final api = ApiService(client: mockClient);
      final aiService = AiService(apiService: api);
      final provider = AiProvider(aiService: aiService);

      await provider.classifyImage([1, 2, 3], 'paper.jpg');

      expect(provider.detection, isNotNull);
      expect(provider.detection?.label, equals('Paper Waste'));
      expect(provider.isAnalyzing, isFalse);
    });
  });
}
