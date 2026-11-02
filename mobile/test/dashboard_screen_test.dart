import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/providers/auth_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/auth_service.dart';
import 'package:smart_waste_mobile/services/report_service.dart';
import 'package:smart_waste_mobile/views/dashboard/home_screen.dart';

void main() {
  testWidgets('HomeScreen renders greeting, stats cards, and action button', (tester) async {
    final api = ApiService();
    final authService = AuthService(apiService: api);
    final reportService = ReportService(apiService: api);

    final authProvider = AuthProvider(authService: authService);
    final reportProvider = ReportProvider(reportService: reportService);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
          ChangeNotifierProvider<ReportProvider>.value(value: reportProvider),
        ],
        child: MaterialApp(
          theme: AppTheme.lightTheme,
          home: const HomeScreen(),
        ),
      ),
    );

    // Verify elements on HomeScreen
    expect(find.text('Ringkasan Laporan'), findsOneWidget);
    expect(find.text('Lapor Sampah Sekarang'), findsOneWidget);
    expect(find.text('Total Laporan'), findsOneWidget);
    expect(find.text('Menunggu'), findsOneWidget);
    expect(find.text('Diproses'), findsOneWidget);
    expect(find.text('Selesai'), findsOneWidget);
  });
}
