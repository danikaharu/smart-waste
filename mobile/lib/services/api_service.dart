import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;

  ApiException(this.message, [this.statusCode]);

  @override
  String toString() => message;
}

class ApiService {
  final http.Client client;

  ApiService({http.Client? client}) : client = client ?? http.Client();

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(AppConstants.prefTokenKey);
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(AppConstants.prefTokenKey, token);
  }

  Future<void> removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(AppConstants.prefTokenKey);
    await prefs.remove(AppConstants.prefUserKey);
  }

  Future<Map<String, String>> _headers({bool requiresAuth = true}) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (requiresAuth) {
      final token = await getToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    return headers;
  }

  Future<dynamic> get(String endpoint, {bool requiresAuth = true, Map<String, dynamic>? queryParams}) async {
    var uriString = '${AppConstants.apiBaseUrl}$endpoint';
    if (queryParams != null && queryParams.isNotEmpty) {
      final query = queryParams.entries
          .where((e) => e.value != null && e.value.toString().isNotEmpty)
          .map((e) => '${Uri.encodeComponent(e.key)}=${Uri.encodeComponent(e.value.toString())}')
          .join('&');
      if (query.isNotEmpty) {
        uriString += '?$query';
      }
    }

    final uri = Uri.parse(uriString);
    final headers = await _headers(requiresAuth: requiresAuth);

    try {
      final response = await client.get(uri, headers: headers);
      return _processResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Gagal terhubung ke server: ${e.toString()}');
    }
  }

  Future<dynamic> post(String endpoint, Map<String, dynamic> body, {bool requiresAuth = true}) async {
    final uri = Uri.parse('${AppConstants.apiBaseUrl}$endpoint');
    final headers = await _headers(requiresAuth: requiresAuth);

    try {
      final response = await client.post(uri, headers: headers, body: jsonEncode(body));
      return _processResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Gagal terhubung ke server: ${e.toString()}');
    }
  }

  Future<dynamic> postMultipart(
    String endpoint, {
    required Map<String, String> fields,
    required String fileField,
    required List<int> fileBytes,
    required String filename,
    bool requiresAuth = true,
  }) async {
    final uri = Uri.parse('${AppConstants.apiBaseUrl}$endpoint');
    final request = http.MultipartRequest('POST', uri);

    if (requiresAuth) {
      final token = await getToken();
      if (token != null && token.isNotEmpty) {
        request.headers['Authorization'] = 'Bearer $token';
      }
    }
    request.headers['Accept'] = 'application/json';

    request.fields.addAll(fields);

    request.files.add(
      http.MultipartFile.fromBytes(
        fileField,
        fileBytes,
        filename: filename,
      ),
    );

    try {
      final streamedResponse = await client.send(request);
      final response = await http.Response.fromStream(streamedResponse);
      return _processResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Gagal mengunggah data ke server: ${e.toString()}');
    }
  }

  dynamic _processResponse(http.Response response) {
    dynamic body;
    try {
      body = jsonDecode(response.body);
    } catch (_) {
      body = null;
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }

    if (response.statusCode == 401) {
      removeToken();
      throw ApiException('Sesi telah berakhir, silakan login kembali.', 401);
    }

    if (body is Map && body.containsKey('message')) {
      throw ApiException(body['message'].toString(), response.statusCode);
    }

    throw ApiException('Terjadi kesalahan pada server (${response.statusCode})', response.statusCode);
  }
}
