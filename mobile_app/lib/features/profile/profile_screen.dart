import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/i18n/strings.dart';
import '../app/app_state.dart';

final meProvider =
    FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.dio.get('/profile');
  return Map<String, dynamic>.from(response.data['data'] as Map);
});

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = AppStrings.of(context);
    final auth = ref.watch(authProvider);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(title: Text(s.account)),
        body: Center(
            child: FilledButton.icon(
                onPressed: () => context.push('/auth'),
                icon: const Icon(Icons.login),
                label: Text(s.login))),
      );
    }

    final me = ref.watch(meProvider);

    return Scaffold(
      appBar: AppBar(title: Text(s.account)),
      body: me.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => _Message(
            message: error.toString(), action: () => ref.refresh(meProvider)),
        data: (user) => RefreshIndicator(
          onRefresh: () => ref.refresh(meProvider.future),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 110),
            children: [
              _ProfileCard(
                  user: user,
                  onEdit: () => _showEditProfile(context, ref, user)),
              const SizedBox(height: 26),
              _CardsPanel(),
              const SizedBox(height: 24),
              _BalancePanel(),
              const SizedBox(height: 24),
              const _DividerWave(),
              const SizedBox(height: 20),
              _MenuSection(
                title: s.account,
                items: [
                  _MenuItem(Icons.gavel_outlined, s.t('auctionOffers'), () {}),
                  _MenuItem(Icons.redeem_outlined, s.t('coupons'), () {}),
                  _MenuItem(Icons.live_tv_outlined, s.t('livePaid'), () {}),
                  _MenuItem(Icons.favorite_border, s.t('favorites'), () {}),
                  _MenuItem(Icons.swap_horiz, s.t('resaleLists'),
                      () => context.go('/resale')),
                  _MenuItem(Icons.account_circle_outlined, s.t('accountInfo'),
                      () => _showEditProfile(context, ref, user)),
                  _MenuItem(
                      Icons.accessible_outlined, s.t('accessibility'), () {}),
                  _MenuItem(Icons.flight_takeoff, s.t('travelers'), () {}),
                ],
              ),
              const SizedBox(height: 18),
              _MenuSection(
                title: s.t('settings'),
                items: [
                  _MenuItem(Icons.notifications_outlined, s.t('notifications'),
                      () {}),
                  _MenuItem(
                      Icons.devices_outlined, s.t('trustedDevices'), () {}),
                  _MenuItem(Icons.apps_outlined, s.t('appIcon'), () {}),
                ],
              ),
              const SizedBox(height: 18),
              _Preferences(
                  onLanguage: (locale) => ref
                      .read(settingsProvider.notifier)
                      .setLocale(Locale(locale)),
                  s: s),
              const SizedBox(height: 22),
              _MoreSection(s: s),
              const SizedBox(height: 18),
              _RatingPanel(s: s),
              const SizedBox(height: 18),
              FilledButton.tonalIcon(
                onPressed: () async {
                  await ref.read(authProvider.notifier).logout();
                  if (context.mounted) context.go('/auth');
                },
                icon: const Icon(Icons.logout),
                label: Text(s.t('logout')),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _showEditProfile(
      BuildContext context, WidgetRef ref, Map<String, dynamic> user) async {
    final s = AppStrings.of(context);
    final name = TextEditingController(text: user['name']?.toString() ?? '');
    final email = TextEditingController(text: user['email']?.toString() ?? '');
    final phone = TextEditingController(text: user['phone']?.toString() ?? '');

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF111216),
      builder: (context) => Padding(
        padding: EdgeInsets.fromLTRB(
            16, 16, 16, MediaQuery.viewInsetsOf(context).bottom + 16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(s.t('editProfile'),
                style: Theme.of(context)
                    .textTheme
                    .titleLarge
                    ?.copyWith(fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            TextField(
                controller: name,
                decoration: InputDecoration(labelText: s.t('name'))),
            TextField(
                controller: email,
                decoration: InputDecoration(labelText: s.t('email'))),
            TextField(
                controller: phone,
                decoration: InputDecoration(labelText: s.t('phone'))),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: () async {
                  await ref.read(apiClientProvider).dio.put('/profile', data: {
                    'name': name.text,
                    'email': email.text,
                    'phone': phone.text
                  });
                  ref.invalidate(meProvider);
                  await ref.read(authProvider.notifier).refreshProfile();
                  if (context.mounted) Navigator.pop(context);
                },
                child: Text(s.t('save')),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProfileCard extends StatelessWidget {
  const _ProfileCard({required this.user, required this.onEdit});

  final Map<String, dynamic> user;
  final VoidCallback onEdit;

  @override
  Widget build(BuildContext context) {
    final initial =
        (user['name'] ?? 'W').toString().characters.first.toUpperCase();
    return Card(
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(18),
            child: Row(
              children: [
                CircleAvatar(
                    radius: 44,
                    backgroundColor: const Color(0xFFC8F000),
                    child: Text(initial,
                        style: const TextStyle(
                            color: Color(0xFF151515),
                            fontSize: 28,
                            fontWeight: FontWeight.w900))),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(user['name']?.toString() ?? '',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: Theme.of(context)
                              .textTheme
                              .headlineSmall
                              ?.copyWith(fontWeight: FontWeight.w900)),
                      const SizedBox(height: 4),
                      Text(user['email']?.toString() ?? '',
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(color: Color(0xFFB4B6C2))),
                    ],
                  ),
                ),
                TextButton.icon(
                    onPressed: onEdit,
                    icon: const Icon(Icons.chevron_left),
                    label: Text(AppStrings.of(context).t('editProfile'))),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(16),
            color: const Color(0xFF24252A),
            child: Row(
              children: [
                const Icon(Icons.workspace_premium_outlined, size: 34),
                const SizedBox(width: 12),
                Expanded(
                    child: Text('باقة الوناسة',
                        style: Theme.of(context)
                            .textTheme
                            .titleLarge
                            ?.copyWith(fontWeight: FontWeight.w900))),
                FilledButton.icon(
                    onPressed: () {},
                    icon: const Icon(Icons.verified_outlined),
                    label: const Text('ترقية')),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _CardsPanel extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(s.t('myCards'),
            style: Theme.of(context)
                .textTheme
                .headlineSmall
                ?.copyWith(fontWeight: FontWeight.w900)),
        const SizedBox(height: 14),
        Card(
          clipBehavior: Clip.antiAlias,
          child: Column(
            children: [
              Container(
                height: 138,
                decoration: const BoxDecoration(
                  gradient: LinearGradient(colors: [
                    Color(0xFF6721B7),
                    Color(0xFF0C7EC1),
                    Color(0xFFFF2D7A)
                  ]),
                ),
                child: const Center(
                    child: Text('ASEER CARD',
                        style: TextStyle(
                            fontSize: 34,
                            color: Colors.white,
                            fontWeight: FontWeight.w900))),
              ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('بطاقات منصة عسير هنا!',
                        style: Theme.of(context)
                            .textTheme
                            .titleLarge
                            ?.copyWith(fontWeight: FontWeight.w900)),
                    const SizedBox(height: 6),
                    const Text('طريقة ممتعة وسهلة للاستمتاع بتجارب على الأرض!',
                        style: TextStyle(color: Color(0xFFB4B6C2))),
                    const SizedBox(height: 14),
                    Row(
                      children: [
                        Expanded(
                            child: FilledButton(
                                onPressed: () {},
                                child: const Text('إضافة بطاقة رقمية'))),
                        const SizedBox(width: 10),
                        Expanded(
                            child: OutlinedButton(
                                onPressed: () {},
                                child: const Text('شحن البطاقة الفعلية'))),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _BalancePanel extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(s.t('balance'),
            style: Theme.of(context)
                .textTheme
                .titleLarge
                ?.copyWith(fontWeight: FontWeight.w900)),
        const SizedBox(height: 6),
        const Text('﷼ 0',
            style: TextStyle(fontSize: 38, fontWeight: FontWeight.w900)),
        const SizedBox(height: 14),
        SizedBox(
            width: double.infinity,
            height: 56,
            child: OutlinedButton(
                onPressed: () {},
                child: Text(s.t('topUpBalance'),
                    style: const TextStyle(fontSize: 18)))),
      ],
    );
  }
}

class _MenuSection extends StatelessWidget {
  const _MenuSection({required this.title, required this.items});

  final String title;
  final List<_MenuItem> items;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title,
            style: Theme.of(context)
                .textTheme
                .headlineSmall
                ?.copyWith(fontWeight: FontWeight.w900)),
        const SizedBox(height: 8),
        ...items.map((item) => _MenuTile(item: item)),
      ],
    );
  }
}

class _MenuItem {
  const _MenuItem(this.icon, this.title, this.onTap);
  final IconData icon;
  final String title;
  final VoidCallback onTap;
}

class _MenuTile extends StatelessWidget {
  const _MenuTile({required this.item});

  final _MenuItem item;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Icon(item.icon, size: 30),
      title: Text(item.title,
          textAlign: TextAlign.right,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
      trailing: const Icon(Icons.chevron_left),
      onTap: item.onTap,
    );
  }
}

class _Preferences extends StatelessWidget {
  const _Preferences({required this.onLanguage, required this.s});

  final ValueChanged<String> onLanguage;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('تفضيلات التطبيق',
            style: Theme.of(context)
                .textTheme
                .headlineSmall
                ?.copyWith(fontWeight: FontWeight.w900)),
        const SizedBox(height: 10),
        _PreferenceRow(
            icon: Icons.attach_money, title: s.t('currency'), value: 'SAR'),
        _PreferenceRow(
            icon: Icons.language,
            title: s.t('language'),
            value: s.isArabic ? 'AR' : 'EN',
            onTap: () => onLanguage(s.isArabic ? 'en' : 'ar')),
        _PreferenceRow(
            icon: Icons.schedule, title: s.t('timeFormat'), value: '12-ساعة'),
        SwitchListTile(
          contentPadding: EdgeInsets.zero,
          secondary: const Icon(Icons.center_focus_strong),
          title: Text(s.t('quickLogin'),
              style:
                  const TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
          subtitle: Text(s.t('enabledFingerprint')),
          value: true,
          onChanged: (_) {},
        ),
      ],
    );
  }
}

class _PreferenceRow extends StatelessWidget {
  const _PreferenceRow(
      {required this.icon,
      required this.title,
      required this.value,
      this.onTap});

  final IconData icon;
  final String title;
  final String value;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Icon(icon, size: 30),
      title: Text(title,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
      trailing: Text(value,
          style: const TextStyle(
              fontSize: 20,
              decoration: TextDecoration.underline,
              fontWeight: FontWeight.w900)),
      onTap: onTap,
    );
  }
}

class _MoreSection extends StatelessWidget {
  const _MoreSection({required this.s});

  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    final items = [
      _MenuItem(Icons.support_agent_outlined, s.t('support'), () {}),
      _MenuItem(Icons.share_outlined, s.t('shareApp'), () {}),
      _MenuItem(Icons.info_outline, s.t('aboutUs'), () {}),
      _MenuItem(Icons.security_outlined, s.t('securityReport'), () {}),
    ];
    return _MenuSection(title: s.t('more'), items: items);
  }
}

class _RatingPanel extends StatelessWidget {
  const _RatingPanel({required this.s});

  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          children: [
            Text(s.t('rateExperience'),
                style: Theme.of(context)
                    .textTheme
                    .headlineSmall
                    ?.copyWith(fontWeight: FontWeight.w900)),
            const SizedBox(height: 8),
            const Text('ساعدنا على تحسين تجربتك وجعلها أكثر متعة',
                style: TextStyle(color: Color(0xFFB4B6C2))),
            const SizedBox(height: 22),
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _RateFace(face: '😍', label: 'حبيت'),
                _RateFace(face: '😊', label: 'مو مرة'),
                _RateFace(face: '😡', label: 'خلني ساكت!'),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _RateFace extends StatelessWidget {
  const _RateFace({required this.face, required this.label});

  final String face;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(face, style: const TextStyle(fontSize: 54)),
        Text(label,
            style: const TextStyle(
                color: Color(0xFFB4B6C2), fontWeight: FontWeight.w900)),
      ],
    );
  }
}

class _DividerWave extends StatelessWidget {
  const _DividerWave();

  @override
  Widget build(BuildContext context) {
    return SizedBox(
        height: 24,
        width: double.infinity,
        child: CustomPaint(painter: _WavePainter()));
  }
}

class _WavePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF666976)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 4;
    final path = Path();
    const step = 15.0;
    for (double x = 0; x <= size.width + step; x += step) {
      if (x == 0) {
        path.moveTo(x, size.height * .5);
      } else {
        path.lineTo(x - step / 2, size.height * .25);
        path.lineTo(x, size.height * .5);
      }
    }
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _Message extends StatelessWidget {
  const _Message({required this.message, required this.action});

  final String message;
  final VoidCallback action;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 12),
          OutlinedButton(onPressed: action, child: Text(s.t('retry'))),
        ],
      ),
    );
  }
}
