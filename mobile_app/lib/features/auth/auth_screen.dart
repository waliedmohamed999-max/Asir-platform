import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/i18n/strings.dart';
import '../../shared/widgets/platform_logo.dart';
import '../app/app_state.dart';

class AuthScreen extends ConsumerStatefulWidget {
  const AuthScreen({super.key});

  @override
  ConsumerState<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends ConsumerState<AuthScreen> {
  final name = TextEditingController();
  final login = TextEditingController();
  final email = TextEditingController();
  final phone = TextEditingController();
  final password = TextEditingController();
  bool loading = false;
  int tab = 0;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);

    return Scaffold(
      appBar: AppBar(title: Text(tab == 0 ? s.t('login') : s.t('register'))),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(s.appName,
              style: Theme.of(context)
                  .textTheme
                  .headlineMedium
                  ?.copyWith(fontWeight: FontWeight.w900)),
          const SizedBox(height: 10),
          const Align(
              alignment: AlignmentDirectional.centerStart,
              child: PlatformLogo(size: 72)),
          Text(s.tagline),
          const SizedBox(height: 24),
          SegmentedButton<int>(
            segments: [
              ButtonSegment(
                  value: 0,
                  label: Text(s.t('login')),
                  icon: const Icon(Icons.login)),
              ButtonSegment(
                  value: 1,
                  label: Text(s.t('register')),
                  icon: const Icon(Icons.person_add_alt)),
            ],
            selected: {tab},
            onSelectionChanged: (value) => setState(() => tab = value.first),
          ),
          const SizedBox(height: 20),
          if (tab == 1) ...[
            TextField(
                controller: name,
                decoration: InputDecoration(
                    labelText: s.t('name'),
                    prefixIcon: const Icon(Icons.person_outline))),
            const SizedBox(height: 12),
            TextField(
                controller: email,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                    labelText: s.t('email'),
                    prefixIcon: const Icon(Icons.email_outlined))),
            const SizedBox(height: 12),
            TextField(
                controller: phone,
                keyboardType: TextInputType.phone,
                decoration: InputDecoration(
                    labelText: s.t('phone'),
                    prefixIcon: const Icon(Icons.phone_outlined))),
          ] else ...[
            TextField(
                controller: login,
                decoration: InputDecoration(
                    labelText: s.t('emailOrPhone'),
                    prefixIcon: const Icon(Icons.alternate_email))),
          ],
          const SizedBox(height: 12),
          TextField(
              controller: password,
              obscureText: true,
              decoration: InputDecoration(
                  labelText: s.t('password'),
                  prefixIcon: const Icon(Icons.lock_outline))),
          const SizedBox(height: 20),
          FilledButton(
            onPressed: loading ? null : _submit,
            child: loading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : Text(tab == 0 ? s.t('login') : s.t('register')),
          ),
          TextButton(
              onPressed: loading ? null : _forgotPassword,
              child: Text(s.t('forgotPassword'))),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    final s = AppStrings.of(context);
    setState(() => loading = true);
    try {
      if (tab == 0) {
        await ref
            .read(authProvider.notifier)
            .login(login: login.text.trim(), password: password.text);
      } else {
        await ref.read(authProvider.notifier).register(
              name: name.text.trim(),
              email: email.text.trim(),
              phone: phone.text.trim().isEmpty ? null : phone.text.trim(),
              password: password.text,
            );
      }
      if (mounted) context.go('/');
    } on DioException catch (error) {
      _show(
          error.response?.data?['message']?.toString() ?? s.t('loginRequired'));
    } catch (error) {
      _show(error.toString());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> _forgotPassword() async {
    final s = AppStrings.of(context);
    final value =
        email.text.trim().isNotEmpty ? email.text.trim() : login.text.trim();
    if (value.isEmpty || !value.contains('@')) {
      _show(s.t('email'));
      return;
    }
    setState(() => loading = true);
    try {
      await ref
          .read(apiClientProvider)
          .dio
          .post('/auth/forgot-password', data: {'email': value});
      _show(s.t('forgotPassword'));
    } catch (error) {
      _show(error.toString());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  void _show(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(message)));
  }
}
