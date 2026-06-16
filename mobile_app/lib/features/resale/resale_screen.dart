import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/i18n/strings.dart';
import '../app/app_state.dart';
import '../bookings/bookings_screen.dart';

class ResaleScreen extends ConsumerWidget {
  const ResaleScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = AppStrings.of(context);
    final auth = ref.watch(authProvider);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(title: Text(s.resale)),
        body: Center(
          child: FilledButton.icon(
            onPressed: () => context.push('/auth'),
            icon: const Icon(Icons.login),
            label: Text(s.t('loginRequired')),
          ),
        ),
      );
    }

    final bookings = ref.watch(bookingsProvider);

    return Scaffold(
      appBar: AppBar(title: Text(s.resale)),
      body: bookings.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => _ResaleError(
          message: error.toString(),
          onRetry: () => ref.refresh(bookingsProvider.future),
        ),
        data: (data) {
          final tickets = _resaleTickets(data);
          final activeCount =
              tickets.where((ticket) => ticket.isEligible).length;
          final totalValue =
              tickets.fold<double>(0, (sum, ticket) => sum + ticket.lineTotal);

          return RefreshIndicator(
            onRefresh: () => ref.refresh(bookingsProvider.future),
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 18, 16, 110),
              children: [
                _ResaleHero(
                  activeCount: activeCount,
                  totalTickets: tickets.length,
                  totalValue: totalValue,
                ),
                const SizedBox(height: 14),
                _QuickActions(
                  onBookings: () => context.go('/bookings'),
                  onBalance: () => _showComingSoon(context, 'الرصيد'),
                ),
                const SizedBox(height: 18),
                Text(
                  s.t('resaleLists'),
                  style: Theme.of(context)
                      .textTheme
                      .headlineSmall
                      ?.copyWith(fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 12),
                if (tickets.isEmpty)
                  _EmptyResale(onBookings: () => context.go('/bookings'))
                else
                  ...tickets.map(
                    (ticket) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _ResaleTicketCard(
                        ticket: ticket,
                        onSell: () => _createListing(context, ref, ticket),
                      ),
                    ),
                  ),
                const SizedBox(height: 10),
                _HowItWorks(
                  steps: [
                    _StepData(Icons.confirmation_number_outlined,
                        s.t('myTickets'), 'اختر تذكرة مدفوعة ولم تنته بعد'),
                    _StepData(Icons.sell_outlined, s.t('resale'),
                        'حدد سعر العرض وانتظر طلب الشراء'),
                    _StepData(Icons.account_balance_wallet_outlined,
                        s.t('balance'), 'بعد اكتمال البيع يظهر الرصيد هنا'),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  List<_ResaleTicket> _resaleTickets(List<dynamic> bookings) {
    final result = <_ResaleTicket>[];

    for (final bookingValue in bookings) {
      if (bookingValue is! Map) continue;
      final booking = Map<String, dynamic>.from(bookingValue);
      final event = booking['event'] is Map
          ? Map<String, dynamic>.from(booking['event'] as Map)
          : <String, dynamic>{};
      final items =
          List<Map<String, dynamic>>.from(booking['items'] as List? ?? []);

      for (final item in items) {
        result.add(_ResaleTicket(
          bookingItemId: (item['id'] as num?)?.toInt() ?? 0,
          bookingReference: booking['reference']?.toString() ?? '',
          bookingStatus: booking['status']?.toString() ?? '',
          paymentStatus: booking['payment_status']?.toString() ?? '',
          resaleListing: item['resale_listing'] is Map
              ? Map<String, dynamic>.from(item['resale_listing'] as Map)
              : null,
          eventTitle: event['title']?.toString() ?? 'فعالية غير محددة',
          eventDate: event['starts_at']?.toString() ??
              booking['booking_date']?.toString() ??
              '',
          city: event['city'] is Map
              ? (event['city']['name']?.toString() ?? '')
              : '',
          ticketName: item['ticket_name']?.toString() ?? 'تذكرة',
          quantity: (item['quantity'] as num?)?.toInt() ?? 1,
          unitPrice: (item['unit_price'] as num?)?.toDouble() ?? 0,
          lineTotal: (item['line_total'] as num?)?.toDouble() ?? 0,
          qrToken: item['qr_token']?.toString() ?? '',
        ));
      }
    }

    return result;
  }

  Future<void> _createListing(
      BuildContext context, WidgetRef ref, _ResaleTicket ticket) async {
    if (!ticket.isEligible || ticket.hasListing) {
      final message = ticket.hasListing
          ? 'هذه التذكرة معروضة بالفعل في بوابة إعادة البيع.'
          : 'هذه التذكرة غير قابلة لإعادة البيع حالياً بسبب حالة الحجز أو الدفع.';
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(message)));
      return;
    }

    try {
      final api = ref.read(apiClientProvider);
      await api.dio.post('/resale-listings', data: {
        'booking_item_id': ticket.bookingItemId,
        'price': ticket.lineTotal > 0 ? ticket.lineTotal : ticket.unitPrice,
        'seller_note': 'تم إنشاء القائمة من تطبيق منصة عسير',
      });
      ref.invalidate(bookingsProvider);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم عرض التذكرة في بوابة إعادة البيع.')),
        );
      }
    } catch (error) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('تعذر إنشاء قائمة البيع: $error')),
        );
      }
    }
  }

  void _showComingSoon(BuildContext context, String title) {
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text('$title قيد التجهيز')));
  }
}

class _ResaleTicket {
  const _ResaleTicket({
    required this.bookingItemId,
    required this.bookingReference,
    required this.bookingStatus,
    required this.paymentStatus,
    required this.resaleListing,
    required this.eventTitle,
    required this.eventDate,
    required this.city,
    required this.ticketName,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
    required this.qrToken,
  });

  final int bookingItemId;
  final String bookingReference;
  final String bookingStatus;
  final String paymentStatus;
  final Map<String, dynamic>? resaleListing;
  final String eventTitle;
  final String eventDate;
  final String city;
  final String ticketName;
  final int quantity;
  final double unitPrice;
  final double lineTotal;
  final String qrToken;

  bool get hasListing => resaleListing != null;

  bool get isEligible {
    final paid = paymentStatus == 'paid';
    final activeBooking = bookingStatus == 'paid' ||
        bookingStatus == 'confirmed' ||
        bookingStatus == 'completed';
    return paid &&
        activeBooking &&
        quantity > 0 &&
        !hasListing &&
        bookingItemId > 0;
  }
}

class _ResaleHero extends StatelessWidget {
  const _ResaleHero({
    required this.activeCount,
    required this.totalTickets,
    required this.totalValue,
  });

  final int activeCount;
  final int totalTickets;
  final double totalValue;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFF2B2D35)),
        gradient: const LinearGradient(
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
          colors: [Color(0xFF171823), Color(0xFF0B0C10)],
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(Icons.swap_horiz,
              color: Theme.of(context).colorScheme.primary, size: 42),
          const SizedBox(height: 14),
          Text(
            'مركز إعادة البيع',
            style: Theme.of(context)
                .textTheme
                .headlineSmall
                ?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          const Text(
            'تابع تذاكرك القابلة للبيع، راجع قيمتها، وابدأ من المحفظة مباشرة.',
            style: TextStyle(color: Color(0xFFB4B6C2), height: 1.5),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _MetricTile(
                  label: 'قابلة للبيع',
                  value: '$activeCount',
                  color: const Color(0xFFFF2D7A),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _MetricTile(
                  label: 'كل التذاكر',
                  value: '$totalTickets',
                  color: const Color(0xFF8B5CF6),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _MetricTile(
                  label: 'القيمة',
                  value: totalValue.toStringAsFixed(0),
                  color: const Color(0xFFC8F000),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _MetricTile extends StatelessWidget {
  const _MetricTile({
    required this.label,
    required this.value,
    required this.color,
  });

  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(.05),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.white.withOpacity(.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Color(0xFF9CA0AF), fontSize: 12)),
          const SizedBox(height: 6),
          Text(value,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                  color: color, fontWeight: FontWeight.w900, fontSize: 22)),
        ],
      ),
    );
  }
}

class _QuickActions extends StatelessWidget {
  const _QuickActions({required this.onBookings, required this.onBalance});

  final VoidCallback onBookings;
  final VoidCallback onBalance;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton.icon(
            onPressed: onBookings,
            icon: const Icon(Icons.confirmation_number_outlined),
            label: const Text('تذاكري'),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: onBalance,
            icon: const Icon(Icons.account_balance_wallet_outlined),
            label: const Text('الرصيد'),
          ),
        ),
      ],
    );
  }
}

class _ResaleTicketCard extends StatelessWidget {
  const _ResaleTicketCard({required this.ticket, required this.onSell});

  final _ResaleTicket ticket;
  final VoidCallback onSell;

  @override
  Widget build(BuildContext context) {
    final color =
        ticket.isEligible ? const Color(0xFFFF2D7A) : const Color(0xFF5D606B);
    final buttonLabel = ticket.hasListing
        ? 'معروضة للبيع'
        : (ticket.isEligible ? 'عرض للبيع' : 'غير متاحة للبيع');

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFF111216),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
            color: ticket.isEligible
                ? const Color(0x55FF2D7A)
                : const Color(0xFF2B2D35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: color.withOpacity(.14),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(Icons.confirmation_number_outlined, color: color),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(ticket.eventTitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                            fontWeight: FontWeight.w900, fontSize: 16)),
                    const SizedBox(height: 4),
                    Text(
                      [
                        ticket.ticketName,
                        if (ticket.city.isNotEmpty) ticket.city,
                      ].join(' • '),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: Color(0xFFB4B6C2)),
                    ),
                  ],
                ),
              ),
              _StatusBadge(active: ticket.isEligible),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _InfoCell(label: 'الكمية', value: 'x${ticket.quantity}'),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _InfoCell(
                    label: 'سعر الوحدة',
                    value: '${ticket.unitPrice.toStringAsFixed(2)} ر.س'),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _InfoCell(
                    label: 'الإجمالي',
                    value: '${ticket.lineTotal.toStringAsFixed(2)} ر.س'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFF0B0C10),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFF252833)),
            ),
            child: Text(
              ticket.bookingReference,
              textDirection: TextDirection.ltr,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                  color: Color(0xFFD7D8E0),
                  fontWeight: FontWeight.w800,
                  letterSpacing: .3),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: onSell,
              icon: const Icon(Icons.sell_outlined),
              label: Text(buttonLabel),
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoCell extends StatelessWidget {
  const _InfoCell({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFF0D0E12),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: const Color(0xFF252833)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Color(0xFF8F92A1), fontSize: 11)),
          const SizedBox(height: 5),
          Text(value,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontWeight: FontWeight.w900)),
        ],
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.active});

  final bool active;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: active
            ? const Color(0xFF10B981).withOpacity(.12)
            : const Color(0xFF5D606B).withOpacity(.16),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        active ? 'متاحة' : 'مغلقة',
        style: TextStyle(
          color: active ? const Color(0xFF34D399) : const Color(0xFFB4B6C2),
          fontWeight: FontWeight.w900,
          fontSize: 12,
        ),
      ),
    );
  }
}

class _EmptyResale extends StatelessWidget {
  const _EmptyResale({required this.onBookings});

  final VoidCallback onBookings;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFF111216),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFF2B2D35)),
      ),
      child: Column(
        children: [
          const Icon(Icons.confirmation_number_outlined,
              color: Color(0xFF5D606B), size: 42),
          const SizedBox(height: 10),
          Text(s.t('resaleEmpty'),
              textAlign: TextAlign.center,
              style: const TextStyle(
                  color: Color(0xFFB4B6C2), fontWeight: FontWeight.w800)),
          const SizedBox(height: 14),
          OutlinedButton.icon(
            onPressed: onBookings,
            icon: const Icon(Icons.confirmation_number_outlined),
            label: Text(s.bookings),
          ),
        ],
      ),
    );
  }
}

class _HowItWorks extends StatelessWidget {
  const _HowItWorks({required this.steps});

  final List<_StepData> steps;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: steps
          .map((step) => Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: ListTile(
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                      side: const BorderSide(color: Color(0xFF2B2D35))),
                  tileColor: const Color(0xFF0D0E12),
                  leading: Icon(step.icon),
                  title: Text(step.title,
                      style: const TextStyle(fontWeight: FontWeight.w900)),
                  subtitle: Text(step.subtitle),
                  trailing: const Icon(Icons.chevron_left),
                ),
              ))
          .toList(),
    );
  }
}

class _StepData {
  const _StepData(this.icon, this.title, this.subtitle);

  final IconData icon;
  final String title;
  final String subtitle;
}

class _ResaleError extends StatelessWidget {
  const _ResaleError({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off, size: 42, color: Color(0xFFFF2D7A)),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center),
            const SizedBox(height: 14),
            FilledButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('إعادة المحاولة'),
            ),
          ],
        ),
      ),
    );
  }
}
