import 'package:flutter/material.dart';
import 'package:smart_waste_mobile/models/report_model.dart';

class ReportDetailScreen extends StatelessWidget {
  final ReportModel report;

  const ReportDetailScreen({super.key, required this.report});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detail Laporan')),
      body: Center(child: Text(report.title)),
    );
  }
}
