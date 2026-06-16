import 'package:flutter/material.dart';

class Skeleton extends StatefulWidget {
  const Skeleton({required this.child, super.key});

  final Widget child;

  @override
  State<Skeleton> createState() => _SkeletonState();
}

class _SkeletonState extends State<Skeleton>
    with SingleTickerProviderStateMixin {
  late final AnimationController controller;

  @override
  void initState() {
    super.initState();
    controller = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 1200))
      ..repeat();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final base = Theme.of(context).colorScheme.surfaceContainerHighest;
    final highlight = Theme.of(context).colorScheme.surface;

    return AnimatedBuilder(
      animation: controller,
      builder: (context, child) {
        return ShaderMask(
          blendMode: BlendMode.srcATop,
          shaderCallback: (bounds) => LinearGradient(
            begin: Alignment.centerLeft,
            end: Alignment.centerRight,
            stops: const [.1, .45, .9],
            colors: [base, highlight, base],
            transform: _SlideGradient(controller.value),
          ).createShader(bounds),
          child: child,
        );
      },
      child: widget.child,
    );
  }
}

class _SlideGradient extends GradientTransform {
  const _SlideGradient(this.value);

  final double value;

  @override
  Matrix4 transform(Rect bounds, {TextDirection? textDirection}) {
    return Matrix4.translationValues(bounds.width * (value * 2 - 1), 0, 0);
  }
}

class HomeSkeleton extends StatelessWidget {
  const HomeSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Skeleton(
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Container(height: 44, decoration: _box(context)),
          const SizedBox(height: 16),
          Container(height: 230, decoration: _box(context)),
          const SizedBox(height: 22),
          Row(
              children: List.generate(
                  4,
                  (_) => Expanded(
                      child: Padding(
                          padding: const EdgeInsets.all(4),
                          child: Container(
                              height: 72, decoration: _box(context)))))),
          const SizedBox(height: 22),
          ...List.generate(
              3,
              (_) => Padding(
                  padding: const EdgeInsets.only(bottom: 14),
                  child: Container(height: 210, decoration: _box(context)))),
        ],
      ),
    );
  }

  BoxDecoration _box(BuildContext context) {
    return BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(8));
  }
}
