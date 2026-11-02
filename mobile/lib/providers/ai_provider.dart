import 'package:flutter/foundation.dart';
import 'package:smart_waste_mobile/models/ai_detection_model.dart';
import 'package:smart_waste_mobile/services/ai_service.dart';

class AiProvider extends ChangeNotifier {
  final AiService aiService;

  AiDetectionModel? _detection;
  bool _isAnalyzing = false;
  String? _errorMessage;

  AiProvider({required this.aiService});

  AiDetectionModel? get detection => _detection;
  bool get isAnalyzing => _isAnalyzing;
  String? get errorMessage => _errorMessage;

  void reset() {
    _detection = null;
    _errorMessage = null;
    _isAnalyzing = false;
    notifyListeners();
  }

  Future<AiDetectionModel?> classifyImage(List<int> bytes, String filename) async {
    _isAnalyzing = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final result = await aiService.detectWasteFromBytes(bytes, filename);
      _detection = result;
      _isAnalyzing = false;
      notifyListeners();
      return result;
    } catch (e) {
      _errorMessage = e.toString();
      _isAnalyzing = false;
      notifyListeners();
      return null;
    }
  }
}
