import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../core/i18n/strings.dart';
import '../../shared/widgets/platform_logo.dart';
import '../app/app_state.dart';

class ChatScreen extends ConsumerStatefulWidget {
  const ChatScreen({super.key});

  @override
  ConsumerState<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends ConsumerState<ChatScreen> {
  static const _tokenKey = 'support_conversation_token';

  final _name = TextEditingController();
  final _username = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _bio = TextEditingController();
  final _message = TextEditingController();
  final _reply = TextEditingController();

  bool _loading = true;
  bool _submitting = false;
  bool _started = false;
  Map<String, dynamic>? _conversation;

  @override
  void initState() {
    super.initState();
    Future.microtask(_bootstrap);
  }

  @override
  void dispose() {
    _name.dispose();
    _username.dispose();
    _email.dispose();
    _phone.dispose();
    _bio.dispose();
    _message.dispose();
    _reply.dispose();
    super.dispose();
  }

  Future<void> _bootstrap() async {
    final auth = ref.read(authProvider);
    final user = auth.user ?? {};
    _name.text = user['name']?.toString() ?? '';
    _email.text = user['email']?.toString() ?? '';
    _phone.text = user['phone']?.toString() ?? '';
    _bio.text = user['bio']?.toString() ?? '';
    _username.text = _suggestUsername(_name.text, _email.text);

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_tokenKey);
    if (token == null || token.isEmpty) {
      setState(() => _loading = false);
      return;
    }

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.dio.get(
        '/support/conversations/current',
        queryParameters: {'token': token},
      );
      setState(() {
        _conversation = Map<String, dynamic>.from(response.data['data'] as Map);
        _loading = false;
        _started = true;
      });
    } catch (_) {
      await prefs.remove(_tokenKey);
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);

    if (_loading) {
      return Scaffold(
        appBar: AppBar(title: Text(s.chat)),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_conversation != null) {
      return _ChatThread(
        conversation: _conversation!,
        reply: _reply,
        submitting: _submitting,
        onRefresh: _refreshConversation,
        onSend: _sendMessage,
      );
    }

    if (!_started) {
      return _IntroScreen(onStart: () => setState(() => _started = true));
    }

    return _ProfileSetupScreen(
      name: _name,
      username: _username,
      email: _email,
      phone: _phone,
      bio: _bio,
      message: _message,
      submitting: _submitting,
      onBack: () => setState(() => _started = false),
      onSubmit: _createConversation,
    );
  }

  Future<void> _createConversation() async {
    if (_submitting) return;
    if (_name.text.trim().isEmpty || _username.text.trim().isEmpty) {
      _show('اكتب اسمك واسم المستخدم أولاً');
      return;
    }

    final username = _username.text.trim();
    if (!RegExp(r'^[A-Za-z0-9_]+$').hasMatch(username)) {
      _show(
          'اسم المستخدم يسمح بالحروف الإنجليزية والأرقام والشرطة السفلية فقط');
      return;
    }

    setState(() => _submitting = true);
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.dio.post('/support/conversations', data: {
        'customer_name': _name.text.trim(),
        'username': username,
        'customer_email':
            _email.text.trim().isEmpty ? null : _email.text.trim(),
        'customer_phone':
            _phone.text.trim().isEmpty ? null : _phone.text.trim(),
        'bio': _bio.text.trim().isEmpty ? null : _bio.text.trim(),
        'topic': 'community',
        'message': _message.text.trim().isEmpty
            ? 'مرحباً، أريد الانضمام وفتح محادثة مع فريق منصة عسير.'
            : _message.text.trim(),
      });

      final conversation =
          Map<String, dynamic>.from(response.data['data'] as Map);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenKey, conversation['access_token'].toString());
      setState(() => _conversation = conversation);
    } catch (error) {
      _show('تعذر إنشاء المحادثة، حاول مرة أخرى');
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _refreshConversation() async {
    final conversation = _conversation;
    if (conversation == null) return;
    final api = ref.read(apiClientProvider);
    final response = await api.dio.get(
      '/support/conversations/${conversation['id']}',
      queryParameters: {'token': conversation['access_token']},
    );
    setState(() {
      _conversation = Map<String, dynamic>.from(response.data['data'] as Map);
    });
  }

  Future<void> _sendMessage() async {
    final body = _reply.text.trim();
    final conversation = _conversation;
    if (_submitting || body.isEmpty || conversation == null) return;

    setState(() => _submitting = true);
    try {
      final api = ref.read(apiClientProvider);
      await api.dio.post(
        '/support/conversations/${conversation['id']}/messages',
        data: {'body': body, 'token': conversation['access_token']},
      );
      _reply.clear();
      await _refreshConversation();
    } catch (_) {
      _show('تعذر إرسال الرسالة');
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  String _suggestUsername(String name, String email) {
    final source = email.contains('@') ? email.split('@').first : name;
    final normalized = source
        .toLowerCase()
        .replaceAll(RegExp(r'[^a-z0-9_]+'), '_')
        .replaceAll(RegExp(r'_+'), '_')
        .replaceAll(RegExp(r'^_|_$'), '');
    return normalized.isEmpty ? 'aseer_user' : normalized;
  }

  void _show(String message) {
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(message)));
  }
}

class _IntroScreen extends StatelessWidget {
  const _IntroScreen({required this.onStart});

  final VoidCallback onStart;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF06070A),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(22, 12, 22, 24),
                children: [
                  Align(
                    alignment: AlignmentDirectional.centerEnd,
                    child: IconButton(
                      onPressed: () {},
                      icon: const Icon(Icons.close,
                          color: Colors.white, size: 34),
                    ),
                  ),
                  const SizedBox(height: 4),
                  const _CommunityCollage(),
                  const SizedBox(height: 38),
                  const Center(child: PlatformLogo(size: 86)),
                  const SizedBox(height: 28),
                  Text(
                    'اكتشف وانضم لمجتمعات\nممتعة تناسب اهتماماتك!',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          height: 1.45,
                        ),
                  ),
                  const SizedBox(height: 22),
                  const Text(
                    'التقِ بالناس اللي يحبون نفس الفعالية، وشارك حماسك لليوم الكبير. هنا تقدر تصنع لحظات لا تُنسى وتتعرف على أصدقاء جدد!',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Color(0xFFA6A8B3),
                      fontSize: 18,
                      height: 1.75,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.fromLTRB(20, 18, 20, 22),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: Color(0xFF242630))),
                color: Color(0xFF08090D),
              ),
              child: SizedBox(
                width: double.infinity,
                height: 58,
                child: FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  onPressed: onStart,
                  child: const Text('ابدأ',
                      style:
                          TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProfileSetupScreen extends StatelessWidget {
  const _ProfileSetupScreen({
    required this.name,
    required this.username,
    required this.email,
    required this.phone,
    required this.bio,
    required this.message,
    required this.submitting,
    required this.onBack,
    required this.onSubmit,
  });

  final TextEditingController name;
  final TextEditingController username;
  final TextEditingController email;
  final TextEditingController phone;
  final TextEditingController bio;
  final TextEditingController message;
  final bool submitting;
  final VoidCallback onBack;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF06070A),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 14, 24, 0),
              child: Row(
                children: [
                  IconButton(
                    onPressed: onBack,
                    icon: const Icon(Icons.chevron_left,
                        color: Colors.white, size: 34),
                  ),
                  const Spacer(),
                  const Text('1/4',
                      style: TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.w900)),
                  const Spacer(),
                  const SizedBox(width: 48),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 38, vertical: 18),
              child: Row(
                children: const [
                  Expanded(child: _StepLine(active: false)),
                  SizedBox(width: 8),
                  Expanded(child: _StepLine(active: false)),
                  SizedBox(width: 8),
                  Expanded(child: _StepLine(active: false)),
                  SizedBox(width: 8),
                  Expanded(child: _StepLine(active: true)),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(24, 0, 24, 120),
                children: [
                  Text(
                    'سوّ ملفك الشخصي',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'قبل لا نبدأ، عندنا بعض المتطلبات اللي نحتاجها منك عشان نضمن سلامة مجتمعنا',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        color: Color(0xFFA6A8B3),
                        fontSize: 18,
                        height: 1.65,
                        fontWeight: FontWeight.w700),
                  ),
                  const SizedBox(height: 28),
                  Center(
                    child: Stack(
                      clipBehavior: Clip.none,
                      children: [
                        Container(
                          width: 132,
                          height: 132,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: const Color(0xFFC8F000),
                            shape: BoxShape.circle,
                            border: Border.all(color: const Color(0xFF30323B)),
                          ),
                          child: const Text('WA',
                              style: TextStyle(
                                  color: Color(0xFF20222A),
                                  fontSize: 44,
                                  fontWeight: FontWeight.w900)),
                        ),
                        PositionedDirectional(
                          start: -2,
                          bottom: 4,
                          child: Container(
                            width: 42,
                            height: 42,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                              border:
                                  Border.all(color: const Color(0xFF252731)),
                            ),
                            child: const Icon(Icons.edit_outlined,
                                color: Color(0xFF20222A)),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 32),
                  _DarkField(
                      label: 'الاسم', hint: 'اكتب اسمك', controller: name),
                  _DarkField(
                      label: 'اسم المستخدم',
                      hint: 'اختر اسم مستخدم',
                      helper: 'يُسمح فقط بالحروف والأرقام والشرطة السفلية (_)',
                      controller: username,
                      ltr: true),
                  _DarkField(
                      label: 'البريد الإلكتروني',
                      hint: 'example@email.com',
                      controller: email,
                      keyboardType: TextInputType.emailAddress,
                      ltr: true),
                  _DarkField(
                      label: 'رقم الجوال',
                      hint: '05xxxxxxxx',
                      controller: phone,
                      keyboardType: TextInputType.phone,
                      ltr: true),
                  _DarkField(
                      label: 'الوصف',
                      hint: 'عط الناس فكرة عنك أكثر',
                      controller: bio,
                      maxLines: 4),
                  _DarkField(
                      label: 'أول رسالة',
                      hint: 'اكتب رسالتك للمسؤول',
                      controller: message,
                      maxLines: 3),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 22),
              decoration: const BoxDecoration(
                color: Color(0xFF08090D),
                border: Border(top: BorderSide(color: Color(0xFF242630))),
              ),
              child: SizedBox(
                width: double.infinity,
                height: 58,
                child: FilledButton(
                  onPressed: submitting ? null : onSubmit,
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.white,
                    disabledBackgroundColor: const Color(0xFF3A3B42),
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  child: submitting
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('التالي',
                          style: TextStyle(
                              fontSize: 22, fontWeight: FontWeight.w900)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ChatThread extends StatelessWidget {
  const _ChatThread({
    required this.conversation,
    required this.reply,
    required this.submitting,
    required this.onRefresh,
    required this.onSend,
  });

  final Map<String, dynamic> conversation;
  final TextEditingController reply;
  final bool submitting;
  final Future<void> Function() onRefresh;
  final VoidCallback onSend;

  @override
  Widget build(BuildContext context) {
    final messages = List<Map<String, dynamic>>.from(
        conversation['messages'] as List? ?? []);

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('محادثة الدعم'),
            Text('@${conversation['username']}',
                style: const TextStyle(fontSize: 12, color: Color(0xFF9CA0AF))),
          ],
        ),
        actions: [
          IconButton(onPressed: onRefresh, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: RefreshIndicator(
              onRefresh: onRefresh,
              child: ListView.builder(
                padding: const EdgeInsets.fromLTRB(16, 18, 16, 18),
                itemCount: messages.length + 1,
                itemBuilder: (context, index) {
                  if (index == 0) {
                    return const _ThreadHeader();
                  }
                  final message = messages[index - 1];
                  return _MessageBubble(message: message);
                },
              ),
            ),
          ),
          SafeArea(
            top: false,
            child: Container(
              padding: const EdgeInsets.fromLTRB(14, 10, 14, 14),
              decoration: const BoxDecoration(
                color: Color(0xFF08090D),
                border: Border(top: BorderSide(color: Color(0xFF252731))),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: reply,
                      minLines: 1,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'اكتب رسالتك...',
                        filled: true,
                        fillColor: const Color(0xFF111217),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(18),
                          borderSide:
                              const BorderSide(color: Color(0xFF343640)),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  FilledButton(
                    onPressed: submitting ? null : onSend,
                    style: FilledButton.styleFrom(
                      minimumSize: const Size(52, 52),
                      shape: const CircleBorder(),
                    ),
                    child: const Icon(Icons.send),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ThreadHeader extends StatelessWidget {
  const _ThreadHeader();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF12131A),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFF2B2D35)),
      ),
      child: const Row(
        children: [
          PlatformLogo(size: 44),
          SizedBox(width: 12),
          Expanded(
            child: Text(
              'تم فتح محادثتك مع فريق منصة عسير. رد المسؤول سيظهر هنا مباشرة بعد تحديث المحادثة.',
              style: TextStyle(
                  color: Color(0xFFD7D8E0),
                  height: 1.6,
                  fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }
}

class _MessageBubble extends StatelessWidget {
  const _MessageBubble({required this.message});

  final Map<String, dynamic> message;

  @override
  Widget build(BuildContext context) {
    final isAdmin = message['sender_type'] == 'admin';
    return Align(
      alignment: isAdmin
          ? AlignmentDirectional.centerStart
          : AlignmentDirectional.centerEnd,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        constraints:
            BoxConstraints(maxWidth: MediaQuery.sizeOf(context).width * .78),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
        decoration: BoxDecoration(
          color: isAdmin ? const Color(0xFF202126) : const Color(0xFFFF2D7A),
          borderRadius: BorderRadiusDirectional.only(
            topStart: const Radius.circular(18),
            topEnd: const Radius.circular(18),
            bottomStart: Radius.circular(isAdmin ? 4 : 18),
            bottomEnd: Radius.circular(isAdmin ? 18 : 4),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isAdmin ? 'المسؤول' : 'أنت',
              style: TextStyle(
                color: isAdmin ? const Color(0xFFB4B6C2) : Colors.white70,
                fontSize: 11,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              message['body']?.toString() ?? '',
              style: const TextStyle(
                  color: Colors.white,
                  height: 1.55,
                  fontWeight: FontWeight.w700),
            ),
          ],
        ),
      ),
    );
  }
}

class _DarkField extends StatelessWidget {
  const _DarkField({
    required this.label,
    required this.hint,
    required this.controller,
    this.helper,
    this.maxLines = 1,
    this.keyboardType,
    this.ltr = false,
  });

  final String label;
  final String hint;
  final String? helper;
  final TextEditingController controller;
  final int maxLines;
  final TextInputType? keyboardType;
  final bool ltr;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 22),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.w900)),
          const SizedBox(height: 6),
          if (helper != null)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: Text(helper!,
                  style: const TextStyle(
                      color: Color(0xFF9CA0AF), fontWeight: FontWeight.w700)),
            ),
          TextField(
            controller: controller,
            maxLines: maxLines,
            keyboardType: keyboardType,
            textDirection: ltr ? TextDirection.ltr : null,
            decoration: InputDecoration(
              hintText: hint,
              filled: true,
              fillColor: const Color(0xFF07080B),
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFF555864)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFF555864)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    const BorderSide(color: Color(0xFFFF2D7A), width: 1.5),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _StepLine extends StatelessWidget {
  const _StepLine({required this.active});

  final bool active;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 3,
      decoration: BoxDecoration(
        color: active ? Colors.white : const Color(0xFF2B2D35),
        borderRadius: BorderRadius.circular(999),
      ),
    );
  }
}

class _CommunityCollage extends StatelessWidget {
  const _CommunityCollage();

  static const _images = [
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=500&q=80',
    'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=500&q=80',
    'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=500&q=80',
    'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=500&q=80',
    'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=500&q=80',
    'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=500&q=80',
  ];

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 360,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          _tile(0, left: 0, top: 0, width: 128, height: 132, angle: -.16),
          _tile(1, right: 10, top: 0, width: 132, height: 144, angle: .14),
          _tile(2, left: 102, top: 42, width: 150, height: 178, angle: -.05),
          _tile(3, right: 0, top: 132, width: 142, height: 128, angle: -.18),
          _tile(4, left: 18, top: 142, width: 130, height: 154, angle: .13),
          _tile(5, left: 150, top: 188, width: 158, height: 142, angle: .08),
          const Positioned(
            left: 12,
            bottom: 10,
            child: Icon(Icons.auto_awesome, color: Color(0xFFC8F000), size: 24),
          ),
          const Positioned(
            right: 16,
            bottom: 20,
            child: Icon(Icons.auto_awesome, color: Color(0xFFC8F000), size: 28),
          ),
        ],
      ),
    );
  }

  Widget _tile(
    int index, {
    double? left,
    double? right,
    required double top,
    required double width,
    required double height,
    required double angle,
  }) {
    return Positioned(
      left: left,
      right: right,
      top: top,
      child: Transform.rotate(
        angle: angle,
        child: Container(
          width: width,
          height: height,
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            color: const Color(0xFF191B22),
            borderRadius: BorderRadius.circular(30),
            border: Border.all(color: Colors.white.withOpacity(.72), width: 2),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(.35),
                blurRadius: 18,
                offset: const Offset(0, 12),
              ),
            ],
          ),
          child: CachedNetworkImage(
            imageUrl: _images[index],
            fit: BoxFit.cover,
            errorWidget: (_, __, ___) =>
                const Icon(Icons.groups_2_outlined, color: Color(0xFF6B6E7A)),
          ),
        ),
      ),
    );
  }
}
