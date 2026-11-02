import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/models/report_model.dart';
import 'package:smart_waste_mobile/providers/auth_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/views/reports/create_report_screen.dart';
import 'package:smart_waste_mobile/views/reports/report_detail_screen.dart';
import 'package:smart_waste_mobile/widgets/report_card.dart';
import 'package:smart_waste_mobile/widgets/stat_card.dart';

class HomeScreen extends StatefulWidget {
  final VoidCallback? onNavigateToReports;

  const HomeScreen({super.key, this.onNavigateToReports});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final reportProvider = Provider.of<ReportProvider>(context, listen: false);
      reportProvider.fetchStats();
      reportProvider.fetchReports();
    });
  }

  Future<void> _refresh() async {
    final reportProvider = Provider.of<ReportProvider>(context, listen: false);
    await Future.wait([
      reportProvider.fetchStats(),
      reportProvider.fetchReports(),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final reportProvider = Provider.of<ReportProvider>(context);
    final stats = reportProvider.stats;
    final userName = auth.currentUser?.name ?? 'Warga Peduli';

    final totalReports = stats['total_reports']?.toString() ?? '0';
    final pendingReports = stats['pending_reports']?.toString() ?? '0';
    final inProgressReports = stats['in_progress_reports']?.toString() ?? '0';
    final completedReports = stats['completed_reports']?.toString() ?? '0';
    final List<ReportModel> recentReports = (stats['recent_reports'] as List<ReportModel>?) ?? reportProvider.reports.take(5).toList();

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 20,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: AppTheme.primaryLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.eco_rounded, color: AppTheme.primaryColor, size: 22),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Halo, $userName',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                ),
                const Text(
                  'Mari jaga kebersihan lingkungan',
                  style: TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.normal),
                ),
              ],
            ),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        color: AppTheme.primaryColor,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Hero Call-to-Action Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF059669), Color(0xFF10B981)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryColor.withValues(alpha: 0.25),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.add_photo_alternate_rounded, color: Colors.white, size: 24),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Temukan Sampah Liar?',
                                style: TextStyle(
                                  fontSize: 17,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                              SizedBox(height: 2),
                              Text(
                                'Foto & laporkan untuk segera dibersihkan',
                                style: TextStyle(fontSize: 12, color: Colors.white70),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
                    ElevatedButton.icon(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const CreateReportScreen()),
                        );
                      },
                      icon: const Icon(Icons.camera_alt_rounded, size: 18, color: AppTheme.primaryColor),
                      label: const Text(
                        'Lapor Sampah Sekarang',
                        style: TextStyle(
                          color: AppTheme.primaryColor,
                          fontWeight: FontWeight.w700,
                          fontSize: 14,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: AppTheme.primaryColor,
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Section: Ringkasan Laporan
              const Text(
                'Ringkasan Laporan',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 12),

              // 2x2 Grid of StatCards
              GridView.count(
                crossAxisCount: 2,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: 1.5,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                children: [
                  StatCard(
                    title: 'Total Laporan',
                    value: totalReports,
                    icon: Icons.folder_outlined,
                    color: const Color(0xFF0F172A),
                    bgColor: const Color(0xFFF1F5F9),
                  ),
                  StatCard(
                    title: 'Menunggu',
                    value: pendingReports,
                    icon: Icons.hourglass_top_rounded,
                    color: AppTheme.statusPending,
                    bgColor: AppTheme.statusPendingBg,
                  ),
                  StatCard(
                    title: 'Diproses',
                    value: inProgressReports,
                    icon: Icons.autorenew_rounded,
                    color: AppTheme.statusInProgress,
                    bgColor: AppTheme.statusInProgressBg,
                  ),
                  StatCard(
                    title: 'Selesai',
                    value: completedReports,
                    icon: Icons.check_circle_outline_rounded,
                    color: AppTheme.statusCompleted,
                    bgColor: AppTheme.statusCompletedBg,
                  ),
                ],
              ),
              const SizedBox(height: 28),

              // Section: Laporan Terbaru
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Laporan Terbaru',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                  ),
                  if (widget.onNavigateToReports != null)
                    TextButton(
                      onPressed: widget.onNavigateToReports,
                      child: const Text('Lihat Semua'),
                    ),
                ],
              ),
              const SizedBox(height: 10),

              if (recentReports.isEmpty)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    children: [
                      Icon(Icons.inbox_outlined, size: 48, color: Colors.grey.shade400),
                      const SizedBox(height: 12),
                      const Text(
                        'Belum ada laporan sampah',
                        style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Laporan yang Anda kirim akan muncul di sini',
                        style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                      ),
                    ],
                  ),
                )
              else
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: recentReports.length,
                  itemBuilder: (context, index) {
                    final report = recentReports[index];
                    return ReportCard(
                      report: report,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => ReportDetailScreen(report: report),
                          ),
                        );
                      },
                    );
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }
}
