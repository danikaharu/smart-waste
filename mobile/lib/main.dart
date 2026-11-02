import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/providers/ai_provider.dart';
import 'package:smart_waste_mobile/providers/auth_provider.dart';
import 'package:smart_waste_mobile/providers/report_provider.dart';
import 'package:smart_waste_mobile/services/ai_service.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/auth_service.dart';
import 'package:smart_waste_mobile/services/report_service.dart';
import 'package:smart_waste_mobile/views/auth/login_screen.dart';
import 'package:smart_waste_mobile/views/dashboard/main_navigation_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  final apiService = ApiService();
  final authService = AuthService(apiService: apiService);
  final reportService = ReportService(apiService: apiService);
  final aiService = AiService(apiService: apiService);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider(authService: authService)),
        ChangeNotifierProvider(create: (_) => ReportProvider(reportService: reportService)),
        ChangeNotifierProvider(create: (_) => AiProvider(aiService: aiService)),
      ],
      child: const SmartWasteApp(),
    ),
  );
}

class SmartWasteApp extends StatefulWidget {
  const SmartWasteApp({super.key});

  @override
  State<SmartWasteApp> createState() => _SmartWasteAppState();
}

class _SmartWasteAppState extends State<SmartWasteApp> {
  late Future<bool> _authCheckFuture;

  @override
  void initState() {
    super.initState();
    _authCheckFuture = Provider.of<AuthProvider>(context, listen: false).checkAuthStatus();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: AppConstants.appName,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      home: FutureBuilder<bool>(
        future: _authCheckFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Scaffold(
              body: Center(
                child: CircularProgressIndicator(color: AppTheme.primaryColor),
              ),
            );
          }

          final isAuthenticated = snapshot.data ?? false;
          if (isAuthenticated) {
            return const MainNavigationScreen();
          } else {
            return const LoginScreen();
          }
        },
      ),
    );
  }
}
