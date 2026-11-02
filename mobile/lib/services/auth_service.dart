import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/models/user_model.dart';
import 'package:smart_waste_mobile/services/api_service.dart';

class AuthService {
  final ApiService apiService;

  AuthService({required this.apiService});

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await apiService.post('/login', {
      'email': email,
      'password': password,
    }, requiresAuth: false);

    final data = response['data'];
    final token = data['token'] as String;
    final user = UserModel.fromJson(data['user']);

    await apiService.saveToken(token);
    await _saveCachedUser(user);

    return {'user': user, 'token': token};
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    final response = await apiService.post('/register', {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'phone': phone,
    }, requiresAuth: false);

    final data = response['data'];
    final token = data['token'] as String;
    final user = UserModel.fromJson(data['user']);

    await apiService.saveToken(token);
    await _saveCachedUser(user);

    return {'user': user, 'token': token};
  }

  Future<void> logout() async {
    try {
      await apiService.post('/logout', {});
    } catch (_) {
      // Ignore network errors on logout
    } finally {
      await apiService.removeToken();
    }
  }

  Future<UserModel?> getProfile() async {
    try {
      final response = await apiService.get('/user');
      if (response != null && response['data'] != null) {
        final user = UserModel.fromJson(response['data']['user']);
        await _saveCachedUser(user);
        return user;
      }
    } catch (_) {
      // Fallback to cached user if offline
      return getCachedUser();
    }
    return null;
  }

  Future<void> _saveCachedUser(UserModel user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(AppConstants.prefUserKey, jsonEncode(user.toJson()));
  }

  Future<UserModel?> getCachedUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userStr = prefs.getString(AppConstants.prefUserKey);
    if (userStr != null) {
      try {
        return UserModel.fromJson(jsonDecode(userStr));
      } catch (_) {}
    }
    return null;
  }
}
