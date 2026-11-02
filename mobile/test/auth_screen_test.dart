import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';
import 'package:smart_waste_mobile/providers/auth_provider.dart';
import 'package:smart_waste_mobile/services/api_service.dart';
import 'package:smart_waste_mobile/services/auth_service.dart';
import 'package:smart_waste_mobile/views/auth/login_screen.dart';

void main() {
  testWidgets('LoginScreen renders fields and quick demo fill button', (tester) async {
    final api = ApiService();
    final authService = AuthService(apiService: api);
    final authProvider = AuthProvider(authService: authService);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        ],
        child: MaterialApp(
          theme: AppTheme.lightTheme,
          home: const LoginScreen(),
        ),
      ),
    );

    // Verify presence of title and fields
    expect(find.text('Smart Waste'), findsWidgets);
    expect(find.byType(TextField), findsNWidgets(2));
    expect(find.text('Masuk'), findsOneWidget);

    // Verify quick demo button
    final demoBtn = find.text('Isi Akun Demo Warga');
    expect(demoBtn, findsOneWidget);

    // Tap demo fill button
    await tester.tap(demoBtn);
    await tester.pumpAndSettle();

    // Verify text controllers populated
    expect(find.text('user@smartwaste.com'), findsOneWidget);
  });
}
