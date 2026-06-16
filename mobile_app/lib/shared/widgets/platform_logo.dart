import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../features/app/app_state.dart';

class PlatformLogo extends ConsumerWidget {
  const PlatformLogo({this.size = 56, super.key});

  final double size;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final logoUrl = ref.watch(apiClientProvider).logoUrl;

    return ClipRRect(
      borderRadius: BorderRadius.circular(8),
      child: Image.network(
        logoUrl,
        width: size,
        height: size,
        fit: BoxFit.contain,
        errorBuilder: (_, __, ___) => Icon(Icons.terrain, size: size * .7),
      ),
    );
  }
}
