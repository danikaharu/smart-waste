import 'package:flutter/foundation.dart';
import 'package:smart_waste_mobile/models/report_model.dart';
import 'package:smart_waste_mobile/services/report_service.dart';

class ReportProvider extends ChangeNotifier {
  final ReportService reportService;

  List<ReportModel> _reports = [];
  Map<String, dynamic> _stats = {
    'total_reports': 0,
    'pending_reports': 0,
    'in_progress_reports': 0,
    'completed_reports': 0,
    'rejected_reports': 0,
    'recent_reports': <ReportModel>[],
  };

  bool _isLoading = false;
  bool _isSubmitting = false;
  String? _errorMessage;

  String? _filterStatus;
  String? _filterCategory;
  String? _searchQuery;

  ReportProvider({required this.reportService});

  List<ReportModel> get reports => _reports;
  Map<String, dynamic> get stats => _stats;
  bool get isLoading => _isLoading;
  bool get isSubmitting => _isSubmitting;
  String? get errorMessage => _errorMessage;
  String? get filterStatus => _filterStatus;
  String? get filterCategory => _filterCategory;

  void setFilterStatus(String? status) {
    _filterStatus = status;
    fetchReports();
  }

  void setFilterCategory(String? category) {
    _filterCategory = category;
    fetchReports();
  }

  void setSearchQuery(String? query) {
    _searchQuery = query;
    fetchReports();
  }

  Future<void> fetchReports() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _reports = await reportService.getReports(
        status: _filterStatus,
        category: _filterCategory,
        search: _searchQuery,
      );
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchStats() async {
    try {
      final data = await reportService.getDashboardStats();
      if (data.isNotEmpty) {
        _stats = data;
        notifyListeners();
      }
    } catch (e) {
      debugPrint('[ReportProvider] fetchStats failed: $e');
    }
  }

  Future<ReportModel?> submitReport({
    required String title,
    String? description,
    required List<int> imageBytes,
    required String imageName,
    required double latitude,
    required double longitude,
    String? addressNote,
    String? wasteCategory,
  }) async {
    _isSubmitting = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final report = await reportService.submitReport(
        title: title,
        description: description,
        imageBytes: imageBytes,
        imageName: imageName,
        latitude: latitude,
        longitude: longitude,
        addressNote: addressNote,
        wasteCategory: wasteCategory,
      );

      _isSubmitting = false;
      // Prepend to current reports list
      _reports.insert(0, report);
      // Refresh stats
      fetchStats();
      notifyListeners();
      return report;
    } catch (e) {
      _errorMessage = e.toString();
      _isSubmitting = false;
      notifyListeners();
      return null;
    }
  }
}
