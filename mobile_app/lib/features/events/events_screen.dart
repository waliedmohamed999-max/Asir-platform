import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/i18n/strings.dart';
import '../../shared/widgets/async_view.dart';
import '../../shared/widgets/event_card.dart';
import '../app/app_state.dart';

class EventsScreen extends ConsumerWidget {
  const EventsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final category = GoRouterState.of(context).uri.queryParameters['category'];
    final events = ref.watch(eventsProvider(category));
    final s = AppStrings.of(context);

    return Scaffold(
      appBar: AppBar(title: Text(s.events)),
      body: AsyncView<List<dynamic>>(
        value: events,
        builder: (data) => RefreshIndicator(
          onRefresh: () => ref.refresh(eventsProvider(category).future),
          child: ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: data.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (context, index) =>
                EventCard(event: Map<String, dynamic>.from(data[index] as Map)),
          ),
        ),
      ),
    );
  }
}
