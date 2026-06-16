import 'dart:async';
import 'dart:math';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/i18n/strings.dart';
import '../../shared/widgets/async_view.dart';
import '../../shared/widgets/event_card.dart';
import '../../shared/widgets/platform_logo.dart';
import '../../shared/widgets/skeleton.dart';
import '../app/app_state.dart';

final resaleListingsProvider =
    FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final api = ref.watch(apiClientProvider);
  final response =
      await api.dio.get('/resale-listings', queryParameters: {'per_page': 8});
  return List<dynamic>.from(response.data['data'] as List);
});

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final home = ref.watch(homeProvider);
    final resaleListings = ref.watch(resaleListingsProvider);

    return SafeArea(
      bottom: false,
      child: AsyncView<Map<String, dynamic>>(
        value: home,
        loading: const HomeSkeleton(),
        builder: (data) {
          final banners =
              List<Map<String, dynamic>>.from(data['banners'] as List? ?? []);
          final sections =
              Map<String, dynamic>.from(data['sections'] as Map? ?? {});
          final filters =
              Map<String, dynamic>.from(data['filters'] as Map? ?? {});
          final categories = List<Map<String, dynamic>>.from(
              filters['categories'] as List? ?? []);
          final best = _events(sections['trending']).isNotEmpty
              ? _events(sections['trending'])
              : _events(sections['recommended']);
          final latest = _events(sections['upcoming']).isNotEmpty
              ? _events(sections['upcoming'])
              : best;
          final stories = _events(sections['app_stories']);
          final dashboardAds = [
            ..._items(sections['featured_events']),
            ..._items(sections['today_cards']),
            ..._items(sections['experience_cards']),
            ..._items(sections['offers']),
            ...banners.skip(1),
          ];

          return RefreshIndicator(
            onRefresh: () => ref.refresh(homeProvider.future),
            child: CustomScrollView(
              slivers: [
                SliverToBoxAdapter(child: _PromoStrip(onBack: () {})),
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 14, 16, 8),
                  sliver: SliverToBoxAdapter(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _TopBar(
                            onLanguage: ref
                                .read(settingsProvider.notifier)
                                .toggleLocale),
                        const SizedBox(height: 18),
                        _Stories(items: stories.isNotEmpty ? stories : best),
                        const SizedBox(height: 20),
                        _HeroShowcase(items: banners, fallback: latest),
                        const SizedBox(height: 22),
                        _Communities(items: best),
                        const SizedBox(height: 18),
                        const _ZigZag(color: Color(0xFFA8F52D)),
                        const SizedBox(height: 22),
                        _BookingSearch(),
                        const SizedBox(height: 26),
                        _AdBanner(
                            item: dashboardAds.isNotEmpty
                                ? dashboardAds.first
                                : null),
                        const SizedBox(height: 26),
                        const _ResalePromoCard(),
                        const SizedBox(height: 22),
                        _ResaleListingsSection(value: resaleListings),
                        const SizedBox(height: 26),
                        const _NewsletterCard(),
                        const SizedBox(height: 26),
                        const _ZigZag(color: Color(0xFFFF7B2D)),
                      ],
                    ),
                  ),
                ),
                _CategoryGrid(categories: categories),
                _EventRail(
                    title: AppStrings.of(context).t('bestSelling'),
                    items: best),
                _EventRail(
                    title: AppStrings.of(context).t('latestExperiences'),
                    items: latest),
                const SliverToBoxAdapter(child: SizedBox(height: 98)),
              ],
            ),
          );
        },
      ),
    );
  }

  List<Map<String, dynamic>> _events(dynamic value) =>
      List<Map<String, dynamic>>.from(value as List? ?? []);

  List<Map<String, dynamic>> _items(dynamic value) =>
      List<Map<String, dynamic>>.from(value as List? ?? []);
}

String? _contentImage(Map<String, dynamic> item, {bool heroFirst = false}) {
  final primary = item['image_url'] ??
      (heroFirst ? item['hero_image_url'] : null) ??
      item['banner_image_url'] ??
      item['hero_image_url'];
  if (primary != null && primary.toString().isNotEmpty) {
    return primary.toString();
  }

  final event = item['event'];
  if (event is Map) {
    final eventMap = Map<String, dynamic>.from(event);
    final eventHero = eventMap['banner_image_url'] ?? eventMap['image_url'];
    if (eventHero != null && eventHero.toString().isNotEmpty) {
      return eventHero.toString();
    }
  }

  return null;
}

class _PromoStrip extends StatelessWidget {
  const _PromoStrip({required this.onBack});

  final VoidCallback onBack;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 52,
      color: const Color(0xFFFF2D7A),
      padding: const EdgeInsets.symmetric(horizontal: 10),
      child: Row(
        children: [
          IconButton(
              onPressed: onBack,
              icon: const Icon(Icons.chevron_left, color: Colors.white)),
          const Expanded(
            child: Text(
              '✨ وظائف للمبدعين فقط، قدّم الآن! ✨',
              textAlign: TextAlign.center,
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.w900),
            ),
          ),
          const SizedBox(width: 48),
        ],
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  const _TopBar({required this.onLanguage});

  final VoidCallback onLanguage;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        const PlatformLogo(size: 46),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            'المملكة العربية السعودية',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w900,
                decoration: TextDecoration.underline),
          ),
        ),
        IconButton(
            onPressed: onLanguage, icon: const Icon(Icons.language, size: 34)),
        IconButton(onPressed: () {}, icon: const Icon(Icons.search, size: 34)),
      ],
    );
  }
}

class _Stories extends StatelessWidget {
  const _Stories({required this.items});

  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    final stories = items.isEmpty
        ? [
            {'title': 'سيرك بلوما'},
            {'title': 'فلاينق اوفر'},
            {'title': 'أكاديمية نادي'},
            {'title': 'بوليفارد فلاورز'},
          ]
        : items.take(6).toList();

    return SizedBox(
      height: 116,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: stories.length,
        separatorBuilder: (_, __) => const SizedBox(width: 18),
        itemBuilder: (context, index) {
          final item = Map<String, dynamic>.from(stories[index]);
          final image = _contentImage(item);
          return InkWell(
            borderRadius: BorderRadius.circular(8),
            onTap: () => _openStory(context, stories, index),
            child: SizedBox(
              width: 84,
              child: Column(
                children: [
                  Container(
                    width: 76,
                    height: 76,
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border:
                          Border.all(color: const Color(0xFFFF2D7A), width: 4),
                    ),
                    child: ClipOval(
                        child: _NetworkImage(
                            url: image?.toString(), icon: Icons.auto_awesome)),
                  ),
                  const SizedBox(height: 7),
                  Text(item['title']?.toString() ?? '',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      textAlign: TextAlign.center),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  void _openStory(
      BuildContext context, List<Map<String, dynamic>> stories, int initial) {
    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: 'story',
      barrierColor: Colors.black87,
      pageBuilder: (_, __, ___) =>
          _StoryViewer(items: stories, initialIndex: initial),
    );
  }
}

class _StoryViewer extends StatefulWidget {
  const _StoryViewer({required this.items, required this.initialIndex});

  final List<Map<String, dynamic>> items;
  final int initialIndex;

  @override
  State<_StoryViewer> createState() => _StoryViewerState();
}

class _StoryViewerState extends State<_StoryViewer> {
  late final PageController _controller;
  late int _index;

  @override
  void initState() {
    super.initState();
    _index = widget.initialIndex;
    _controller = PageController(initialPage: widget.initialIndex);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        backgroundColor: Colors.black,
        body: Stack(
          children: [
            PageView.builder(
              controller: _controller,
              itemCount: widget.items.length,
              onPageChanged: (value) => setState(() => _index = value),
              itemBuilder: (context, index) {
                final item = widget.items[index];
                final image = _contentImage(item, heroFirst: true);

                return Stack(
                  fit: StackFit.expand,
                  children: [
                    _NetworkImage(
                        url: image?.toString(), icon: Icons.auto_awesome),
                    const DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.bottomCenter,
                          end: Alignment.center,
                          colors: [Colors.black87, Colors.transparent],
                        ),
                      ),
                    ),
                    PositionedDirectional(
                      start: 20,
                      end: 20,
                      bottom: 34,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(item['badge']?.toString() ?? 'منصة عسير',
                              style: const TextStyle(
                                  color: Color(0xFFFF2D7A),
                                  fontWeight: FontWeight.w900)),
                          const SizedBox(height: 8),
                          Text(
                            item['title']?.toString() ?? '',
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: Theme.of(context)
                                .textTheme
                                .headlineMedium
                                ?.copyWith(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w900),
                          ),
                          if ((item['subtitle']?.toString() ?? '').isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Text(
                                item['subtitle'].toString(),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                    color: Color(0xFFD7D8E0),
                                    fontWeight: FontWeight.w700),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
            PositionedDirectional(
              start: 12,
              end: 12,
              top: 12,
              child: Row(
                children: List.generate(widget.items.length, (index) {
                  return Expanded(
                    child: Container(
                      height: 4,
                      margin: const EdgeInsets.symmetric(horizontal: 2),
                      decoration: BoxDecoration(
                        color: index <= _index
                            ? Colors.white
                            : Colors.white.withOpacity(.28),
                        borderRadius: BorderRadius.circular(999),
                      ),
                    ),
                  );
                }),
              ),
            ),
            PositionedDirectional(
              top: 24,
              end: 12,
              child: IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.close, color: Colors.white, size: 30),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HeroShowcase extends StatefulWidget {
  const _HeroShowcase({required this.items, required this.fallback});

  final List<Map<String, dynamic>> items;
  final List<Map<String, dynamic>> fallback;

  @override
  State<_HeroShowcase> createState() => _HeroShowcaseState();
}

class _HeroShowcaseState extends State<_HeroShowcase> {
  late final PageController _controller;
  Timer? _timer;
  int _index = 0;

  @override
  void initState() {
    super.initState();
    _controller = PageController(viewportFraction: .88);
    _startTimer();
  }

  @override
  void didUpdateWidget(covariant _HeroShowcase oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.items.length != widget.items.length ||
        oldWidget.fallback.length != widget.fallback.length) {
      _index = 0;
      _startTimer();
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _startTimer() {
    _timer?.cancel();
    final data = widget.items.isNotEmpty ? widget.items : widget.fallback;
    if (data.length < 2) return;
    _timer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (!mounted || !_controller.hasClients) return;
      final next = (_index + 1) % data.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 520),
        curve: Curves.easeOutCubic,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final data = widget.items.isNotEmpty ? widget.items : widget.fallback;
    if (data.isEmpty) return const SizedBox.shrink();

    return Column(
      children: [
        SizedBox(
          height: 330,
          child: PageView.builder(
            controller: _controller,
            itemCount: data.length,
            onPageChanged: (value) => setState(() => _index = value),
            itemBuilder: (context, index) {
              final item = data[index];
              final image = _contentImage(item, heroFirst: true);
              return Padding(
                padding: const EdgeInsetsDirectional.only(end: 14),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      _NetworkImage(
                          url: image?.toString(),
                          icon: Icons.festival_outlined),
                      const DecoratedBox(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                              begin: Alignment.bottomCenter,
                              end: Alignment.center,
                              colors: [Colors.black87, Colors.transparent]),
                        ),
                      ),
                      PositionedDirectional(
                        start: 16,
                        end: 16,
                        bottom: 16,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if ((item['badge']?.toString() ?? '').isNotEmpty)
                              Container(
                                margin: const EdgeInsets.only(bottom: 8),
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 10, vertical: 5),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFF2D7A),
                                  borderRadius: BorderRadius.circular(999),
                                ),
                                child: Text(
                                  item['badge'].toString(),
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w900),
                                ),
                              ),
                            Text(
                              item['title']?.toString() ?? '',
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: Theme.of(context)
                                  .textTheme
                                  .headlineSmall
                                  ?.copyWith(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w900),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(data.length, (index) {
            final active = index == _index;
            return AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: active ? 24 : 7,
              height: 7,
              margin: const EdgeInsets.symmetric(horizontal: 3),
              decoration: BoxDecoration(
                color:
                    active ? const Color(0xFFFF2D7A) : const Color(0xFF3A3C45),
                borderRadius: BorderRadius.circular(999),
              ),
            );
          }),
        ),
      ],
    );
  }
}

class _Communities extends StatelessWidget {
  const _Communities({required this.items});

  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(s.t('communities'),
            style: Theme.of(context)
                .textTheme
                .headlineSmall
                ?.copyWith(fontWeight: FontWeight.w900)),
        const SizedBox(height: 14),
        SizedBox(
          height: 104,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: 6,
            separatorBuilder: (_, __) => const SizedBox(width: 16),
            itemBuilder: (context, index) {
              final item = items.length > index
                  ? items[index]
                  : <String, dynamic>{
                      'title': [
                        'ونتر وندرلاند',
                        'النصر',
                        'الاتحاد',
                        'الشارع العالمي',
                        'محبي عسير',
                        'موسم الرياض'
                      ][index]
                    };
              return SizedBox(
                width: 86,
                child: Column(
                  children: [
                    Stack(
                      clipBehavior: Clip.none,
                      children: [
                        CircleAvatar(
                            radius: 36,
                            backgroundColor: const Color(0xFF1A1B20),
                            child: _NetworkImage(
                                url: _contentImage(
                                    Map<String, dynamic>.from(item)),
                                icon: Icons.groups_2_outlined)),
                        const PositionedDirectional(
                            bottom: -2,
                            end: -3,
                            child: Icon(Icons.verified,
                                color: Color(0xFF7A55FF), size: 24)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(item['title']?.toString() ?? '',
                        maxLines: 1, overflow: TextOverflow.ellipsis),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _BookingSearch extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    final options = [s.today, s.t('tomorrow'), s.t('thisWeek'), s.t('custom')];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Center(
            child: Text(s.t('nextBooking'),
                style: Theme.of(context)
                    .textTheme
                    .headlineSmall
                    ?.copyWith(fontWeight: FontWeight.w900))),
        const SizedBox(height: 16),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 2.75),
          itemCount: options.length,
          itemBuilder: (context, index) => OutlinedButton.icon(
              onPressed: () {},
              icon: const Icon(Icons.calendar_month),
              label: Text(options[index])),
        ),
      ],
    );
  }
}

class _CategoryGrid extends StatelessWidget {
  const _CategoryGrid({required this.categories});

  final List<Map<String, dynamic>> categories;

  static const _fallback = [
    {'slug': 'today', 'name': 'اليوم', 'icon': 'calendar_today'},
    {'slug': 'experiences', 'name': 'التجارب', 'icon': 'confirmation_number'},
    {'slug': 'sports', 'name': 'الرياضة', 'icon': 'emoji_events'},
    {'slug': 'football', 'name': 'كرة القدم', 'icon': 'sports_soccer'},
    {'slug': 'restaurants', 'name': 'المطاعم', 'icon': 'restaurant'},
    {'slug': 'aviation', 'name': 'الطيران', 'icon': 'flight_takeoff'},
    {'slug': 'hotels', 'name': 'فنادق', 'icon': 'beach_access'},
    {'slug': 'concerts', 'name': 'الحفلات', 'icon': 'music_note'},
    {'slug': 'shows', 'name': 'العروض', 'icon': 'theater_comedy'},
    {'slug': 'store', 'name': 'المتجر', 'icon': 'shopping_bag'},
    {'slug': 'auctions', 'name': 'مزادات', 'icon': 'gavel'},
    {'slug': 'more', 'name': 'المزيد', 'icon': 'grid_view'},
  ];

  @override
  Widget build(BuildContext context) {
    final items = categories.isEmpty ? _fallback : categories.take(12).toList();

    return SliverPadding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 8),
      sliver: SliverGrid.builder(
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 4,
            mainAxisSpacing: 20,
            crossAxisSpacing: 14,
            childAspectRatio: .78),
        itemCount: items.length,
        itemBuilder: (context, index) {
          final item = items[index];
          final icon = _iconFor(item['icon']?.toString());
          final slug = item['slug']?.toString() ?? '';
          final isFresh =
              slug == 'aviation' || slug == 'hotels' || slug == 'auctions';

          return InkWell(
            borderRadius: BorderRadius.circular(8),
            onTap: () => context.go('/events?category=$slug'),
            child: Column(
              children: [
                Icon(icon, size: 42, color: const Color(0xFFE8E8EE)),
                const SizedBox(height: 8),
                Text(item['name']?.toString() ?? '',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontWeight: FontWeight.w800)),
                if (isFresh)
                  const Text('جديد',
                      style: TextStyle(
                          color: Color(0xFFFF2D7A),
                          fontWeight: FontWeight.w900,
                          fontSize: 12)),
              ],
            ),
          );
        },
      ),
    );
  }

  IconData _iconFor(String? icon) {
    return switch (icon) {
      'calendar_today' => Icons.calendar_today_outlined,
      'confirmation_number' => Icons.confirmation_number_outlined,
      'emoji_events' => Icons.emoji_events_outlined,
      'sports_soccer' => Icons.sports_soccer,
      'restaurant' => Icons.restaurant_outlined,
      'flight_takeoff' => Icons.flight_takeoff,
      'beach_access' => Icons.beach_access_outlined,
      'music_note' => Icons.music_note,
      'theater_comedy' => Icons.theater_comedy_outlined,
      'shopping_bag' => Icons.shopping_bag_outlined,
      'gavel' => Icons.gavel_outlined,
      'grid_view' => Icons.grid_view_outlined,
      _ => Icons.category_outlined,
    };
  }
}

class _EventRail extends StatelessWidget {
  const _EventRail({required this.title, required this.items});

  final String title;
  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty)
      return const SliverToBoxAdapter(child: SizedBox.shrink());

    return SliverPadding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 8),
      sliver: SliverList.list(
        children: [
          Text(title,
              style: Theme.of(context)
                  .textTheme
                  .headlineSmall
                  ?.copyWith(fontWeight: FontWeight.w900)),
          const SizedBox(height: 14),
          SizedBox(
            height: 362,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: items.length,
              separatorBuilder: (_, __) => const SizedBox(width: 14),
              itemBuilder: (context, index) =>
                  SizedBox(width: 286, child: EventCard(event: items[index])),
            ),
          ),
        ],
      ),
    );
  }
}

class _AdBanner extends StatelessWidget {
  const _AdBanner({this.item});

  final Map<String, dynamic>? item;

  @override
  Widget build(BuildContext context) {
    final image = item == null ? null : _contentImage(item!, heroFirst: true);
    final title = item?['title']?.toString() ?? 'إعلان منصة عسير';
    final subtitle = item?['subtitle']?.toString() ??
        item?['meta_label']?.toString() ??
        item?['price_label']?.toString() ??
        'اكتشف أحدث العروض والفعاليات';
    final badge = item?['badge']?.toString() ?? 'إعلان';

    return InkWell(
      borderRadius: BorderRadius.circular(8),
      onTap: () {
        final event = item?['event'];
        if (event is Map && (event['slug']?.toString().isNotEmpty ?? false)) {
          context.push('/events/${event['slug']}');
          return;
        }
        final slug = item?['slug']?.toString();
        if (slug != null && slug.isNotEmpty) {
          context.push('/events');
        }
      },
      child: Container(
        height: 104,
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          color: const Color(0xFF202126),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFF343640)),
        ),
        child: Stack(
          fit: StackFit.expand,
          children: [
            if (image != null && image.toString().isNotEmpty)
              _NetworkImage(
                  url: image.toString(), icon: Icons.campaign_outlined),
            const DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.centerRight,
                  end: Alignment.centerLeft,
                  colors: [Colors.black87, Colors.black45, Colors.transparent],
                ),
              ),
            ),
            PositionedDirectional(
              start: 16,
              end: 16,
              top: 14,
              bottom: 14,
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          badge,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Color(0xFFFF2D7A),
                            fontSize: 12,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          subtitle,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Color(0xFFD7D8E0),
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Icon(Icons.arrow_back_ios_new,
                      color: Colors.white, size: 18),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ResalePromoCard extends StatelessWidget {
  const _ResalePromoCard();

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(8),
      onTap: () => context.go('/resale'),
      child: Container(
        height: 390,
        padding: const EdgeInsets.all(22),
        decoration: BoxDecoration(
          color: const Color(0xFF08090D),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFF2E3038)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            const Text('إعلان',
                style: TextStyle(
                    color: Color(0xFF8F92A1),
                    fontSize: 14,
                    fontWeight: FontWeight.w800)),
            const SizedBox(height: 18),
            const Text(
              'اعد بيع تذاكرك',
              textAlign: TextAlign.right,
              style: TextStyle(
                color: Colors.white,
                fontSize: 32,
                fontWeight: FontWeight.w900,
                height: 1.15,
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              'لم تعد بحاجة لتذاكرك؟ او حصل لك ظرف ولم تستطع الحضور؟ اعد بيع تذكرتك بكل سهولة على منصة إعادة البيع من عسير',
              textAlign: TextAlign.right,
              style: TextStyle(
                color: Color(0xFFA6A8B3),
                fontSize: 20,
                fontWeight: FontWeight.w700,
                height: 1.55,
              ),
            ),
            const Spacer(),
            SizedBox(
              height: 142,
              child: CustomPaint(
                painter: _TicketTradePainter(),
                child: const SizedBox.expand(),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ResaleListingsSection extends StatelessWidget {
  const _ResaleListingsSection({required this.value});

  final AsyncValue<List<dynamic>> value;

  @override
  Widget build(BuildContext context) {
    return value.when(
      loading: () => const SizedBox(
        height: 170,
        child: Center(child: CircularProgressIndicator()),
      ),
      error: (_, __) => const SizedBox.shrink(),
      data: (items) {
        final listings = items
            .whereType<Map>()
            .map((item) => Map<String, dynamic>.from(item))
            .toList();

        if (listings.isEmpty) {
          return const _EmptyResaleMarket();
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    'تذاكر معروضة للبيع',
                    style: Theme.of(context)
                        .textTheme
                        .headlineSmall
                        ?.copyWith(fontWeight: FontWeight.w900),
                  ),
                ),
                TextButton(
                  onPressed: () => context.go('/resale'),
                  child: const Text('عرض الكل'),
                ),
              ],
            ),
            const SizedBox(height: 12),
            SizedBox(
              height: 218,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: listings.length,
                separatorBuilder: (_, __) => const SizedBox(width: 12),
                itemBuilder: (context, index) =>
                    _ResaleListingCard(listing: listings[index]),
              ),
            ),
          ],
        );
      },
    );
  }
}

class _ResaleListingCard extends StatelessWidget {
  const _ResaleListingCard({required this.listing});

  final Map<String, dynamic> listing;

  @override
  Widget build(BuildContext context) {
    final event = listing['event'] is Map
        ? Map<String, dynamic>.from(listing['event'] as Map)
        : <String, dynamic>{};
    final ticket = listing['ticket'] is Map
        ? Map<String, dynamic>.from(listing['ticket'] as Map)
        : <String, dynamic>{};
    final city = event['city'] is Map ? event['city']['name']?.toString() : '';
    final image = event['banner_image_url'] ?? event['image_url'];
    final price = (listing['price'] as num?)?.toDouble() ?? 0;

    return InkWell(
      borderRadius: BorderRadius.circular(16),
      onTap: () => context.go('/resale'),
      child: Container(
        width: 242,
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          color: const Color(0xFF15161B),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFF2E3038)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                fit: StackFit.expand,
                children: [
                  _NetworkImage(
                      url: image?.toString(),
                      icon: Icons.confirmation_number_outlined),
                  const DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.bottomCenter,
                        end: Alignment.center,
                        colors: [Colors.black87, Colors.transparent],
                      ),
                    ),
                  ),
                  PositionedDirectional(
                    start: 10,
                    top: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFF2D7A),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: const Text('إعادة بيع',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w900)),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    event['title']?.toString() ?? 'تذكرة معروضة للبيع',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 7),
                  Text(
                    [
                      if ((city ?? '').isNotEmpty) city,
                      ticket['name']?.toString() ?? 'تذكرة'
                    ].join(' • '),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        color: Color(0xFFA6A8B3),
                        fontSize: 12,
                        fontWeight: FontWeight.w700),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Text('${price.toStringAsFixed(2)} ر.س',
                          style: const TextStyle(
                              color: Color(0xFFC8F000),
                              fontWeight: FontWeight.w900,
                              fontSize: 16)),
                      const Spacer(),
                      const Icon(Icons.arrow_back_ios_new,
                          size: 15, color: Colors.white),
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

class _EmptyResaleMarket extends StatelessWidget {
  const _EmptyResaleMarket();

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(8),
      onTap: () => context.go('/resale'),
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: const Color(0xFF15161B),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFF2E3038)),
        ),
        child: Row(
          children: const [
            Icon(Icons.sell_outlined, color: Color(0xFFFF2D7A), size: 34),
            SizedBox(width: 12),
            Expanded(
              child: Text(
                'لا توجد تذاكر معروضة حالياً. افتح بوابة إعادة البيع لعرض تذكرتك.',
                style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    height: 1.5),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _NewsletterCard extends StatelessWidget {
  const _NewsletterCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: const Color(0xFF08090D),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: const Color(0xFF2E3038)),
      ),
      child: Column(
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: CustomPaint(painter: _NewsletterPainter())),
              const SizedBox(width: 14),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    _NewsletterCheck('توصيات شخصية'),
                    _NewsletterCheck('عروض خاصة'),
                    _NewsletterCheck('إعلانات مسبقة'),
                    _NewsletterCheck('أبرز الأحداث'),
                    _NewsletterCheck('أخبار مثيرة للاهتمام'),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          const Text(
            'ابق على اطلاع!',
            textAlign: TextAlign.center,
            style: TextStyle(
                color: Colors.white, fontSize: 30, fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 12),
          const Text(
            'كن أول من يحصل على عروض حصرية وابقى على اطلاع على آخر الأخبار حول منتجاتنا، كل ذلك مباشرة إلى بريدك الإلكتروني.',
            textAlign: TextAlign.center,
            style: TextStyle(
                color: Color(0xFFA6A8B3),
                fontSize: 18,
                height: 1.55,
                fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 22),
          TextField(
            keyboardType: TextInputType.emailAddress,
            decoration: InputDecoration(
              hintText: 'اكتب بريدك الإلكتروني',
              filled: true,
              fillColor: const Color(0xFF08090D),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Color(0xFF2E3038)),
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 56,
            child: FilledButton(
              onPressed: null,
              style: FilledButton.styleFrom(
                disabledBackgroundColor: const Color(0xFF3A3B42),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8)),
              ),
              child: const Text('إشترك الآن!',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
            ),
          ),
        ],
      ),
    );
  }
}

class _NewsletterCheck extends StatelessWidget {
  const _NewsletterCheck(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          Text(text,
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w800)),
          const SizedBox(width: 10),
          const Icon(Icons.check, color: Color(0xFF10B981), size: 22),
        ],
      ),
    );
  }
}

class _ZigZag extends StatelessWidget {
  const _ZigZag({required this.color});

  final Color color;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 34,
      width: double.infinity,
      child: CustomPaint(painter: _ZigZagPainter(color)),
    );
  }
}

class _TicketTradePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final white = Paint()..color = Colors.white;
    final sleeve = Paint()..color = const Color(0xFFF5F5F5);
    final ticketPaint = Paint()..color = const Color(0xFFC8F000);
    final ticketBack = Paint()..color = const Color(0xFFFF2D7A);
    final money = Paint()..color = const Color(0xFFC7F8D2);
    final outline = Paint()
      ..color = Colors.black
      ..strokeWidth = 2
      ..style = PaintingStyle.stroke;

    final leftHand = Path()
      ..moveTo(0, size.height * .63)
      ..cubicTo(size.width * .12, size.height * .6, size.width * .18,
          size.height * .48, size.width * .27, size.height * .55)
      ..lineTo(size.width * .42, size.height * .36)
      ..lineTo(size.width * .48, size.height * .48)
      ..lineTo(size.width * .31, size.height * .72)
      ..cubicTo(size.width * .18, size.height * .78, size.width * .1,
          size.height * .75, 0, size.height * .78)
      ..close();
    canvas.drawPath(leftHand, white);
    canvas.drawPath(leftHand, outline);

    final sleevePath = Path()
      ..moveTo(0, size.height * .64)
      ..lineTo(size.width * .2, size.height * .58)
      ..lineTo(size.width * .22, size.height * .76)
      ..lineTo(0, size.height * .8)
      ..close();
    canvas.drawPath(sleevePath, sleeve);

    canvas.save();
    canvas.translate(size.width * .37, size.height * .4);
    canvas.rotate(-.45);
    final back = RRect.fromRectAndRadius(
        Rect.fromLTWH(-4, 8, size.width * .16, size.height * .42),
        const Radius.circular(8));
    canvas.drawRRect(back, ticketBack);
    final ticket = RRect.fromRectAndRadius(
        Rect.fromLTWH(0, 0, size.width * .16, size.height * .42),
        const Radius.circular(8));
    canvas.drawRRect(ticket, ticketPaint);
    canvas.drawRRect(ticket, outline);
    final star = Path();
    for (int i = 0; i < 10; i++) {
      final angle = -1.57 + i * .628;
      final radius = i.isEven ? 18.0 : 7.0;
      final point = Offset(size.width * .08 + radius * cos(angle),
          size.height * .2 + radius * sin(angle));
      if (i == 0) {
        star.moveTo(point.dx, point.dy);
      } else {
        star.lineTo(point.dx, point.dy);
      }
    }
    star.close();
    canvas.drawPath(star, white);
    canvas.restore();

    final rightHand = Path()
      ..moveTo(size.width, size.height * .77)
      ..cubicTo(size.width * .88, size.height * .72, size.width * .83,
          size.height * .62, size.width * .75, size.height * .68)
      ..lineTo(size.width * .55, size.height * .55)
      ..lineTo(size.width * .52, size.height * .68)
      ..lineTo(size.width * .74, size.height * .85)
      ..cubicTo(size.width * .86, size.height * .92, size.width * .92,
          size.height * .88, size.width, size.height * .94)
      ..close();
    canvas.drawPath(rightHand, white);
    canvas.drawPath(rightHand, outline);

    canvas.save();
    canvas.translate(size.width * .58, size.height * .52);
    canvas.rotate(.22);
    final cash = RRect.fromRectAndRadius(
        Rect.fromLTWH(0, 0, size.width * .2, size.height * .34),
        const Radius.circular(4));
    canvas.drawRRect(cash, money);
    canvas.drawRRect(cash, outline);
    canvas.drawCircle(
        Offset(size.width * .1, size.height * .17), size.height * .08, outline);
    canvas.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _NewsletterPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final pink = Paint()..color = const Color(0xFFFF2D7A);
    final purple = Paint()..color = const Color(0xFF7C3AED);
    final green = Paint()..color = const Color(0xFFC8F000);
    final skin = Paint()..color = const Color(0xFFFFD6B8);
    final black = Paint()..color = const Color(0xFF050507);

    canvas.drawCircle(Offset(size.width * .22, size.height * .18), 18, green);
    canvas.drawCircle(Offset(size.width * .1, size.height * .35), 10, pink);

    final body = RRect.fromRectAndRadius(
      Rect.fromLTWH(size.width * .22, size.height * .38, size.width * .34,
          size.height * .52),
      const Radius.circular(24),
    );
    canvas.drawRRect(body, pink);
    final bag = RRect.fromRectAndRadius(
      Rect.fromLTWH(size.width * .36, size.height * .48, size.width * .24,
          size.height * .26),
      const Radius.circular(12),
    );
    canvas.drawRRect(bag, purple);

    canvas.drawCircle(Offset(size.width * .36, size.height * .31), 24, skin);
    final hair = Path()
      ..moveTo(size.width * .28, size.height * .3)
      ..cubicTo(size.width * .24, size.height * .16, size.width * .45,
          size.height * .13, size.width * .48, size.height * .3)
      ..cubicTo(size.width * .42, size.height * .26, size.width * .37,
          size.height * .37, size.width * .28, size.height * .3);
    canvas.drawPath(hair, black);

    final book = RRect.fromRectAndRadius(
      Rect.fromLTWH(size.width * .5, size.height * .18, size.width * .36,
          size.height * .26),
      const Radius.circular(4),
    );
    canvas.drawRRect(book, Paint()..color = const Color(0xFFFFF6C7));
    canvas.drawLine(Offset(size.width * .68, size.height * .18),
        Offset(size.width * .68, size.height * .44), purple..strokeWidth = 3);
    canvas.drawRect(
        Rect.fromLTWH(size.width * .56, size.height * .25, 28, 18), green);
    canvas.drawRect(
        Rect.fromLTWH(size.width * .72, size.height * .24, 30, 18), pink);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _ZigZagPainter extends CustomPainter {
  const _ZigZagPainter(this.color);

  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 5;
    final path = Path();
    const step = 16.0;
    for (double x = 0; x <= size.width + step; x += step) {
      if (x == 0) {
        path.moveTo(x, size.height * .45);
      } else {
        path.lineTo(x - step / 2, size.height * .2);
        path.lineTo(x, size.height * .45);
      }
    }
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _NetworkImage extends StatelessWidget {
  const _NetworkImage({required this.url, required this.icon});

  final String? url;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    if (url == null || url!.isEmpty) {
      return ColoredBox(
        color: const Color(0xFF1A1B20),
        child:
            Center(child: Icon(icon, color: const Color(0xFF5D606B), size: 42)),
      );
    }
    return CachedNetworkImage(
      imageUrl: url!,
      fit: BoxFit.cover,
      errorWidget: (_, __, ___) => ColoredBox(
        color: const Color(0xFF1A1B20),
        child:
            Center(child: Icon(icon, color: const Color(0xFF5D606B), size: 42)),
      ),
    );
  }
}
