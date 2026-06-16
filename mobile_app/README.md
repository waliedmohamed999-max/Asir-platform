# Farah Flutter App

Flutter mobile client for the Laravel events and services platform.

## Run

Install Flutter, then from this folder:

```bash
flutter pub get
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
```

For Android emulator use the host machine URL:

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

## Structure

- `core/network`: Dio API client and token handling.
- `core/theme`: light and dark premium theme.
- `core/i18n`: Arabic and English strings with RTL support through Flutter locale.
- `features`: auth, home, events, bookings, profile.
- `shared/widgets`: reusable loading and event-card components.

The app uses Riverpod for state management and consumes the Laravel `/api/v1` endpoints.

## UX Foundation

The app now includes:

- Premium smart home with hero slider, quick actions, trending searches, recommendations, trending, today and upcoming rails.
- Skeleton loading states.
- Premium bottom navigation.
- Event details with countdown, favorite/share actions, ticket tiers, seat-selection preview, map/QR area and floating booking button.
- Multi-step checkout UI prepared for Apple Pay, Google Pay, Mada, STC Pay and Stripe.

Install Flutter before running code generation or formatting on this machine.
