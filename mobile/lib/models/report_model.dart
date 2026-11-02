import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/models/user_model.dart';

class ReportModel {
  final int id;
  final int userId;
  final String title;
  final String? description;
  final String? imagePath;
  final String? imageUrl;
  final double latitude;
  final double longitude;
  final String? addressNote;
  final String wasteCategory;
  final double? aiConfidence;
  final String status;
  final String? adminNotes;
  final DateTime? createdAt;
  final DateTime? resolvedAt;
  final UserModel? user;

  ReportModel({
    required this.id,
    required this.userId,
    required this.title,
    this.description,
    this.imagePath,
    this.imageUrl,
    required this.latitude,
    required this.longitude,
    this.addressNote,
    this.wasteCategory = 'Unclassified',
    this.aiConfidence,
    this.status = 'pending',
    this.adminNotes,
    this.createdAt,
    this.resolvedAt,
    this.user,
  });

  String get statusLabel => AppConstants.statusLabelsId[status] ?? status;

  String get categoryLabel => AppConstants.categoryLabelsId[wasteCategory] ?? wasteCategory;

  String get formattedConfidence {
    if (aiConfidence == null) return 'N/A';
    return '${(aiConfidence! * 100).toStringAsFixed(1)}%';
  }

  String? get resolvedImageUrl {
    if (imageUrl != null && imageUrl!.isNotEmpty) {
      return imageUrl;
    }
    if (imagePath != null && imagePath!.isNotEmpty) {
      final cleanPath = imagePath!.startsWith('/') ? imagePath!.substring(1) : imagePath!;
      return '${AppConstants.storageBaseUrl}/$cleanPath';
    }
    return null;
  }

  factory ReportModel.fromJson(Map<String, dynamic> json) {
    return ReportModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id'].toString()) ?? 0,
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      imagePath: json['image_path'] as String?,
      imageUrl: json['image_url'] as String?,
      latitude: json['latitude'] is num ? (json['latitude'] as num).toDouble() : double.tryParse(json['latitude'].toString()) ?? 0.0,
      longitude: json['longitude'] is num ? (json['longitude'] as num).toDouble() : double.tryParse(json['longitude'].toString()) ?? 0.0,
      addressNote: json['address_note'] as String?,
      wasteCategory: json['waste_category'] as String? ?? 'Unclassified',
      aiConfidence: json['ai_confidence'] != null ? (json['ai_confidence'] as num).toDouble() : null,
      status: json['status'] as String? ?? 'pending',
      adminNotes: json['admin_notes'] as String?,
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) : null,
      resolvedAt: json['resolved_at'] != null ? DateTime.tryParse(json['resolved_at'].toString()) : null,
      user: json['user'] is Map<String, dynamic> ? UserModel.fromJson(json['user']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'title': title,
      'description': description,
      'image_path': imagePath,
      'image_url': imageUrl,
      'latitude': latitude,
      'longitude': longitude,
      'address_note': addressNote,
      'waste_category': wasteCategory,
      'ai_confidence': aiConfidence,
      'status': status,
      'admin_notes': adminNotes,
      'created_at': createdAt?.toIso8601String(),
      'resolved_at': resolvedAt?.toIso8601String(),
      'user': user?.toJson(),
    };
  }
}
