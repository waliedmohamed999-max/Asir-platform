import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/i18n/strings.dart';
import '../../shared/widgets/platform_logo.dart';
import '../app/app_state.dart';

class OnboardingScreen extends ConsumerStatefulWidget {
  const OnboardingScreen({super.key});

  @override
  ConsumerState<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends ConsumerState<OnboardingScreen> {
  final controller = PageController();
  int index = 0;

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    final items = [
      (Icons.explore_outlined, s.t('onboarding1Title'), s.t('onboarding1Body')),
      (
        Icons.confirmation_number_outlined,
        s.t('onboarding2Title'),
        s.t('onboarding2Body')
      ),
      (
        Icons.local_offer_outlined,
        s.t('onboarding3Title'),
        s.t('onboarding3Body')
      ),
      (
        Icons.account_balance_wallet_outlined,
        s.t('onboarding4Title'),
        s.t('onboarding4Body')
      ),
    ];

    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              Row(
                children: [
                  const PlatformLogo(size: 48),
                  const SizedBox(width: 10),
                  Expanded(
                      child: Text(s.t('chooseLanguage'),
                          style: Theme.of(context).textTheme.titleMedium)),
                  SegmentedButton<String>(
                    segments: const [
                      ButtonSegment(value: 'ar', label: Text('عربي')),
                      ButtonSegment(value: 'en', label: Text('EN')),
                    ],
                    selected: {s.isArabic ? 'ar' : 'en'},
                    onSelectionChanged: (value) => ref
                        .read(settingsProvider.notifier)
                        .setLocale(Locale(value.first)),
                  ),
                ],
              ),
              Expanded(
                child: PageView.builder(
                  controller: controller,
                  itemCount: items.length,
                  onPageChanged: (value) => setState(() => index = value),
                  itemBuilder: (context, i) {
                    final item = items[i];
                    return KeyedSubtree(
                      key: ValueKey('onboarding-page-$i'),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          CircleAvatar(
                              radius: 54, child: Icon(item.$1, size: 46)),
                          const SizedBox(height: 28),
                          Text(item.$2,
                              textAlign: TextAlign.center,
                              style: Theme.of(context)
                                  .textTheme
                                  .headlineMedium
                                  ?.copyWith(fontWeight: FontWeight.w900)),
                          const SizedBox(height: 12),
                          Text(item.$3,
                              textAlign: TextAlign.center,
                              style: Theme.of(context).textTheme.bodyLarge),
                        ],
                      ),
                    );
                  },
                ),
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                    items.length,
                    (i) => AnimatedContainer(
                          duration: const Duration(milliseconds: 220),
                          width: i == index ? 22 : 8,
                          height: 8,
                          margin: const EdgeInsets.symmetric(horizontal: 3),
                          decoration: BoxDecoration(
                            color: i == index
                                ? Theme.of(context).colorScheme.primary
                                : Theme.of(context).colorScheme.outlineVariant,
                            borderRadius: BorderRadius.circular(8),
                          ),
                        )),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: () async {
                    if (index < items.length - 1) {
                      await controller.nextPage(
                          duration: const Duration(milliseconds: 260),
                          curve: Curves.easeOut);
                      return;
                    }
                    await ref
                        .read(settingsProvider.notifier)
                        .completeOnboarding();
                    if (context.mounted) context.go('/');
                  },
                  child: Text(index == items.length - 1
                      ? s.t('start')
                      : s.t('viewAll')),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
