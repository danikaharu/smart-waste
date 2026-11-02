import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/views/reports/create_report_screen.dart';
import 'package:smart_waste_mobile/views/reports/report_detail_screen.dart';
import 'package:smart_waste_mobile/widgets/report_card.dart';

class ReportListScreen extends StatefulWidget {
  const ReportListScreen({super.key});

  @override
  State<ReportListScreen> createState() => _ReportListScreenState();
}

class _ReportListScreenState extends State<ReportListScreen> {
  final _searchController = TextEditingController();
  String? _activeStatus;

  final List<Map<String, String?>> _statusTabs = [
    {'label': 'Semua', 'status': null},
    {'label': 'Menunggu', 'status': AppConstants.statusPending},
    {'label': 'Diproses', 'status': AppConstants.statusInProgress},
    {'label': 'Selesai', 'status': AppConstants.statusCompleted},
    {'label': 'Ditolak', 'status': AppConstants.statusRejected},
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<ReportProvider>(context, listen: false).fetchReports();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSelectTab(String? status) {
    setState(() {
      _activeStatus = status;
    });
    Provider.of<ReportProvider>(context, listen: false).setFilterStatus(status);
  }

  @override
  Widget build(BuildContext context) {
    final reportProvider = Provider.of<ReportProvider>(context);
    final reports = reportProvider.reports;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Riwayat Laporan'),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CreateReportScreen()),
          );
        },
        backgroundColor: AppTheme.primaryColor,
        icon: const Icon(Icons.add_a_photo_outlined, color: Colors.white),
        label: const Text('Lapor', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
      ),
      body: Column(
        children: [
          // Search & Filter Header
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Column(
              children: [
                // Search Input
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Cari judul, lokasi, atau deskripsi...',
                    prefixIcon: const Icon(Icons.search, size: 20, color: Color(0xFF94A3B8)),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchController.clear();
                              reportProvider.setSearchQuery(null);
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  ),
                  onSubmitted: (val) {
                    reportProvider.setSearchQuery(val.trim().isNotEmpty ? val.trim() : null);
                  },
                ),
                const SizedBox(height: 12),

                // Status Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: _statusTabs.map((tab) {
                      final isSelected = _activeStatus == tab['status'];
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: FilterChip(
                          label: Text(tab['label']!),
                          selected: isSelected,
                          onSelected: (_) => _onSelectTab(tab['status']),
                          selectedColor: AppTheme.primaryLight,
                          checkmarkColor: AppTheme.primaryColor,
                          labelStyle: TextStyle(
                            color: isSelected ? AppTheme.primaryDark : const Color(0xFF64748B),
                            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                            fontSize: 12,
                          ),
                          backgroundColor: const Color(0xFFF1F5F9),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                            side: BorderSide(
                              color: isSelected ? AppTheme.primaryColor : Colors.transparent,
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: Color(0xFFE2E8F0)),

          // Reports List
          Expanded(
            child: RefreshIndicator(
              onRefresh: () => reportProvider.fetchReports(),
              color: AppTheme.primaryColor,
              child: reportProvider.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : reports.isEmpty
                      ? ListView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          children: [
                            Padding(
                              padding: const EdgeInsets.symmetric(vertical: 80, horizontal: 30),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.assignment_outlined, size: 64, color: Colors.grey.shade400),
                                  const SizedBox(height: 16),
                                  const Text(
                                    'Tidak Ada Laporan Ditemukan',
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                                  ),
                                  const SizedBox(height: 6),
                                  const Text(
                                    'Belum ada laporan yang sesuai dengan filter atau kata kunci yang Anda masukkan.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: reports.length,
                          itemBuilder: (context, index) {
                            final report = reports[index];
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
            ),
          ),
        ],
      ),
    );
  }
}
