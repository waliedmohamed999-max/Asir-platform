import 'package:flutter/material.dart';

import '../../core/theme/app_tokens.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({required this.event, super.key});

  final Map<String, dynamic> event;

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  int step = 0;
  final methods = ['Apple Pay', 'Google Pay', 'Mada', 'STC Pay', 'Stripe'];
  String method = 'Mada';
  late final List<Map<String, dynamic>> tickets;
  late final Map<int, int> quantities;

  @override
  void initState() {
    super.initState();
    tickets = List<Map<String, dynamic>>.from(
      widget.event['tickets'] as List? ?? [],
    );
    quantities = {
      for (final ticket in tickets) (ticket['id'] as num).toInt(): 0,
    };

    final selectedId = (widget.event['selected_ticket_id'] as num?)?.toInt();
    if (selectedId != null && quantities.containsKey(selectedId)) {
      quantities[selectedId] = 1;
    } else if (tickets.isNotEmpty) {
      quantities[(tickets.first['id'] as num).toInt()] = 1;
    }
  }

  double get subtotal {
    return tickets.fold(0, (sum, ticket) {
      final id = (ticket['id'] as num).toInt();
      final qty = quantities[id] ?? 0;
      final price = (ticket['price'] as num?)?.toDouble() ?? 0;
      return sum + (price * qty);
    });
  }

  int get totalTickets => quantities.values.fold(0, (sum, qty) => sum + qty);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('إتمام الحجز')),
      bottomNavigationBar: SafeArea(
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: theme.scaffoldBackgroundColor,
            boxShadow: [AppTokens.softShadow(context)],
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('$totalTickets تذكرة',
                        style: theme.textTheme.labelMedium),
                    Text(
                      '${subtotal.toStringAsFixed(2)} SAR',
                      style: theme.textTheme.titleLarge
                          ?.copyWith(fontWeight: FontWeight.w900),
                    ),
                  ],
                ),
              ),
              FilledButton.icon(
                onPressed: totalTickets == 0
                    ? null
                    : () => setState(() => step = step < 2 ? step + 1 : step),
                icon: Icon(step == 2 ? Icons.verified : Icons.arrow_forward),
                label: Text(step == 2 ? 'تأكيد الحجز' : 'التالي'),
              ),
            ],
          ),
        ),
      ),
      body: Stepper(
        currentStep: step,
        onStepTapped: (value) => setState(() => step = value),
        onStepContinue: totalTickets == 0
            ? null
            : () => setState(() => step = step < 2 ? step + 1 : step),
        onStepCancel: () => setState(() => step = step > 0 ? step - 1 : step),
        controlsBuilder: (context, details) => const SizedBox.shrink(),
        steps: [
          Step(
            title: const Text('اختيار التذاكر'),
            isActive: step >= 0,
            content: Column(
              children: tickets.isEmpty
                  ? [const _EmptyTickets()]
                  : tickets.map((ticket) {
                      final id = (ticket['id'] as num).toInt();
                      return _CheckoutTicketCard(
                        ticket: ticket,
                        quantity: quantities[id] ?? 0,
                        onChanged: (value) {
                          setState(() => quantities[id] = value);
                        },
                      );
                    }).toList(),
            ),
          ),
          Step(
            title: const Text('طريقة الدفع'),
            isActive: step >= 1,
            content: Wrap(
              spacing: 8,
              runSpacing: 8,
              children: methods
                  .map((item) => ChoiceChip(
                        label: Text(item),
                        selected: method == item,
                        onSelected: (_) => setState(() => method = item),
                      ))
                  .toList(),
            ),
          ),
          Step(
            title: const Text('المراجعة'),
            isActive: step >= 2,
            content: _OrderSummary(
              event: widget.event,
              tickets: tickets,
              quantities: quantities,
              method: method,
              subtotal: subtotal,
            ),
          ),
        ],
      ),
    );
  }
}

class _CheckoutTicketCard extends StatelessWidget {
  const _CheckoutTicketCard({
    required this.ticket,
    required this.quantity,
    required this.onChanged,
  });

  final Map<String, dynamic> ticket;
  final int quantity;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final color = _ticketColor(ticket['label_color']?.toString());
    final remaining = (ticket['remaining_quantity'] as num?)?.toInt() ?? 0;
    final limit = (ticket['purchase_limit_per_user'] as num?)?.toInt();
    final maxAllowed = [
      remaining,
      if (limit != null) limit,
    ].reduce((a, b) => a < b ? a : b);
    final available = ticket['is_available'] == true && maxAllowed > 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: theme.cardColor,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: color.withOpacity(.28)),
      ),
      child: Column(
        children: [
          Row(
            children: [
              Container(
                width: 42,
                height: 42,
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
                    Text(ticket['name']?.toString() ?? 'Ticket',
                        style: theme.textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.w900)),
                    const SizedBox(height: 3),
                    Text(
                      ticket['availability_label']?.toString() ??
                          (available ? 'Available' : 'Unavailable'),
                      style: theme.textTheme.bodySmall
                          ?.copyWith(color: theme.hintColor),
                    ),
                  ],
                ),
              ),
              Text(
                ticket['price_label']?.toString() ??
                    '${ticket['price'] ?? 0} SAR',
                style: theme.textTheme.titleMedium
                    ?.copyWith(fontWeight: FontWeight.w900),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Text(
                  '$remaining مقعد متبقي • حد الشراء ${limit ?? 'مفتوح'}',
                  style: theme.textTheme.bodySmall,
                ),
              ),
              _QtyButton(
                icon: Icons.remove,
                onTap: quantity <= 0 ? null : () => onChanged(quantity - 1),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14),
                child: Text('$quantity',
                    style: theme.textTheme.titleMedium
                        ?.copyWith(fontWeight: FontWeight.w900)),
              ),
              _QtyButton(
                icon: Icons.add,
                onTap: !available || quantity >= maxAllowed
                    ? null
                    : () => onChanged(quantity + 1),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _QtyButton extends StatelessWidget {
  const _QtyButton({required this.icon, this.onTap});

  final IconData icon;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return IconButton.filledTonal(
      onPressed: onTap,
      icon: Icon(icon),
      constraints: const BoxConstraints.tightFor(width: 38, height: 38),
      padding: EdgeInsets.zero,
    );
  }
}

class _OrderSummary extends StatelessWidget {
  const _OrderSummary({
    required this.event,
    required this.tickets,
    required this.quantities,
    required this.method,
    required this.subtotal,
  });

  final Map<String, dynamic> event;
  final List<Map<String, dynamic>> tickets;
  final Map<int, int> quantities;
  final String method;
  final double subtotal;

  @override
  Widget build(BuildContext context) {
    final selected = tickets.where((ticket) {
      final id = (ticket['id'] as num).toInt();
      return (quantities[id] ?? 0) > 0;
    }).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(event['title']?.toString() ?? '',
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(fontWeight: FontWeight.w900)),
        const SizedBox(height: 12),
        ...selected.map((ticket) {
          final id = (ticket['id'] as num).toInt();
          final qty = quantities[id] ?? 0;
          final price = (ticket['price'] as num?)?.toDouble() ?? 0;
          return ListTile(
            dense: true,
            contentPadding: EdgeInsets.zero,
            title: Text(ticket['name']?.toString() ?? ''),
            subtitle: Text('x$qty'),
            trailing: Text('${(price * qty).toStringAsFixed(2)} SAR'),
          );
        }),
        const Divider(),
        ListTile(
          contentPadding: EdgeInsets.zero,
          title: const Text('طريقة الدفع'),
          trailing: Text(method),
        ),
        ListTile(
          contentPadding: EdgeInsets.zero,
          title: const Text('الإجمالي'),
          trailing: Text(
            '${subtotal.toStringAsFixed(2)} SAR',
            style: const TextStyle(fontWeight: FontWeight.w900),
          ),
        ),
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: () {},
          icon: const Icon(Icons.verified),
          label: const Text('تأكيد الحجز'),
        ),
      ],
    );
  }
}

class _EmptyTickets extends StatelessWidget {
  const _EmptyTickets();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.all(16),
      child: Text('لا توجد تذاكر متاحة للحجز حالياً.'),
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
