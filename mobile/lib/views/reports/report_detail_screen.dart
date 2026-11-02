import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/models/report_model.dart';
import 'package:smart_waste_mobile/widgets/map_preview.dart';
import 'package:smart_waste_mobile/widgets/status_badge.dart';

class ReportDetailScreen extends StatelessWidget {
  final ReportModel report;

  const ReportDetailScreen({super.key, required this.report});

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('EEEE, d MMMM yyyy - HH:mm');
    final formattedDate = report.createdAt != null
        ? dateFormat.format(report.createdAt!)
        : '-';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Laporan'),
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Full-Width Photo Preview
            Container(
              width: double.infinity,
              height: 240,
              color: const Color(0xFFF1F5F9),
              child: report.resolvedImageUrl != null
                  ? Image.network(
                      report.resolvedImageUrl!,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => const Center(
                        child: Icon(Icons.broken_image_outlined, size: 60, color: Color(0xFF94A3B8)),
                      ),
                    )
                  : const Center(
                      child: Icon(Icons.image_outlined, size: 60, color: Color(0xFF94A3B8)),
                    ),
            ),

            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Badges: Category & Status
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryLight,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          report.wasteCategory,
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.primaryDark,
                          ),
                        ),
                      ),
                      StatusBadge(status: report.status),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Report Title
                  Text(
                    report.title,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 6),

                  // Date & Timestamp
                  Row(
                    children: [
                      const Icon(Icons.access_time_rounded, size: 14, color: Color(0xFF94A3B8)),
                      const SizedBox(width: 4),
                      Text(
                        formattedDate,
                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // AI Confidence Badge
                  if (report.aiConfidence != null) ...[
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFBBF7D0)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.auto_awesome, color: AppTheme.primaryColor, size: 16),
                          const SizedBox(width: 8),
                          Text(
                            'Klasifikasi AI: ${report.formattedConfidence} Keyakinan',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: AppTheme.primaryDark,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Description
                  if (report.description != null && report.description!.isNotEmpty) ...[
                    const Text(
                      'Deskripsi Laporan:',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      report.description!,
                      style: const TextStyle(fontSize: 14, color: Color(0xFF475569), height: 1.5),
                    ),
                    const SizedBox(height: 20),
                  ],

                  // Admin Notes (If present)
                  if (report.adminNotes != null && report.adminNotes!.isNotEmpty) ...[
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF9C3),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFFDE047)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.admin_panel_settings_rounded, size: 18, color: Color(0xFF854D0E)),
                              SizedBox(width: 6),
                              Text(
                                'Catatan Petugas / Admin',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF854D0E),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(
                            report.adminNotes!,
                            style: const TextStyle(fontSize: 13, color: Color(0xFF713F12), height: 1.4),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],

                  // Geolocation Map
                  const Text(
                    'Lokasi di Peta:',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                  ),
                  const SizedBox(height: 8),
                  MapPreview(
                    latitude: report.latitude,
                    longitude: report.longitude,
                    height: 200,
                    addressNote: report.addressNote,
                  ),
                  if (report.addressNote != null && report.addressNote!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.pin_drop_rounded, size: 16, color: Color(0xFF64748B)),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            'Patokan: ${report.addressNote}',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontStyle: FontStyle.italic),
                          ),
                        ),
                      ],
                    ),
                  ],
                  const SizedBox(height: 28),

                  // Status Workflow Timeline
                  const Text(
                    'Perjalanan Status Laporan:',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                  ),
                  const SizedBox(height: 12),
                  _buildTimelineTile(
                    title: 'Laporan Dibuat',
                    subtitle: 'Laporan berhasil dicatat dalam sistem',
                    isDone: true,
                    isLast: false,
                  ),
                  _buildTimelineTile(
                    title: 'Verifikasi & Penugasan Petugas',
                    subtitle: report.status == 'in_progress' || report.status == 'completed'
                        ? 'Petugas telah ditugaskan ke lokasi sampah'
                        : 'Menunggu verifikasi admin',
                    isDone: report.status == 'in_progress' || report.status == 'completed',
                    isLast: false,
                  ),
                  _buildTimelineTile(
                    title: report.status == 'rejected' ? 'Laporan Ditolak' : 'Pembersihan Selesai',
                    subtitle: report.status == 'completed'
                        ? 'Lokasi telah dibersihkan dan diverifikasi'
                        : report.status == 'rejected'
                            ? (report.adminNotes ?? 'Laporan tidak memenuhi kriteria verifikasi')
                            : 'Petugas sedang menyelesaikan penanganan',
                    isDone: report.status == 'completed' || report.status == 'rejected',
                    isError: report.status == 'rejected',
                    isLast: true,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTimelineTile({
    required String title,
    required String subtitle,
    required bool isDone,
    bool isError = false,
    required bool isLast,
  }) {
    final Color color = isError
        ? AppTheme.statusRejected
        : isDone
            ? AppTheme.primaryColor
            : const Color(0xFFCBD5E1);

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                color: isDone ? color : Colors.white,
                shape: BoxShape.circle,
                border: Border.all(color: color, width: 2),
              ),
              child: isDone
                  ? Icon(
                      isError ? Icons.close : Icons.check,
                      size: 14,
                      color: Colors.white,
                    )
                  : null,
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 36,
                color: isDone ? color : const Color(0xFFE2E8F0),
              ),
          ],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: isDone ? const Color(0xFF0F172A) : const Color(0xFF94A3B8),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDone ? const Color(0xFF64748B) : const Color(0xFFCBD5E1),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
