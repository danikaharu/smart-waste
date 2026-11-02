import 'package:smart_waste_mobile/models/ai_detection_model.dart';
import 'package:smart_waste_mobile/services/api_service.dart';

class AiService {
  final ApiService apiService;

  AiService({required this.apiService});

  Future<AiDetectionModel> detectWasteFromBytes(List<int> bytes, String filename) async {
    final response = await apiService.postMultipart(
      '/detect-waste',
      fields: {},
      fileField: 'image',
      fileBytes: bytes,
      filename: filename,
    );

    return AiDetectionModel.fromJson(response['data']);
  }
}
