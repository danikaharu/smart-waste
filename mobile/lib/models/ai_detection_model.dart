class AiDetectionModel {
  final String label;
  final String category;
  final double confidence;
  final Map<String, double> probabilities;

  AiDetectionModel({
    required this.label,
    required this.category,
    required this.confidence,
    this.probabilities = const {},
  });

  String get confidencePercent => '${(confidence * 100).toStringAsFixed(1)}%';

  factory AiDetectionModel.fromJson(Map<String, dynamic> json) {
    final probsRaw = json['probabilities'];
    final Map<String, double> probs = {};

    if (probsRaw is Map) {
      probsRaw.forEach((key, value) {
        if (value is num) {
          probs[key.toString()] = value.toDouble();
        }
      });
    }

    return AiDetectionModel(
      label: json['label'] as String? ?? 'Unclassified',
      category: json['category'] as String? ?? 'Belum Terklasifikasi',
      confidence: json['confidence'] is num
          ? (json['confidence'] as num).toDouble()
          : double.tryParse(json['confidence'].toString()) ?? 0.0,
      probabilities: probs,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'label': label,
      'category': category,
      'confidence': confidence,
      'probabilities': probabilities,
    };
  }
}
