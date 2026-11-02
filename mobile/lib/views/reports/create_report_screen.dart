import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/models/ai_detection_model.dart';
import 'package:smart_waste_mobile/providers/ai_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/services/location_service.dart';
import 'package:smart_waste_mobile/widgets/custom_button.dart';
import 'package:smart_waste_mobile/widgets/custom_text_field.dart';
import 'package:smart_waste_mobile/widgets/map_preview.dart';

class CreateReportScreen extends StatefulWidget {
  final Uint8List? initialImageBytes;
  final String? initialImageName;
  final AiDetectionModel? initialDetection;

  const CreateReportScreen({
    super.key,
    this.initialImageBytes,
    this.initialImageName,
    this.initialDetection,
  });

  @override
  State<CreateReportScreen> createState() => _CreateReportScreenState();
}

class _CreateReportScreenState extends State<CreateReportScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _addressController = TextEditingController();

  Uint8List? _imageBytes;
  String? _imageName;
  String _selectedCategory = 'Organic Waste';
  double? _aiConfidence;
  bool _isAutoDetected = false;

  double _latitude = AppConstants.defaultLatitude;
  double _longitude = AppConstants.defaultLongitude;
  bool _isFetchingLocation = false;
  String? _locationStatus;

  final ImagePicker _picker = ImagePicker();
  final LocationService _locationService = LocationService();

  @override
  void initState() {
    super.initState();
    if (widget.initialImageBytes != null) {
      _imageBytes = widget.initialImageBytes;
      _imageName = widget.initialImageName ?? 'waste.jpg';
    }
    if (widget.initialDetection != null) {
      _selectedCategory = widget.initialDetection!.label;
      _aiConfidence = widget.initialDetection!.confidence;
      _isAutoDetected = true;
    }
    _getCurrentLocation();
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _getCurrentLocation() async {
    setState(() {
      _isFetchingLocation = true;
      _locationStatus = 'Mendapatkan sinyal GPS...';
    });

    final result = await _locationService.getCurrentLocation();

    if (!mounted) return;

    setState(() {
      _latitude = result.latitude;
      _longitude = result.longitude;
      _isFetchingLocation = false;
      _locationStatus = result.isFallback
          ? 'Lokasi default (GPS tidak aktif)'
          : 'Koordinat GPS akurat';
    });
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? file = await _picker.pickImage(
        source: source,
        maxWidth: 1200,
        maxHeight: 1200,
        imageQuality: 85,
      );

      if (file == null) return;

      final bytes = await file.readAsBytes();
      final name = file.name;

      setState(() {
        _imageBytes = bytes;
        _imageName = name;
        _isAutoDetected = false;
      });

      if (!mounted) return;

      // Automatically classify with AI
      final aiProvider = Provider.of<AiProvider>(context, listen: false);
      final detection = await aiProvider.classifyImage(bytes, name);

      if (!mounted) return;

      if (detection != null) {
        setState(() {
          _selectedCategory = detection.label;
          _aiConfidence = detection.confidence;
          _isAutoDetected = true;
          if (_titleController.text.trim().isEmpty) {
            _titleController.text = 'Laporan Sampah ${_getFriendlyCategory(detection.label)}';
          }
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memilih foto: $e'), backgroundColor: AppTheme.statusRejected),
      );
    }
  }

  String _getFriendlyCategory(String label) {
    switch (label) {
      case 'Organic Waste':
        return 'Organik';
      case 'Plastic Waste':
        return 'Plastik';
      case 'Paper Waste':
        return 'Kertas';
      default:
        return 'Umum';
    }
  }

  Future<void> _submitReport() async {
    if (_imageBytes == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Mohon lampirkan foto sampah terlebih dahulu'),
          backgroundColor: AppTheme.statusRejected,
        ),
      );
      return;
    }

    if (!_formKey.currentState!.validate()) return;

    final reportProvider = Provider.of<ReportProvider>(context, listen: false);
    final report = await reportProvider.submitReport(
      title: _titleController.text.trim(),
      description: _descriptionController.text.trim(),
      imageBytes: _imageBytes!,
      imageName: _imageName ?? 'waste_report.jpg',
      latitude: _latitude,
      longitude: _longitude,
      addressNote: _addressController.text.trim(),
      wasteCategory: _selectedCategory,
    );

    if (!mounted) return;

    if (report != null) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(
                  color: AppTheme.primaryLight,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded, color: AppTheme.primaryColor, size: 48),
              ),
              const SizedBox(height: 16),
              const Text(
                'Laporan Terkirim!',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 8),
              const Text(
                'Terima kasih telah berkontribusi menjaga kebersihan lingkungan. Laporan Anda sedang menunggu verifikasi petugas.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
              ),
            ],
          ),
          actions: [
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pop(context);
                },
                child: const Text('Kembali ke Beranda'),
              ),
            ),
          ],
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(reportProvider.errorMessage ?? 'Gagal mengirim laporan.'),
          backgroundColor: AppTheme.statusRejected,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final aiProvider = Provider.of<AiProvider>(context);
    final reportProvider = Provider.of<ReportProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Buat Laporan Sampah'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Photo Section
              const Text(
                '1. Foto Sampah',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 8),

              if (_imageBytes != null)
                ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Stack(
                    children: [
                      Image.memory(
                        _imageBytes!,
                        height: 220,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                      Positioned(
                        top: 10,
                        right: 10,
                        child: CircleAvatar(
                          backgroundColor: Colors.black.withValues(alpha: 0.6),
                          child: IconButton(
                            icon: const Icon(Icons.close, color: Colors.white, size: 20),
                            onPressed: () {
                              setState(() {
                                _imageBytes = null;
                                _aiConfidence = null;
                                _isAutoDetected = false;
                              });
                            },
                          ),
                        ),
                      ),
                    ],
                  ),
                )
              else
                Container(
                  width: double.infinity,
                  height: 160,
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFCBD5E1), style: BorderStyle.solid),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.add_a_photo_outlined, size: 40, color: Color(0xFF94A3B8)),
                      const SizedBox(height: 10),
                      const Text(
                        'Lampirkan foto sampah di lokasi',
                        style: TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          ElevatedButton.icon(
                            onPressed: () => _pickImage(ImageSource.camera),
                            icon: const Icon(Icons.camera_alt_outlined, size: 16),
                            label: const Text('Ambil Foto', style: TextStyle(fontSize: 13)),
                            style: ElevatedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            ),
                          ),
                          const SizedBox(width: 10),
                          OutlinedButton.icon(
                            onPressed: () => _pickImage(ImageSource.gallery),
                            icon: const Icon(Icons.photo_library_outlined, size: 16),
                            label: const Text('Galeri', style: TextStyle(fontSize: 13)),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

              // AI Detection Status Banner
              if (aiProvider.isAnalyzing) ...[
                const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                  ),
                  child: const Row(
                    children: [
                      SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.statusInProgress),
                      ),
                      SizedBox(width: 10),
                      Text(
                        'AI sedang menganalisis jenis sampah...',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.statusInProgress),
                      ),
                    ],
                  ),
                ),
              ] else if (_isAutoDetected && _aiConfidence != null) ...[
                const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryLight,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.auto_awesome_rounded, color: AppTheme.primaryColor, size: 18),
                          const SizedBox(width: 8),
                          const Text(
                            'Terdeteksi Otomatis oleh AI:',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.primaryDark),
                          ),
                          const Spacer(),
                          Text(
                            '${(_aiConfidence! * 100).toStringAsFixed(1)}% Yakin',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.primaryColor),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        AppConstants.categoryLabelsId[_selectedCategory] ?? _selectedCategory,
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Color(0xFF065F46)),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 24),

              // Category Selection Dropdown
              const Text(
                'Kategori Sampah',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
              ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: _selectedCategory,
                    isExpanded: true,
                    items: AppConstants.categories.map((cat) {
                      return DropdownMenuItem(
                        value: cat,
                        child: Text(
                          AppConstants.categoryLabelsId[cat] ?? cat,
                          style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A)),
                        ),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) {
                        setState(() {
                          _selectedCategory = val;
                        });
                      }
                    },
                  ),
                ),
              ),
              const SizedBox(height: 18),

              // Title Field
              CustomTextField(
                controller: _titleController,
                label: 'Judul Laporan',
                hint: 'Contoh: Tumpukan Botol Plastik di Pinggir Jalan',
                validator: (val) => val == null || val.trim().isEmpty ? 'Judul laporan wajib diisi' : null,
              ),
              const SizedBox(height: 18),

              // Description Field
              CustomTextField(
                controller: _descriptionController,
                label: 'Deskripsi Tambahan',
                hint: 'Jelaskan kondisi sampah (jumlah perkiraan, bau, hambatan jalan)',
                maxLines: 3,
              ),
              const SizedBox(height: 24),

              // GPS Geolocation Section
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '2. Lokasi GPS Sampah',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                  ),
                  TextButton.icon(
                    onPressed: _isFetchingLocation ? null : _getCurrentLocation,
                    icon: _isFetchingLocation
                        ? const SizedBox(
                            width: 14,
                            height: 14,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.my_location_rounded, size: 16),
                    label: const Text('Dapatkan Lokasi GPS', style: TextStyle(fontSize: 12)),
                  ),
                ],
              ),
              const SizedBox(height: 6),

              // Interactive OpenStreetMap Preview
              MapPreview(
                latitude: _latitude,
                longitude: _longitude,
                height: 180,
                addressNote: 'Lat: ${_latitude.toStringAsFixed(5)}, Long: ${_longitude.toStringAsFixed(5)}',
              ),
              const SizedBox(height: 8),

              if (_locationStatus != null)
                Text(
                  _locationStatus!,
                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                ),
              const SizedBox(height: 14),

              // Address Landmark Field
              CustomTextField(
                controller: _addressController,
                label: 'Patokan Alamat / Landmark',
                hint: 'Contoh: Depan warung kopi Bu Sri / samping tiang listrik No. 12',
              ),
              const SizedBox(height: 32),

              // Submit Button
              CustomButton(
                text: 'Kirim Laporan Sampah',
                isLoading: reportProvider.isSubmitting,
                onPressed: _submitReport,
                icon: Icons.send_rounded,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
