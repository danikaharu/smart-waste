import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:smart_waste_mobile/main.dart';
import 'package:smart_waste_mobile/providers/ai_provider.dart';
import 'package:smart_waste_mobile/providers/auth_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/services/ai_service.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/auth_service.dart';
import 'package:smart_waste_mobile/services/report_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  testWidgets('SmartWasteApp smoke test initializes without errors', (WidgetTester tester) async {
    final apiService = ApiService();
    final authService = AuthService(apiService: apiService);
    final reportService = ReportService(apiService: apiService);
    final aiService = AiService(apiService: apiService);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider(create: (_) => AuthProvider(authService: authService)),
          ChangeNotifierProvider(create: (_) => ReportProvider(reportService: reportService)),
          ChangeNotifierProvider(create: (_) => AiProvider(aiService: aiService)),
        ],
        child: const SmartWasteApp(),
      ),
    );
    await tester.pump();

    expect(find.byType(SmartWasteApp), findsOneWidget);
  });
}
