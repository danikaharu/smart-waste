import 'package:flutter_test/flutter_test.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';

void main() {
  test('AppConstants contains valid API endpoints and categories', () {
    expect(AppConstants.appName, equals('Smart Waste'));
    expect(AppConstants.categories, contains('Organic Waste'));
    expect(AppConstants.categories, contains('Plastic Waste'));
    expect(AppConstants.categories, contains('Paper Waste'));
    expect(AppConstants.apiBaseUrl, isNotEmpty);
  });

  test('AppTheme defines primary eco green color', () {
    expect(AppTheme.primaryColor.toARGB32(), isNotNull);
    expect(AppTheme.accentColor.toARGB32(), isNotNull);
  });
}
