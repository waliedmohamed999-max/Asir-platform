import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../core/i18n/strings.dart';
import '../../shared/widgets/platform_logo.dart';
import '../app/app_state.dart';

final bookingsProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final api = ref.watch(apiClientProvider);
  final lang = ref.watch(settingsProvider).locale.languageCode;
  final response =
      await api.dio.get('/wallet', queryParameters: {'lang': lang});
  return List<dynamic>.from(response.data['data'] as List);
});

class BookingsScreen extends ConsumerStatefulWidget {
  const BookingsScreen({super.key});

  @override
  ConsumerState<BookingsScreen> createState() => _BookingsScreenState();
}

class _BookingsScreenState extends ConsumerState<BookingsScreen> {
  String _selectedSection = 'all';
  bool _showPast = false;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    final auth = ref.watch(authProvider);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(title: Text(s.wallet)),
        body: Center(
            child: FilledButton.icon(
                onPressed: () => context.push('/auth'),
                icon: const Icon(Icons.login),
                label: Text(s.t('loginRequired')))),
      );
    }

    final bookings = ref.watch(bookingsProvider);

    return Scaffold(
      body: bookings.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, __) =>
            Center(child: Text(error.toString(), textAlign: TextAlign.center)),
        data: (data) {
          final normalized = data
              .map((item) => Map<String, dynamic>.from(item as Map))
              .toList();
          final visibleBookings = normalized
              .where(
                  (booking) => _showPast ? _isPast(booking) : !_isPast(booking))
              .toList();
          final sections = _sectionsFor(normalized);
          final selected =
              sections.any((section) => section.key == _selectedSection)
                  ? _selectedSection
                  : sections.first.key;
          final filtered = _filterBySection(visibleBookings, selected);

          return RefreshIndicator(
            onRefresh: () => ref.refresh(bookingsProvider.future),
            child: CustomScrollView(
              slivers: [
                SliverToBoxAdapter(
                  child: _BookingsHeader(
                    sections: sections,
                    selected: selected,
                    onSelect: (key) => setState(() => _selectedSection = key),
                  ),
                ),
                if (filtered.isEmpty)
                  SliverFillRemaining(
                    hasScrollBody: false,
                    child: _EmptyBookingsState(
                      showPast: _showPast,
                      onRefresh: () => ref.refresh(bookingsProvider.future),
                      onTogglePast: () =>
                          setState(() => _showPast = !_showPast),
                    ),
                  )
                else
                  SliverPadding(
                    padding: const EdgeInsets.fromLTRB(16, 18, 16, 120),
                    sliver: SliverList.separated(
                      itemCount: filtered.length + 1,
                      separatorBuilder: (_, __) => const SizedBox(height: 12),
                      itemBuilder: (context, index) {
                        if (index == filtered.length) {
                          return _PastBookingsToggle(
                            showPast: _showPast,
                            onTap: () => setState(() => _showPast = !_showPast),
                          );
                        }
                        return _TicketCard(booking: filtered[index]);
                      },
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }

  List<_BookingSection> _sectionsFor(List<Map<String, dynamic>> bookings) {
    final sections = <_BookingSection>[
      const _BookingSection('all', 'الفعاليات', []),
    ];
    final seen = <String>{'all'};

    for (final booking in bookings) {
      final event = booking['event'] is Map
          ? Map<String, dynamic>.from(booking['event'] as Map)
          : <String, dynamic>{};
      final category = event['category'] is Map
          ? Map<String, dynamic>.from(event['category'] as Map)
          : <String, dynamic>{};
      final slug = category['slug']?.toString();
      final name = category['name']?.toString();
      if (slug == null || slug.isEmpty || name == null || name.isEmpty) {
        continue;
      }
      if (seen.add(slug)) {
        sections.add(_BookingSection(slug, name, [slug]));
      }
    }

    for (final section in const [
      _BookingSection('season', 'موسم', ['today', 'concerts', 'shows']),
      _BookingSection('trips', 'الرحلات', ['aviation', 'more']),
      _BookingSection('hotels', 'فنادق', ['hotels']),
      _BookingSection('marine_trips', 'رحلات بحرية', ['experiences']),
      _BookingSection('restaurants', 'المطاعم', ['restaurants']),
    ]) {
      if (seen.add(section.key)) {
        sections.add(section);
      }
    }

    return sections;
  }

  List<Map<String, dynamic>> _filterBySection(
      List<Map<String, dynamic>> bookings, String sectionKey) {
    if (sectionKey == 'all') return bookings;
    final sections = _sectionsFor(bookings);
    final section = sections.firstWhere(
      (item) => item.key == sectionKey,
      orElse: () => _BookingSection(sectionKey, sectionKey, [sectionKey]),
    );
    return bookings.where((booking) {
      final event = booking['event'] is Map
          ? Map<String, dynamic>.from(booking['event'] as Map)
          : <String, dynamic>{};
      final category = event['category'] is Map
          ? Map<String, dynamic>.from(event['category'] as Map)
          : <String, dynamic>{};
      final slug = category['slug']?.toString() ?? '';
      return section.categorySlugs.contains(slug);
    }).toList();
  }

  bool _isPast(Map<String, dynamic> booking) {
    final event = booking['event'] is Map
        ? Map<String, dynamic>.from(booking['event'] as Map)
        : <String, dynamic>{};
    final dateText = event['ends_at']?.toString() ??
        event['starts_at']?.toString() ??
        booking['booking_date']?.toString();
    if (dateText == null || dateText.isEmpty) return false;
    final date = DateTime.tryParse(dateText);
    if (date == null) return false;
    return date.isBefore(DateTime.now());
  }
}

class _BookingSection {
  const _BookingSection(this.key, this.label, this.categorySlugs);

  final String key;
  final String label;
  final List<String> categorySlugs;
}

class _BookingsHeader extends StatelessWidget {
  const _BookingsHeader({
    required this.sections,
    required this.selected,
    required this.onSelect,
  });

  final List<_BookingSection> sections;
  final String selected;
  final ValueChanged<String> onSelect;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Color(0xFF07080B),
        border: Border(bottom: BorderSide(color: Color(0xFF242630))),
      ),
      child: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  IconButton(
                    onPressed: () {},
                    icon: const Icon(Icons.more_vert,
                        color: Colors.white, size: 32),
                  ),
                  const Spacer(),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const PlatformLogo(size: 74),
                      const SizedBox(height: 18),
                      Text(
                        'الحجوزات',
                        style:
                            Theme.of(context).textTheme.displaySmall?.copyWith(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  height: 1,
                                ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            SizedBox(
              height: 70,
              child: ListView.separated(
                padding: const EdgeInsets.symmetric(horizontal: 18),
                scrollDirection: Axis.horizontal,
                itemCount: sections.length,
                separatorBuilder: (_, __) => const SizedBox(width: 26),
                itemBuilder: (context, index) {
                  final section = sections[index];
                  final isActive = selected == section.key;
                  return InkWell(
                    onTap: () => onSelect(section.key),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        Text(
                          section.label,
                          style: TextStyle(
                            color: isActive
                                ? Colors.white
                                : const Color(0xFF737681),
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(height: 18),
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 180),
                          width: isActive ? 92 : 0,
                          height: 3,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(999),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _EmptyBookingsState extends StatelessWidget {
  const _EmptyBookingsState({
    required this.showPast,
    required this.onRefresh,
    required this.onTogglePast,
  });

  final bool showPast;
  final VoidCallback onRefresh;
  final VoidCallback onTogglePast;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Expanded(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 32),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.confirmation_number,
                      size: 64, color: Colors.white),
                  const SizedBox(height: 22),
                  Text(
                    showPast
                        ? 'لا يوجد أي حجوزات سابقة.'
                        : 'لا يوجد أي حجوزات\nقادمة.',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          height: 1.45,
                        ),
                  ),
                  const SizedBox(height: 28),
                  OutlinedButton(
                    onPressed: onRefresh,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: const BorderSide(color: Colors.white),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 26, vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text('تحديث الصفحة',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w900)),
                  ),
                ],
              ),
            ),
          ),
        ),
        _PastBookingsToggle(showPast: showPast, onTap: onTogglePast),
        const SizedBox(height: 110),
      ],
    );
  }
}

class _PastBookingsToggle extends StatelessWidget {
  const _PastBookingsToggle({required this.showPast, required this.onTap});

  final bool showPast;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(18, 26, 18, 24),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: Color(0xFF242630))),
      ),
      child: Column(
        children: [
          TextButton(
            onPressed: onTap,
            child: Text(
              showPast ? 'عرض الحجوزات القادمة' : 'عرض الحجوزات السابقة',
              style: const TextStyle(
                decoration: TextDecoration.underline,
                color: Colors.white,
                fontSize: 20,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
          const SizedBox(height: 18),
          TextButton(
            onPressed: () {},
            child: const Text(
              'لماذا لا استطيع الوصول الى تذاكري؟',
              style: TextStyle(
                decoration: TextDecoration.underline,
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TicketCard extends StatelessWidget {
  const _TicketCard({required this.booking});

  final Map<String, dynamic> booking;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    final event = booking['event'] is Map
        ? Map<String, dynamic>.from(booking['event'] as Map)
        : <String, dynamic>{};
    final items =
        List<Map<String, dynamic>>.from(booking['items'] as List? ?? []);
    final firstItem = items.isNotEmpty ? items.first : <String, dynamic>{};
    final qr = items.isNotEmpty
        ? items.first['qr_token']?.toString() ?? booking['reference'].toString()
        : booking['reference'].toString();
    final quantity = items.fold<int>(
        0, (sum, item) => sum + ((item['quantity'] as num?)?.toInt() ?? 0));
    final ticketNames = items
        .map((item) => item['ticket_name']?.toString() ?? '')
        .where((name) => name.isNotEmpty)
        .take(2)
        .join('، ');
    final status =
        _statusLabel(context, booking['status']?.toString() ?? 'active');
    final statusColor = _statusColor(booking['status']?.toString() ?? 'active');
    final image = event['banner_image_url'] ??
        event['image_url'] ??
        ((event['gallery'] is List && (event['gallery'] as List).isNotEmpty)
            ? (event['gallery'] as List).first['url']
            : null);
    final city = event['city'] is Map ? event['city']['name']?.toString() : '';
    final category =
        event['category'] is Map ? event['category']['name']?.toString() : '';
    final total = (booking['total_amount'] as num?)?.toDouble() ?? 0;
    final reference = booking['reference']?.toString() ?? '';

    return ClipRRect(
      borderRadius: BorderRadius.circular(22),
      child: Container(
        decoration: BoxDecoration(
          color: const Color(0xFF111216),
          border: Border.all(color: const Color(0xFF2B2D35)),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFFFF2D7A).withOpacity(.12),
              blurRadius: 28,
              offset: const Offset(0, 16),
            ),
          ],
        ),
        child: Stack(
          children: [
            Positioned.fill(
              child: const DecoratedBox(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topRight,
                    end: Alignment.bottomLeft,
                    colors: [
                      Color(0xFF272136),
                      Color(0xFF151521),
                      Color(0xFF07080B),
                    ],
                  ),
                ),
              ),
            ),
            PositionedDirectional(
              top: -48,
              end: -38,
              child: Container(
                width: 190,
                height: 190,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0x33FF2D7A), width: 28),
                ),
              ),
            ),
            PositionedDirectional(
              bottom: 72,
              start: -48,
              child: Container(
                width: 150,
                height: 150,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: const Color(0xFF7C3AED).withOpacity(.22),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const PlatformLogo(size: 40),
                      const SizedBox(width: 10),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('منصة عسير',
                                style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 16)),
                            Text('ASEER PLATFORM PASS',
                                textDirection: TextDirection.ltr,
                                style: TextStyle(
                                    color: Color(0xFFB4B6C2),
                                    fontWeight: FontWeight.w800,
                                    fontSize: 11,
                                    letterSpacing: .8)),
                          ],
                        ),
                      ),
                      _EventMiniArt(image: image?.toString()),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: _PassStatusBadge(label: status, color: statusColor),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              event['title']?.toString() ??
                                  booking['reference'].toString(),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: Theme.of(context)
                                  .textTheme
                                  .headlineSmall
                                  ?.copyWith(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w900,
                                      height: 1.05),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'بطاقة دخول رقمية موثقة',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: Color(0xFFC9CAD3),
                                fontWeight: FontWeight.w700,
                                fontSize: 12,
                              ),
                            ),
                            const SizedBox(height: 10),
                            Wrap(
                              spacing: 7,
                              runSpacing: 7,
                              children: [
                                if ((city ?? '').isNotEmpty)
                                  _TicketChip(
                                      icon: Icons.location_on_outlined,
                                      label: city!),
                                if ((category ?? '').isNotEmpty)
                                  _TicketChip(
                                      icon: Icons.category_outlined,
                                      label: category!),
                                _TicketChip(
                                    icon: Icons.confirmation_number_outlined,
                                    label: '$quantity تذكرة'),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 14),
                      Container(
                        padding: const EdgeInsets.all(7),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(18),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(.22),
                              blurRadius: 18,
                              offset: const Offset(0, 10),
                            ),
                          ],
                        ),
                        child: QrImageView(
                          data: qr,
                          size: 82,
                          padding: EdgeInsets.zero,
                          backgroundColor: Colors.white,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),
                  _Perforation(reference: reference),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: _PassInfo(
                          label: 'نوع التذكرة',
                          value: ticketNames.isNotEmpty
                              ? ticketNames
                              : (firstItem['ticket_name']?.toString() ??
                                  'تذكرة'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _PassInfo(
                          label: 'تاريخ الحجز',
                          value: _shortDate(booking['booking_date']),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: _PassInfo(
                          label: 'الإجمالي',
                          value: '${total.toStringAsFixed(2)} ر.س',
                          valueColor: const Color(0xFFC8F000),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _PassInfo(
                          label: 'رقم الحجز',
                          value: reference,
                          ltr: true,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: FilledButton.icon(
                          onPressed: () => _showTicketDetails(context, qr),
                          icon: const Icon(Icons.qr_code_2),
                          label: const Text('عرض الكود'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(s.t('downloadPdf'))));
                          },
                          icon: const Icon(Icons.picture_as_pdf, size: 18),
                          label: Text(s.t('downloadPdf')),
                        ),
                      ),
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

  String _statusLabel(BuildContext context, String status) {
    final s = AppStrings.of(context);
    if (status == 'used') return s.t('used');
    if (status == 'expired' || status == 'cancelled') return s.t('expired');
    return s.t('active');
  }

  Color _statusColor(String status) {
    if (status == 'used' || status == 'completed')
      return const Color(0xFF8B5CF6);
    if (status == 'expired' || status == 'cancelled')
      return const Color(0xFFEF4444);
    return const Color(0xFF34D399);
  }

  String _shortDate(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 10) return text.substring(0, 10);
    return text.isEmpty ? 'غير محدد' : text;
  }

  void _showTicketDetails(BuildContext context, String qr) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF111216),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 34),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('كود دخول التذكرة',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
              ),
              child: QrImageView(
                data: qr,
                size: 220,
                padding: EdgeInsets.zero,
                backgroundColor: Colors.white,
              ),
            ),
            const SizedBox(height: 14),
            Text(qr,
                textDirection: TextDirection.ltr,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    color: Color(0xFFB4B6C2), fontWeight: FontWeight.w800)),
          ],
        ),
      ),
    );
  }
}

class _PassStatusBadge extends StatelessWidget {
  const _PassStatusBadge({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: color.withOpacity(.15),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: color.withOpacity(.45)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w900,
              fontSize: 12,
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
        color: Colors.white.withOpacity(.1),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withOpacity(.12)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: const Color(0xFFD7D8E0)),
          const SizedBox(width: 5),
          Text(label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                  color: Color(0xFFE9EAF0),
                  fontSize: 12,
                  fontWeight: FontWeight.w800)),
        ],
      ),
    );
  }
}

class _EventMiniArt extends StatelessWidget {
  const _EventMiniArt({required this.image});

  final String? image;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 74,
      height: 52,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white.withOpacity(.16)),
        color: Colors.white.withOpacity(.08),
      ),
      child: image == null || image!.isEmpty
          ? const Icon(Icons.local_activity_outlined,
              color: Color(0xFFD7D8E0), size: 28)
          : CachedNetworkImage(
              imageUrl: image!,
              fit: BoxFit.cover,
              errorWidget: (_, __, ___) => const Icon(
                Icons.local_activity_outlined,
                color: Color(0xFFD7D8E0),
                size: 28,
              ),
            ),
    );
  }
}

class _Perforation extends StatelessWidget {
  const _Perforation({required this.reference});

  final String reference;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: CustomPaint(
            painter: _DashPainter(),
            child: const SizedBox(height: 1),
          ),
        ),
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 10),
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(.08),
            borderRadius: BorderRadius.circular(999),
          ),
          child: Text(
            reference,
            textDirection: TextDirection.ltr,
            style: const TextStyle(
                color: Color(0xFFB4B6C2),
                fontSize: 11,
                fontWeight: FontWeight.w900),
          ),
        ),
        Expanded(
          child: CustomPaint(
            painter: _DashPainter(),
            child: const SizedBox(height: 1),
          ),
        ),
      ],
    );
  }
}

class _DashPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0x66FFFFFF)
      ..strokeWidth = 1;
    const dash = 6.0;
    const gap = 5.0;
    var x = 0.0;
    while (x < size.width) {
      canvas.drawLine(Offset(x, 0), Offset(x + dash, 0), paint);
      x += dash + gap;
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _PassInfo extends StatelessWidget {
  const _PassInfo({
    required this.label,
    required this.value,
    this.valueColor = Colors.white,
    this.ltr = false,
  });

  final String label;
  final String value;
  final Color valueColor;
  final bool ltr;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(11),
      decoration: BoxDecoration(
        color: Colors.black.withOpacity(.22),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white.withOpacity(.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Color(0xFF9CA0AF), fontSize: 11)),
          const SizedBox(height: 5),
          Text(value,
              textDirection: ltr ? TextDirection.ltr : null,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                  color: valueColor,
                  fontWeight: FontWeight.w900,
                  fontSize: 13)),
        ],
      ),
    );
  }
}
