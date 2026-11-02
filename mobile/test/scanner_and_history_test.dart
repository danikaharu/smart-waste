import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/models/report_model.dart';
import 'package:smart_waste_mobile/providers/ai_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/services/ai_service.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/report_service.dart';
import 'package:smart_waste_mobile/views/reports/report_detail_screen.dart';
import 'package:smart_waste_mobile/views/reports/report_list_screen.dart';
import 'package:smart_waste_mobile/views/scanner/ai_scanner_screen.dart';

void main() {
  testWidgets('AiScannerScreen renders scanner header and action buttons', (tester) async {
    final api = ApiService();
    final aiService = AiService(apiService: api);
    final aiProvider = AiProvider(aiService: aiService);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AiProvider>.value(value: aiProvider),
        ],
        child: MaterialApp(
          theme: AppTheme.lightTheme,
          home: const AiScannerScreen(),
        ),
      ),
    );

    expect(find.text('Deteksi Sampah AI'), findsOneWidget);
    expect(find.text('Ambil Foto'), findsOneWidget);
    expect(find.text('Pilih Galeri'), findsOneWidget);
  });

  testWidgets('ReportListScreen renders filter tabs and search', (tester) async {
    final api = ApiService();
    final reportService = ReportService(apiService: api);
    final reportProvider = ReportProvider(reportService: reportService);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<ReportProvider>.value(value: reportProvider),
        ],
        child: MaterialApp(
          theme: AppTheme.lightTheme,
          home: const ReportListScreen(),
        ),
      ),
    );

    expect(find.text('Riwayat Laporan'), findsOneWidget);
    expect(find.text('Semua'), findsOneWidget);
    expect(find.text('Menunggu'), findsOneWidget);
    expect(find.text('Diproses'), findsOneWidget);
    expect(find.text('Selesai'), findsOneWidget);
  });

  testWidgets('ReportDetailScreen renders report information and status timeline', (tester) async {
    final report = ReportModel(
      id: 99,
      userId: 1,
      title: 'Tumpukan Sampah Plastik Pasar',
      description: 'Menyumbat saluran pembuangan air di pasar induk',
      latitude: -6.2088,
      longitude: 106.8456,
      addressNote: 'Pasar Induk Blok A',
      wasteCategory: 'Plastic Waste',
      aiConfidence: 0.9825,
      status: 'in_progress',
      adminNotes: 'Tim kebersihan sedang menuju ke lokasi',
    );

    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.lightTheme,
        home: ReportDetailScreen(report: report),
      ),
    );

    expect(find.text('Tumpukan Sampah Plastik Pasar'), findsOneWidget);
    expect(find.text('Menyumbat saluran pembuangan air di pasar induk'), findsOneWidget);
    expect(find.text('Catatan Petugas / Admin'), findsOneWidget);
  });
}
