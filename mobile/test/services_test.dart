import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/auth_service.dart';
import 'package:smart_waste_mobile/services/report_service.dart';
import 'package:smart_waste_mobile/services/ai_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  group('AuthService', () {
    test('login stores token and returns user and token', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/login')) {
          return http.Response(
            jsonEncode({
              'status': 'success',
              'data': {
                'token': 'mock-token-xyz',
                'user': {
                  'id': 1,
                  'name': 'Dani Citizen',
                  'email': 'user@smartwaste.com',
                  'role': 'user',
                  'is_active': true,
                },
              },
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final api = ApiService(client: mockClient);
      final auth = AuthService(apiService: api);

      final result = await auth.login('user@smartwaste.com', 'password');

      expect(result['token'], equals('mock-token-xyz'));
      expect(result['user'].email, equals('user@smartwaste.com'));

      final token = await api.getToken();
      expect(token, equals('mock-token-xyz'));
    });
  });

  group('ReportService', () {
    test('getReports parses list of reports', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/reports')) {
          return http.Response(
            jsonEncode({
              'status': 'success',
              'data': [
                {
                  'id': 1,
                  'user_id': 1,
                  'title': 'Sampah Pasar',
                  'latitude': -6.2000,
                  'longitude': 106.8000,
                  'waste_category': 'Organic Waste',
                  'ai_confidence': 0.99,
                  'status': 'pending',
                }
              ],
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final api = ApiService(client: mockClient);
      final reportService = ReportService(apiService: api);

      final reports = await reportService.getReports();

      expect(reports.length, equals(1));
      expect(reports.first.title, equals('Sampah Pasar'));
      expect(reports.first.wasteCategory, equals('Organic Waste'));
    });
  });

  group('AiService', () {
    test('detectWaste parses response correctly', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/detect-waste')) {
          return http.Response(
            jsonEncode({
              'status': 'success',
              'data': {
                'label': 'Plastic Waste',
                'category': 'Anorganik (Plastik)',
                'confidence': 0.9854,
                'probabilities': {
                  'Plastic Waste': 0.9854,
                  'Organic Waste': 0.0100,
                  'Paper Waste': 0.0046,
                },
              },
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final api = ApiService(client: mockClient);
      final aiService = AiService(apiService: api);

      final result = await aiService.detectWasteFromBytes(
        [1, 2, 3],
        'test.jpg',
      );

      expect(result.label, equals('Plastic Waste'));
      expect(result.confidence, equals(0.9854));
    });
  });
}
