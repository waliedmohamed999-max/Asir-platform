import 'dart:async';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../core/theme/app_tokens.dart';
import '../app/app_state.dart';

final eventDetailsProvider =
    FutureProvider.family<Map<String, dynamic>, String>((ref, slug) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.dio.get('/events/$slug');
  return Map<String, dynamic>.from(response.data['data'] as Map);
});

class EventDetailsScreen extends ConsumerWidget {
  const EventDetailsScreen({required this.slug, super.key});

  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final details = ref.watch(eventDetailsProvider(slug));

    return Scaffold(
      body: details.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stackTrace) => Center(child: Text(error.toString())),
        data: (event) => _EventDetails(event: event),
      ),
    );
  }
}

class _EventDetails extends StatelessWidget {
  const _EventDetails({required this.event});

  final Map<String, dynamic> event;

  @override
  Widget build(BuildContext context) {
    final image = event['banner_image_url'] ?? event['image_url'];
    final tickets =
        List<Map<String, dynamic>>.from(event['tickets'] as List? ?? []);
    final social = event['social'] is Map
        ? Map<String, dynamic>.from(event['social'] as Map)
        : <String, dynamic>{};

    return Scaffold(
      floatingActionButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingActionButton: SizedBox(
        width: MediaQuery.sizeOf(context).width - 32,
        child: FilledButton.icon(
          onPressed: () => context.push('/checkout', extra: event),
          icon: const Icon(Icons.lock),
          label: const Text('Book now'),
        ),
      ),
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            pinned: true,
            expandedHeight: 340,
            actions: [
              IconButton.filledTonal(
                  onPressed: () {}, icon: const Icon(Icons.favorite_border)),
              IconButton.filledTonal(
                  onPressed: () {}, icon: const Icon(Icons.ios_share)),
              const SizedBox(width: 8),
            ],
            flexibleSpace: FlexibleSpaceBar(
              title: Text(event['title'] ?? '',
                  maxLines: 1, overflow: TextOverflow.ellipsis),
              background: Stack(
                fit: StackFit.expand,
                children: [
                  if (image != null)
                    Hero(
                        tag: 'event-${event['slug']}',
                        child: CachedNetworkImage(
                            imageUrl: image,
                            fit: BoxFit.cover,
                            errorWidget: (_, __, ___) =>
                                const _ImageFallback())),
                  if (image == null) const _ImageFallback(),
                  const DecoratedBox(
                      decoration: BoxDecoration(
                          gradient: LinearGradient(
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                              colors: AppTokens.heroGradient))),
                ],
              ),
            ),
          ),
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 110),
            sliver: SliverList.list(
              children: [
                _MetaStrip(event: event, social: social),
                const SizedBox(height: 16),
                _Countdown(startsAt: event['starts_at']?.toString()),
                const SizedBox(height: 20),
                Text(event['excerpt'] ?? '',
                    style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 10),
                Text(event['description'] ?? ''),
                const SizedBox(height: 24),
                _SectionTitle('التذاكر والباقات'),
                const SizedBox(height: 10),
                if (tickets.isEmpty)
                  const _TicketsEmptyState()
                else
                  ...tickets.map((ticket) => _TicketTile(
                        event: event,
                        ticket: ticket,
                      )),
                const SizedBox(height: 24),
                _SectionTitle('Seat selection'),
                const SizedBox(height: 10),
                const _SeatSelectionPreview(),
                const SizedBox(height: 24),
                _SectionTitle('Map & entry QR'),
                const SizedBox(height: 10),
                _MapQr(event: event),
                const SizedBox(height: 24),
                _SectionTitle('Terms'),
                const SizedBox(height: 8),
                Text(event['terms'] ??
                    'Terms will be shown here when added from the dashboard.'),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ImageFallback extends StatelessWidget {
  const _ImageFallback();

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Theme.of(context).colorScheme.primaryContainer,
            Theme.of(context).colorScheme.secondaryContainer,
          ],
        ),
      ),
      child: const Center(child: Icon(Icons.image_outlined, size: 54)),
    );
  }
}

class _MetaStrip extends StatelessWidget {
  const _MetaStrip({required this.event, required this.social});

  final Map<String, dynamic> event;
  final Map<String, dynamic> social;

  @override
  Widget build(BuildContext context) {
    final city =
        event['city'] is Map ? event['city']['name'] : event['venue_name'];
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            const Icon(Icons.place_outlined),
            const SizedBox(width: 8),
            Expanded(child: Text(city?.toString() ?? 'Venue')),
            const Icon(Icons.star, color: Color(0xFFFFC857), size: 18),
            const SizedBox(width: 4),
            Text('${social['rating_average'] ?? 0}'),
          ],
        ),
      ),
    );
  }
}

class _Countdown extends StatefulWidget {
  const _Countdown({required this.startsAt});

  final String? startsAt;

  @override
  State<_Countdown> createState() => _CountdownState();
}

class _CountdownState extends State<_Countdown> {
  late Timer timer;
  Duration left = Duration.zero;

  @override
  void initState() {
    super.initState();
    _tick();
    timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
  }

  @override
  void dispose() {
    timer.cancel();
    super.dispose();
  }

  void _tick() {
    final target = DateTime.tryParse(widget.startsAt ?? '');
    if (target == null) return;
    setState(() => left = target.difference(DateTime.now()));
  }

  @override
  Widget build(BuildContext context) {
    final days = left.inDays.clamp(0, 9999);
    final hours = left.inHours.remainder(24).clamp(0, 23);
    final minutes = left.inMinutes.remainder(60).clamp(0, 59);
    return Card(
      color: Theme.of(context).colorScheme.primaryContainer,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _TimeBlock(label: 'Days', value: '$days'),
            _TimeBlock(label: 'Hours', value: '$hours'),
            _TimeBlock(label: 'Min', value: '$minutes'),
          ],
        ),
      ),
    );
  }
}

class _TimeBlock extends StatelessWidget {
  const _TimeBlock({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      Text(value,
          style: Theme.of(context)
              .textTheme
              .headlineSmall
              ?.copyWith(fontWeight: FontWeight.w900)),
      Text(label)
    ]);
  }
}

class _TicketTile extends StatelessWidget {
  const _TicketTile({required this.event, required this.ticket});

  final Map<String, dynamic> event;
  final Map<String, dynamic> ticket;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final color = _ticketColor(ticket['label_color']?.toString());
    final features = List<String>.from(ticket['features'] as List? ?? []);
    final isAvailable = ticket['is_available'] == true;
    final remaining = (ticket['remaining_quantity'] as num?)?.toInt() ?? 0;
    final discount = (ticket['discount_percent'] as num?)?.toInt() ?? 0;
    final price = ticket['price_label']?.toString() ??
        '${ticket['price'] ?? 0} ${ticket['currency'] ?? 'SAR'}';
    final oldPrice = ticket['price_before_discount_label']?.toString();

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: theme.cardColor,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: color.withOpacity(.28)),
        boxShadow: [AppTokens.softShadow(context)],
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(18)),
              gradient: LinearGradient(
                colors: [color.withOpacity(.18), color.withOpacity(.04)],
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    color: color,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.confirmation_number,
                      color: Colors.white),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              ticket['name']?.toString() ?? 'Ticket',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: theme.textTheme.titleMedium
                                  ?.copyWith(fontWeight: FontWeight.w900),
                            ),
                          ),
                          if (discount > 0)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFF2B7A),
                                borderRadius: BorderRadius.circular(99),
                              ),
                              child: Text(
                                '-$discount%',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 11,
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        ticket['description']?.toString() ??
                            'باقة تذاكر متاحة للحجز الآن.',
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: theme.textTheme.bodySmall
                            ?.copyWith(color: theme.hintColor),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                price,
                                style: theme.textTheme.headlineSmall?.copyWith(
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                              if (oldPrice != null) ...[
                                const SizedBox(width: 8),
                                Text(
                                  oldPrice,
                                  style: theme.textTheme.bodySmall?.copyWith(
                                    decoration: TextDecoration.lineThrough,
                                    color: theme.hintColor,
                                  ),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 6),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: [
                              _TicketChip(
                                icon: Icons.event_seat,
                                label: remaining > 0
                                    ? '$remaining مقعد متبقي'
                                    : 'نفدت المقاعد',
                              ),
                              _TicketChip(
                                icon: Icons.person_add_alt,
                                label:
                                    'حد الشراء ${ticket['purchase_limit_per_user'] ?? 'مفتوح'}',
                              ),
                              _TicketChip(
                                icon: Icons.verified,
                                label: ticket['availability_label']
                                        ?.toString() ??
                                    (isAvailable ? 'Available' : 'Unavailable'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    FilledButton(
                      onPressed: isAvailable
                          ? () => context.push('/checkout', extra: {
                                ...event,
                                'selected_ticket_id': ticket['id'],
                              })
                          : null,
                      style: FilledButton.styleFrom(
                        backgroundColor: color,
                        disabledBackgroundColor:
                            theme.disabledColor.withOpacity(.25),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: Text(isAvailable ? 'اختيار' : 'غير متاح'),
                    ),
                  ],
                ),
                if (features.isNotEmpty) ...[
                  const SizedBox(height: 14),
                  const Divider(height: 1),
                  const SizedBox(height: 12),
                  Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: features
                          .take(4)
                          .map((feature) => _FeaturePill(feature))
                          .toList(),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _TicketChip extends StatelessWidget {
  const _TicketChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 6),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(99),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14),
          const SizedBox(width: 5),
          Text(label, style: const TextStyle(fontSize: 11)),
        ],
      ),
    );
  }
}

class _FeaturePill extends StatelessWidget {
  const _FeaturePill(this.label);

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        border: Border.all(color: Theme.of(context).dividerColor),
        borderRadius: BorderRadius.circular(99),
      ),
      child: Text(label, style: const TextStyle(fontSize: 12)),
    );
  }
}

class _TicketsEmptyState extends StatelessWidget {
  const _TicketsEmptyState();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Theme.of(context).dividerColor),
      ),
      child: const Row(
        children: [
          Icon(Icons.confirmation_number_outlined),
          SizedBox(width: 10),
          Expanded(child: Text('لا توجد تذاكر متاحة لهذه الفعالية حالياً.')),
        ],
      ),
    );
  }
}

Color _ticketColor(String? value) {
  if (value == null || value.isEmpty) return const Color(0xFF7C3AED);
  final normalized = value.replaceAll('#', '');
  final parsed = int.tryParse(normalized.length == 6
      ? 'FF$normalized'
      : normalized.padLeft(8, 'F'));
  return parsed == null ? const Color(0xFF7C3AED) : Color(parsed);
}

class _SeatSelectionPreview extends StatelessWidget {
  const _SeatSelectionPreview();

  @override
  Widget build(BuildContext context) {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Container(
                height: 8,
                width: 160,
                decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.primary,
                    borderRadius: BorderRadius.circular(8))),
            const SizedBox(height: 16),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: List.generate(48, (index) {
                final premium = index % 7 == 0;
                final sold = index % 11 == 0;
                return AnimatedContainer(
                  duration: AppTokens.motion,
                  width: 18,
                  height: 18,
                  decoration: BoxDecoration(
                    color: sold
                        ? Colors.grey
                        : premium
                            ? Theme.of(context).colorScheme.primary
                            : Theme.of(context).colorScheme.secondaryContainer,
                    borderRadius: BorderRadius.circular(4),
                  ),
                );
              }),
            ),
          ],
        ),
      ),
    );
  }
}

class _MapQr extends StatelessWidget {
  const _MapQr({required this.event});

  final Map<String, dynamic> event;

  @override
  Widget build(BuildContext context) {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Expanded(
                child: Text(event['map_url'] ??
                    'Map URL can be managed from Laravel dashboard.')),
            QrImageView(data: event['slug'] ?? 'event', size: 92),
          ],
        ),
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.title);

  final String title;

  @override
  Widget build(BuildContext context) {
    return Text(title,
        style: Theme.of(context)
            .textTheme
            .titleLarge
            ?.copyWith(fontWeight: FontWeight.w900));
  }
}
