import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../core/i18n/strings.dart';

class EventCard extends StatelessWidget {
  const EventCard({required this.event, super.key});

  final Map<String, dynamic> event;

  @override
  Widget build(BuildContext context) {
    final image = event['image_url'] ?? event['banner_image_url'];
    final city = event['city'] is Map ? event['city']['name'] : null;
    final s = AppStrings.of(context);
    final priceValue =
        num.tryParse((event['starting_price'] ?? 0).toString()) ?? 0;
    final startsAt = DateTime.tryParse(event['starts_at']?.toString() ?? '');
    final date = startsAt == null
        ? ''
        : DateFormat('EEEE، d MMMM في hh:mm a', s.isArabic ? 'ar' : 'en')
            .format(startsAt);

    return Card(
      clipBehavior: Clip.antiAlias,
      color: Colors.transparent,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      child: InkWell(
        onTap: () => context.push('/events/${event['slug']}'),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                AspectRatio(
                  aspectRatio: 1,
                  child: image == null
                      ? const ColoredBox(
                          color: Color(0xFF1A1B20),
                          child: Center(
                              child: Text('عسير',
                                  style: TextStyle(
                                      color: Color(0xFF282A31),
                                      fontSize: 52,
                                      fontWeight: FontWeight.w900))))
                      : CachedNetworkImage(
                          imageUrl: image,
                          fit: BoxFit.cover,
                          errorWidget: (_, __, ___) => const _ImageFallback()),
                ),
                PositionedDirectional(
                  top: 10,
                  end: 10,
                  child: DecoratedBox(
                    decoration: const BoxDecoration(
                        color: Colors.black54, shape: BoxShape.circle),
                    child: IconButton(
                        onPressed: () {},
                        icon: const Icon(Icons.favorite_border,
                            color: Colors.white)),
                  ),
                ),
                if (event['is_featured'] == true)
                  PositionedDirectional(
                    bottom: 10,
                    start: 10,
                    child: Chip(
                        label: Text(s.t('recommended')),
                        visualDensity: VisualDensity.compact),
                  ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(2, 12, 2, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(event['title'] ?? '',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context)
                          .textTheme
                          .titleLarge
                          ?.copyWith(fontWeight: FontWeight.w900)),
                  const SizedBox(height: 8),
                  Text(date,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                          color: Color(0xFFA7A9B4),
                          fontWeight: FontWeight.w700)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.place_outlined, size: 16),
                      const SizedBox(width: 4),
                      Expanded(
                          child: Text(city ?? event['venue_name'] ?? '',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style:
                                  const TextStyle(color: Color(0xFFA7A9B4)))),
                      Text(
                          priceValue <= 0
                              ? s.t('free')
                              : '${priceValue.toStringAsFixed(0)} SAR',
                          style: Theme.of(context)
                              .textTheme
                              .labelLarge
                              ?.copyWith(
                                  color: const Color(0xFFFF2D7A),
                                  fontWeight: FontWeight.w900)),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ImageFallback extends StatelessWidget {
  const _ImageFallback();

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: const BoxDecoration(color: Color(0xFF1A1B20)),
      child: const Center(
          child: Text('عسير',
              style: TextStyle(
                  color: Color(0xFF282A31),
                  fontSize: 52,
                  fontWeight: FontWeight.w900))),
    );
  }
}
