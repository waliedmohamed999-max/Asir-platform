import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../core/network/api_client.dart';

final apiClientProvider = Provider<ApiClient>((ref) => ApiClient());

final settingsProvider =
    StateNotifierProvider<SettingsController, AppSettings>((ref) {
  return SettingsController()..load();
});

final authProvider = StateNotifierProvider<AuthController, AuthState>((ref) {
  return AuthController(ref.watch(apiClientProvider))..load();
});

class AppSettings {
  const AppSettings({
    this.locale = const Locale('ar'),
    this.themeMode = ThemeMode.dark,
    this.hasSeenOnboarding = false,
    this.ready = false,
  });

  final Locale locale;
  final ThemeMode themeMode;
  final bool hasSeenOnboarding;
  final bool ready;

  AppSettings copyWith(
      {Locale? locale,
      ThemeMode? themeMode,
      bool? hasSeenOnboarding,
      bool? ready}) {
    return AppSettings(
      locale: locale ?? this.locale,
      themeMode: themeMode ?? this.themeMode,
      hasSeenOnboarding: hasSeenOnboarding ?? this.hasSeenOnboarding,
      ready: ready ?? this.ready,
    );
  }
}

class SettingsController extends StateNotifier<AppSettings> {
  SettingsController() : super(const AppSettings());

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final lang = prefs.getString('language') ?? 'ar';
    final theme = prefs.getString('theme') ?? 'dark';
    state = state.copyWith(
      locale: Locale(lang),
      themeMode: theme == 'dark'
          ? ThemeMode.dark
          : theme == 'light'
              ? ThemeMode.light
              : ThemeMode.system,
      hasSeenOnboarding: prefs.getBool('has_seen_onboarding') ?? false,
      ready: true,
    );
  }

  Future<void> setLocale(Locale locale) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('language', locale.languageCode);
    state = state.copyWith(locale: locale);
  }

  Future<void> toggleLocale() {
    return setLocale(state.locale.languageCode == 'ar'
        ? const Locale('en')
        : const Locale('ar'));
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('theme', mode.name);
    state = state.copyWith(themeMode: mode);
  }

  Future<void> toggleTheme() {
    return setThemeMode(
        state.themeMode == ThemeMode.dark ? ThemeMode.light : ThemeMode.dark);
  }

  Future<void> completeOnboarding() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('has_seen_onboarding', true);
    state = state.copyWith(hasSeenOnboarding: true);
  }
}

class AuthState {
  const AuthState(
      {this.ready = false, this.isAuthenticated = false, this.user});

  final bool ready;
  final bool isAuthenticated;
  final Map<String, dynamic>? user;

  AuthState copyWith(
      {bool? ready, bool? isAuthenticated, Map<String, dynamic>? user}) {
    return AuthState(
      ready: ready ?? this.ready,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      user: user ?? this.user,
    );
  }
}

class AuthController extends StateNotifier<AuthState> {
  AuthController(this.api) : super(const AuthState());

  final ApiClient api;

  Future<void> load() async {
    final token = await api.tokenValue;
    if (token == null || token.isEmpty) {
      state = const AuthState(ready: true);
      return;
    }

    try {
      final response = await api.dio.get('/auth/me');
      state = AuthState(
          ready: true,
          isAuthenticated: true,
          user: Map<String, dynamic>.from(response.data['data'] as Map));
    } catch (_) {
      await api.clearToken();
      state = const AuthState(ready: true);
    }
  }

  Future<void> login({required String login, required String password}) async {
    final response = await api.dio.post('/auth/login', data: {
      'login': login,
      'password': password,
      'device_name': 'flutter',
    });
    await api.saveToken(response.data['access_token'] as String);
    state = AuthState(
        ready: true,
        isAuthenticated: true,
        user: _userFrom(response.data['user']));
  }

  Future<void> register(
      {required String name,
      required String email,
      String? phone,
      required String password}) async {
    final response = await api.dio.post('/auth/register', data: {
      'name': name,
      'email': email,
      'phone': phone,
      'password': password,
      'password_confirmation': password,
    });
    await api.saveToken(response.data['access_token'] as String);
    state = AuthState(
        ready: true,
        isAuthenticated: true,
        user: _userFrom(response.data['user']));
  }

  Future<void> refreshProfile() => load();

  Future<void> logout() async {
    try {
      await api.dio.post('/auth/logout');
    } catch (_) {
      // Token may already be invalid; local logout still wins.
    }
    await api.clearToken();
    state = const AuthState(ready: true);
  }

  Map<String, dynamic> _userFrom(dynamic value) {
    if (value is Map && value['data'] is Map) {
      return Map<String, dynamic>.from(value['data'] as Map);
    }
    return Map<String, dynamic>.from(value as Map);
  }
}

final homeProvider =
    FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ref.watch(apiClientProvider);
  final lang = ref.watch(settingsProvider).locale.languageCode;
  try {
    final response = await api.dio.get('/home', queryParameters: {
      'lang': lang,
      '_': DateTime.now().millisecondsSinceEpoch,
    });
    return Map<String, dynamic>.from(response.data as Map);
  } catch (_) {
    return _fallbackHome();
  }
});

final eventsProvider = FutureProvider.autoDispose
    .family<List<dynamic>, String?>((ref, category) async {
  final api = ref.watch(apiClientProvider);
  final lang = ref.watch(settingsProvider).locale.languageCode;
  try {
    final response = await api.dio.get('/events', queryParameters: {
      'lang': lang,
      if (category != null && category.isNotEmpty) 'category': category,
    });
    return List<dynamic>.from(response.data['data'] as List);
  } catch (_) {
    final events = _fallbackEvents();
    if (category == null || category.isEmpty) {
      return events;
    }
    return events
        .where((event) =>
            event is Map &&
            event['category'] is Map &&
            event['category']['slug'] == category)
        .toList();
  }
});

Map<String, dynamic> _fallbackHome() {
  final events = _fallbackEvents().cast<Map<String, dynamic>>();
  return {
    'banners': [
      {
        'title': 'أكوارابيا - مدينة الألعاب المائية',
        'subtitle': 'تجربة عائلية في الرياض',
        'badge': 'منصة عسير',
      },
      {
        'title': 'حفلات عيد الأضحى 2026',
        'subtitle': 'ليلة موسيقية لا تنسى',
        'badge': 'الأكثر مبيعاً',
      },
    ],
    'quick_actions': [],
    'trending_searches': ['حفلات', 'مطاعم', 'الرياضة', 'اليوم'],
    'sections': {
      'recommended': events,
      'trending': events.reversed.toList(),
      'today': events.take(2).toList(),
      'upcoming': events,
      'offers': [],
      'services': [],
    },
  };
}

List<dynamic> _fallbackEvents() {
  final now = DateTime.now();
  return [
    {
      'slug': 'aquarabia',
      'title': 'أكوارابيا - مدينة الألعاب المائية',
      'category': {'slug': 'experiences', 'name': 'التجارب'},
      'venue_name': 'الرياض',
      'starts_at': now.add(const Duration(days: 1)).toIso8601String(),
      'starting_price': 120,
      'is_featured': true,
    },
    {
      'slug': 'angham-eid-2026',
      'title': 'حفل أنغام - حفلات عيد الأضحى 2026',
      'category': {'slug': 'concerts', 'name': 'الحفلات'},
      'venue_name': 'Riyadh',
      'starts_at': now.add(const Duration(days: 6)).toIso8601String(),
      'starting_price': 250,
      'is_featured': true,
    },
    {
      'slug': 'webook-fun-restaurants',
      'title': 'WE BOOK FUN RESTAURANTS',
      'category': {'slug': 'restaurants', 'name': 'المطاعم'},
      'venue_name': 'بوليفارد',
      'starts_at': now.add(const Duration(days: 3)).toIso8601String(),
      'starting_price': 0,
      'is_featured': false,
    },
    {
      'slug': 'flying-over',
      'title': 'فلاينق اوفر',
      'category': {'slug': 'aviation', 'name': 'الطيران'},
      'venue_name': 'موسم الرياض',
      'starts_at': now.add(const Duration(days: 9)).toIso8601String(),
      'starting_price': 95,
      'is_featured': false,
    },
  ];
}
