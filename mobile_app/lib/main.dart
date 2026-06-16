import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'core/i18n/strings.dart';
import 'core/theme/app_theme.dart';
import 'features/app/app_state.dart';
import 'features/auth/auth_screen.dart';
import 'features/bookings/bookings_screen.dart';
import 'features/chat/chat_screen.dart';
import 'features/checkout/checkout_screen.dart';
import 'features/events/event_details_screen.dart';
import 'features/events/events_screen.dart';
import 'features/home/home_screen.dart';
import 'features/onboarding/onboarding_screen.dart';
import 'features/profile/profile_screen.dart';
import 'features/resale/resale_screen.dart';

void main() {
  runApp(const ProviderScope(child: AseerApp()));
}

class AseerApp extends ConsumerWidget {
  const AseerApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settings = ref.watch(settingsProvider);
    final auth = ref.watch(authProvider);

    if (!settings.ready || !auth.ready) {
      return MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        darkTheme: AppTheme.dark(),
        themeAnimationDuration: Duration.zero,
        home: const Scaffold(body: Center(child: CircularProgressIndicator())),
      );
    }

    final router = GoRouter(
      initialLocation: settings.hasSeenOnboarding ? '/' : '/onboarding',
      redirect: (context, state) {
        final protected = state.matchedLocation == '/bookings' ||
            state.matchedLocation == '/checkout' ||
            state.matchedLocation == '/resale';
        if (protected && !auth.isAuthenticated) {
          return '/auth';
        }
        return null;
      },
      routes: [
        ShellRoute(
          builder: (context, state, child) => AppShell(child: child),
          routes: [
            GoRoute(path: '/', builder: (context, state) => const HomeScreen()),
            GoRoute(
                path: '/events',
                builder: (context, state) => const EventsScreen()),
            GoRoute(
                path: '/chat', builder: (context, state) => const ChatScreen()),
            GoRoute(
                path: '/resale',
                builder: (context, state) => const ResaleScreen()),
            GoRoute(
                path: '/bookings',
                builder: (context, state) => const BookingsScreen()),
            GoRoute(
                path: '/profile',
                builder: (context, state) => const ProfileScreen()),
          ],
        ),
        GoRoute(
            path: '/onboarding',
            builder: (context, state) => const OnboardingScreen()),
        GoRoute(path: '/auth', builder: (context, state) => const AuthScreen()),
        GoRoute(
          path: '/events/:slug',
          builder: (context, state) =>
              EventDetailsScreen(slug: state.pathParameters['slug']!),
        ),
        GoRoute(
          path: '/checkout',
          builder: (context, state) => CheckoutScreen(
              event: Map<String, dynamic>.from(state.extra as Map? ?? {})),
        ),
      ],
    );

    return MaterialApp.router(
      debugShowCheckedModeBanner: false,
      title: 'منصة عسير',
      theme: AppTheme.light(),
      darkTheme: AppTheme.dark(),
      themeMode: settings.themeMode,
      themeAnimationDuration: Duration.zero,
      locale: settings.locale,
      builder: (context, child) => Directionality(
        textDirection: settings.locale.languageCode == 'ar'
            ? TextDirection.rtl
            : TextDirection.ltr,
        child: child ?? const SizedBox.shrink(),
      ),
      supportedLocales: const [Locale('ar'), Locale('en')],
      localizationsDelegates: const [
        AppStrings.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      routerConfig: router,
    );
  }
}

class AppShell extends StatefulWidget {
  const AppShell({required this.child, super.key});

  final Widget child;

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  int index = 0;

  @override
  Widget build(BuildContext context) {
    final location = GoRouterState.of(context).matchedLocation;
    final paths = ['/', '/chat', '/resale', '/bookings', '/profile'];
    final s = AppStrings.of(context);
    index = paths.indexWhere((path) => path == location);
    if (index < 0) index = 0;

    return Scaffold(
      body: widget.child,
      bottomNavigationBar: DecoratedBox(
        decoration: const BoxDecoration(
          color: Color(0xFF050608),
          border: Border(top: BorderSide(color: Color(0xFF1E2028))),
        ),
        child: SafeArea(
          top: false,
          child: NavigationBar(
            selectedIndex: index,
            onDestinationSelected: (value) {
              setState(() => index = value);
              context.go(paths[value]);
            },
            destinations: [
              NavigationDestination(
                  icon: const Icon(Icons.home_outlined),
                  selectedIcon: const Icon(Icons.home),
                  label: s.discover),
              NavigationDestination(
                  icon: const Icon(Icons.chat_bubble_outline),
                  selectedIcon: const Icon(Icons.chat_bubble),
                  label: s.chat),
              NavigationDestination(
                  icon: const Icon(Icons.swap_horiz),
                  selectedIcon: const Icon(Icons.swap_horiz),
                  label: s.resale),
              NavigationDestination(
                  icon: const Icon(Icons.confirmation_number_outlined),
                  selectedIcon: const Icon(Icons.confirmation_number),
                  label: s.bookings),
              NavigationDestination(
                  icon: _AccountIcon(selected: index == 4),
                  selectedIcon: const _AccountIcon(selected: true),
                  label: s.account),
            ],
          ),
        ),
      ),
    );
  }
}

class _AccountIcon extends StatelessWidget {
  const _AccountIcon({required this.selected});

  final bool selected;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: selected ? const Color(0xFFC8F000) : Colors.transparent,
        shape: BoxShape.circle,
        border: Border.all(
            color:
                selected ? const Color(0xFFC8F000) : const Color(0xFF8F92A1)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(5),
        child: Text(
          'عسير',
          style: TextStyle(
              color:
                  selected ? const Color(0xFF151515) : const Color(0xFF8F92A1),
              fontSize: 9,
              fontWeight: FontWeight.w900),
        ),
      ),
    );
  }
}
