import 'package:smart_waste_mobile/models/report_model.dart';
import 'package:smart_waste_mobile/services/api_service.dart';

class ReportService {
  final ApiService apiService;

  ReportService({required this.apiService});

  Future<List<ReportModel>> getReports({String? status, String? category, String? search}) async {
    final response = await apiService.get('/reports', queryParams: {
      'status': status,
      'category': category,
      'search': search,
    });

    final List<ReportModel> list = [];
    if (response != null && response['data'] is List) {
      for (final item in response['data']) {
        list.add(ReportModel.fromJson(item));
      }
    }
    return list;
  }

  Future<ReportModel> getReportById(int id) async {
    final response = await apiService.get('/reports/$id');
    return ReportModel.fromJson(response['data']);
  }

  Future<Map<String, dynamic>> getDashboardStats() async {
    final response = await apiService.get('/dashboard/stats');
    if (response != null && response['data'] is Map<String, dynamic>) {
      final data = response['data'] as Map<String, dynamic>;
      final recent = <ReportModel>[];
      if (data['recent_reports'] is List) {
        for (final item in data['recent_reports']) {
          recent.add(ReportModel.fromJson(item));
        }
      }
      return {
        'total_reports': data['total_reports'] ?? 0,
        'pending_reports': data['pending_reports'] ?? 0,
        'in_progress_reports': data['in_progress_reports'] ?? 0,
        'completed_reports': data['completed_reports'] ?? 0,
        'rejected_reports': data['rejected_reports'] ?? 0,
        'recent_reports': recent,
      };
    }
    return {};
  }

  Future<ReportModel> submitReport({
    required String title,
    String? description,
    required List<int> imageBytes,
    required String imageName,
    required double latitude,
    required double longitude,
    String? addressNote,
    String? wasteCategory,
  }) async {
    final fields = {
      'title': title,
      'latitude': latitude.toString(),
      'longitude': longitude.toString(),
    };
    if (description != null && description.isNotEmpty) {
      fields['description'] = description;
    }
    if (addressNote != null && addressNote.isNotEmpty) {
      fields['address_note'] = addressNote;
    }
    if (wasteCategory != null && wasteCategory.isNotEmpty) {
      fields['waste_category'] = wasteCategory;
    }

    final response = await apiService.postMultipart(
      '/reports',
      fields: fields,
      fileField: 'image',
      fileBytes: imageBytes,
      filename: imageName,
    );

    return ReportModel.fromJson(response['data']);
  }
}
