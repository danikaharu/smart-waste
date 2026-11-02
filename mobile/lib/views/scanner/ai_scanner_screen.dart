import 'package:flutter/material.dart';

class AiScannerScreen extends StatelessWidget {
  const AiScannerScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Deteksi Sampah AI')),
      body: const Center(child: Text('AI Scanner')),
    );
  }
}
