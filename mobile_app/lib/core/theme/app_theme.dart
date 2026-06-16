import 'package:flutter/material.dart';

class AppTheme {
  static const _seed = Color(0xFFFF2D7A);
  static const _pink = Color(0xFFFF2D7A);
  static const _lime = Color(0xFFC8F000);

  static ThemeData light() {
    return ThemeData(
      useMaterial3: true,
      colorScheme:
          ColorScheme.fromSeed(seedColor: _seed, brightness: Brightness.light),
      scaffoldBackgroundColor: const Color(0xFFF7F8FA),
      fontFamily: 'Roboto',
      cardTheme: const CardThemeData(elevation: 0, margin: EdgeInsets.zero),
      searchBarTheme: SearchBarThemeData(
        elevation: const WidgetStatePropertyAll(0),
        shape: WidgetStatePropertyAll(
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(8))),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 72,
        indicatorShape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  static ThemeData dark() {
    final scheme =
        ColorScheme.fromSeed(seedColor: _seed, brightness: Brightness.dark)
            .copyWith(
      primary: _pink,
      secondary: _lime,
      surface: const Color(0xFF090A0D),
      surfaceContainer: const Color(0xFF111216),
      surfaceContainerHighest: const Color(0xFF1A1B20),
      outline: const Color(0xFF343640),
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: const Color(0xFF07080A),
      fontFamily: 'Roboto',
      appBarTheme: const AppBarTheme(
        centerTitle: true,
        elevation: 0,
        scrolledUnderElevation: 0,
        backgroundColor: Color(0xFF07080A),
        foregroundColor: Color(0xFFF5F5F7),
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        margin: EdgeInsets.zero,
        color: const Color(0xFF111216),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
          side: const BorderSide(color: Color(0xFF2B2D35)),
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: const Color(0xFF191A20),
        selectedColor: _pink,
        side: BorderSide.none,
        labelStyle: const TextStyle(
            color: Color(0xFFF5F5F7), fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: _pink,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          textStyle: const TextStyle(fontWeight: FontWeight.w900),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: Colors.white,
          side: const BorderSide(color: Color(0xFFE8E8EE)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
      ),
      searchBarTheme: SearchBarThemeData(
        elevation: const WidgetStatePropertyAll(0),
        backgroundColor: const WidgetStatePropertyAll(Color(0xFF111216)),
        shape: WidgetStatePropertyAll(
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(8))),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 78,
        backgroundColor: const Color(0xFF050608),
        indicatorColor: Colors.transparent,
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          final selected = states.contains(WidgetState.selected);
          return TextStyle(
            color: selected ? _pink : const Color(0xFF8F92A1),
            fontWeight: selected ? FontWeight.w900 : FontWeight.w700,
            fontSize: 12,
          );
        }),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          final selected = states.contains(WidgetState.selected);
          return IconThemeData(
              color: selected ? _pink : const Color(0xFF8F92A1), size: 28);
        }),
      ),
    );
  }
}
