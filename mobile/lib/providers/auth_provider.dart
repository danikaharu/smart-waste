import 'package:flutter/foundation.dart';
import 'package:smart_waste_mobile/models/user_model.dart';
import 'package:smart_waste_mobile/services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  final AuthService authService;

  UserModel? _currentUser;
  bool _isLoading = false;
  String? _errorMessage;

  AuthProvider({required this.authService});

  UserModel? get currentUser => _currentUser;
  bool get isAuthenticated => _currentUser != null;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  Future<bool> checkAuthStatus() async {
    try {
      final user = await authService.getProfile();
      _currentUser = user;
      notifyListeners();
      return _currentUser != null;
    } catch (_) {
      _currentUser = null;
      notifyListeners();
      return false;
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final result = await authService.login(email, password);
      _currentUser = result['user'] as UserModel;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final result = await authService.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        phone: phone,
      );
      _currentUser = result['user'] as UserModel;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    await authService.logout();
    _currentUser = null;
    _isLoading = false;
    notifyListeners();
  }
}
