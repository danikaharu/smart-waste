import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/providers/ai_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/services/ai_service.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/report_service.dart';
import 'package:smart_waste_mobile/views/reports/create_report_screen.dart';

void main() {
  testWidgets('CreateReportScreen renders form fields, camera buttons, and submit action', (tester) async {
    final api = ApiService();
    final reportService = ReportService(apiService: api);
    final aiService = AiService(apiService: api);

    final reportProvider = ReportProvider(reportService: reportService);
    final aiProvider = AiProvider(aiService: aiService);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<ReportProvider>.value(value: reportProvider),
          ChangeNotifierProvider<AiProvider>.value(value: aiProvider),
        ],
        child: MaterialApp(
          theme: AppTheme.lightTheme,
          home: const CreateReportScreen(),
        ),
      ),
    );

    // Verify presence of title, input fields, camera/gallery buttons, and submit
    expect(find.text('Buat Laporan Sampah'), findsOneWidget);
    expect(find.text('Ambil Foto'), findsOneWidget);
    expect(find.text('Galeri'), findsOneWidget);
    expect(find.text('Dapatkan Lokasi GPS'), findsOneWidget);
    expect(find.text('Kirim Laporan Sampah'), findsOneWidget);
  });
}
