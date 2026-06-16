import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  ApiClient({String? baseUrl})
      : baseUrl = baseUrl ??
            const String.fromEnvironment('API_BASE_URL',
                defaultValue: 'http://127.0.0.1:8000/api/v1'),
        dio = Dio(BaseOptions(
          baseUrl: baseUrl ??
              const String.fromEnvironment('API_BASE_URL',
                  defaultValue: 'http://127.0.0.1:8000/api/v1'),
          connectTimeout: const Duration(seconds: 6),
          receiveTimeout: const Duration(seconds: 10),
          headers: {'Accept': 'application/json'},
        )) {
    dio.interceptors
        .add(InterceptorsWrapper(onRequest: (options, handler) async {
      final token = await tokenValue;
      if (token != null && token.isNotEmpty) {
        options.headers['Authorization'] = 'Bearer $token';
      }
      handler.next(options);
    }));
  }

  final Dio dio;
  final String baseUrl;

  String get platformBaseUrl => baseUrl.replaceFirst(RegExp(r'/api/v1/?$'), '');
  String get logoUrl {
    final logo =
        Uri.encodeComponent('$platformBaseUrl/branding/aseer-logo.png');
    return '$platformBaseUrl/api/v1/images/proxy?url=$logo';
  }

  Future<String?> get tokenValue async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);
  }

  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
  }
}
