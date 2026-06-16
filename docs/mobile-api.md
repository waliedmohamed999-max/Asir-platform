# Mobile API

Base URL:

```text
http://127.0.0.1:8000/api/v1
```

Use `Accept: application/json` for all requests. Protected endpoints use:

```text
Authorization: Bearer {access_token}
```

## Auth

- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/forgot-password`
- `GET /auth/me`
- `PUT /auth/me`
- `POST /auth/logout`

Login accepts `login` as email or phone plus `password`.

## Discovery

- `GET /home`
- `GET /events?q=&city_id=&category_id=&date=&min_price=&max_price=`
- `GET /events/{slug}`
- `GET /recommendations`
- `GET /offers`
- `GET /services`
- `GET /venues`
- `GET /cities`
- `GET /categories`

## Customer

- `GET /bookings`
- `POST /bookings`
- `GET /bookings/{id}`
- `GET /favorites`
- `POST /favorites/{event_slug}`
- `GET /notifications`
- `POST /devices`
- `GET /events/{slug}/reviews`
- `POST /events/{slug}/reviews`
- `GET /events/{slug}/comments`
- `POST /events/{slug}/comments`
- `POST /organizers/{id}/follow`

Booking payload matches the existing Laravel checkout service:

```json
{
  "event_id": 1,
  "booking_date": "2026-05-24",
  "payment_method": "mada",
  "promo_code": "SUMMER",
  "customer_name": "Customer",
  "customer_email": "customer@example.com",
  "customer_phone": "0500000000",
  "quantities": {
    "1": 2
  }
}
```

The current payment implementation is demo-mode and creates a paid booking using the existing `BookingService`. Real gateway credentials can be wired behind the same endpoint without changing Flutter screens.

## Smart Home Payload

`GET /home` returns production-oriented sections:

- `banners`
- `quick_actions`
- `trending_searches`
- `sections.recommended`
- `sections.trending`
- `sections.upcoming`
- `sections.today`
- `sections.offers`
- `sections.services`
- `sections.places`

The endpoint is cached for five minutes through Laravel cache. It is safe to move the cache driver to Redis later without changing the Flutter app.

## Production Integrations

The code now has stable extension points for:

- Real payments: Apple Pay, Google Pay, Mada, STC Pay, Stripe.
- Push notifications: store Firebase device tokens with `POST /devices`.
- Reviews, comments, organizer follows, favorites, and user interests.
- CDN image URLs from the dashboard fields already exposed through API resources.
- Queue-backed email and notification dispatch through Laravel queues.
