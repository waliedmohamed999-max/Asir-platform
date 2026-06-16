import 'package:flutter/material.dart';

class AppTokens {
  static const double radius = 8;
  static const double gap = 16;
  static const Duration motion = Duration(milliseconds: 260);

  static const List<Color> heroGradient = [
    Color(0xCC000000),
    Color(0x33000000),
    Color(0x00000000),
  ];

  static BoxShadow softShadow(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return BoxShadow(
      color: Colors.black.withOpacity(isDark ? .28 : .08),
      blurRadius: 24,
      offset: const Offset(0, 12),
    );
  }
}
