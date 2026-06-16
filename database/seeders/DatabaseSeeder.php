<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Booking;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\HomepageItem;
use App\Models\ResaleListing;
use App\Models\SupportConversation;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@farah.sa'],
            ['name' => 'مشرف منصة فرح', 'phone' => '0500000001', 'role' => 'admin', 'is_active' => true, 'password' => Hash::make('password')]
        );

        $organizer = User::updateOrCreate(
            ['email' => 'organizer@farah.sa'],
            [
                'name' => 'منظم فعاليات جدة',
                'phone' => '0500000002',
                'role' => 'organizer',
                'is_active' => true,
                'logo_url' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=400&q=80',
                'bio' => 'منظم فعاليات متخصص في الفعاليات الشاطئية والعروض الترفيهية والحجوزات الموسمية داخل جدة.',
                'whatsapp' => '0500000002',
                'instagram_url' => 'https://instagram.com/farah.events',
                'x_url' => 'https://x.com/farah_events',
                'website_url' => 'https://farah.sa/organizer/jeddah-events',
                'password' => Hash::make('password'),
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer@farah.sa'],
            ['name' => 'عميل تجريبي', 'phone' => '0500000003', 'role' => 'customer', 'is_active' => true, 'password' => Hash::make('password')]
        );

        $jeddah = City::updateOrCreate(['slug' => 'jeddah'], ['name' => 'جدة']);
        City::updateOrCreate(['slug' => 'riyadh'], ['name' => 'الرياض', 'sort_order' => 2, 'is_active' => true]);
        City::updateOrCreate(['slug' => 'dammam'], ['name' => 'الدمام', 'sort_order' => 3, 'is_active' => true]);
        $jeddah->update(['sort_order' => 1, 'is_active' => true]);

        $webookCategories = collect([
            ['slug' => 'today', 'name' => 'اليوم', 'name_en' => 'Today', 'icon' => 'calendar_today', 'description' => 'فعاليات وتجارب متاحة اليوم أو خلال الساعات القريبة.', 'sort_order' => 1],
            ['slug' => 'experiences', 'name' => 'التجارب', 'name_en' => 'Experiences', 'icon' => 'confirmation_number', 'description' => 'تجارب ترفيهية وعائلية مختارة للحجز المباشر.', 'sort_order' => 2],
            ['slug' => 'sports', 'name' => 'الرياضة', 'name_en' => 'Sports', 'icon' => 'emoji_events', 'description' => 'مباريات وبطولات وفعاليات رياضية.', 'sort_order' => 3],
            ['slug' => 'football', 'name' => 'كرة القدم', 'name_en' => 'Football', 'icon' => 'sports_soccer', 'description' => 'مباريات كرة القدم وتجارب الجماهير.', 'sort_order' => 4],
            ['slug' => 'restaurants', 'name' => 'المطاعم', 'name_en' => 'Restaurants', 'icon' => 'restaurant', 'description' => 'تجارب طعام ومطاعم ومناطق ضيافة.', 'sort_order' => 5],
            ['slug' => 'aviation', 'name' => 'الطيران', 'name_en' => 'Aviation', 'icon' => 'flight_takeoff', 'description' => 'تجارب سفر وطيران وباقات تنقل.', 'sort_order' => 6],
            ['slug' => 'hotels', 'name' => 'فنادق', 'name_en' => 'Hotels', 'icon' => 'beach_access', 'description' => 'إقامات وفنادق ومنتجعات وباقات سياحية.', 'sort_order' => 7],
            ['slug' => 'concerts', 'name' => 'الحفلات', 'name_en' => 'Concerts', 'icon' => 'music_note', 'description' => 'حفلات موسيقية وأمسيات فنية.', 'sort_order' => 8],
            ['slug' => 'shows', 'name' => 'العروض', 'name_en' => 'Shows', 'icon' => 'theater_comedy', 'description' => 'عروض مسرحية وكوميدية وترفيه حي.', 'sort_order' => 9],
            ['slug' => 'store', 'name' => 'المتجر', 'name_en' => 'Store', 'icon' => 'shopping_bag', 'description' => 'منتجات وبطاقات رقمية وتجارب قابلة للشراء.', 'sort_order' => 10],
            ['slug' => 'auctions', 'name' => 'مزادات', 'name_en' => 'Auctions', 'icon' => 'gavel', 'description' => 'مزادات وتجارب محدودة ومقاعد خاصة.', 'sort_order' => 11],
            ['slug' => 'more', 'name' => 'المزيد', 'name_en' => 'More', 'icon' => 'grid_view', 'description' => 'تصنيفات وخدمات إضافية قابلة للتوسع.', 'sort_order' => 12],
        ])->mapWithKeys(function (array $category) {
            $model = Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'name_ar' => $category['name'],
                    'name_en' => $category['name_en'],
                    'icon' => $category['icon'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'meta_title' => $category['name'],
                    'meta_description' => $category['description'],
                ]
            );

            return [$category['slug'] => $model];
        });

        Category::whereIn('slug', ['adventures', 'kids'])->update(['is_active' => false]);

        $adventure = $webookCategories['experiences'];

        Venue::updateOrCreate(
            ['slug' => 'cyan-waterpark-venue'],
            [
                'name' => 'سيان ووتر بارك',
                'city_id' => $jeddah->id,
                'address' => 'أبحر الشمالية، جدة',
                'google_maps_url' => 'https://maps.google.com/maps?q=Jeddah%20Corniche&t=&z=13&ie=UTF8&iwloc=&output=embed',
                'latitude' => 21.7485000,
                'longitude' => 39.0367000,
                'image_url' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1200&q=80',
                'description' => 'منتزه مائي وتجربة عائلية مناسبة للفعاليات الصيفية والمغامرات البحرية.',
                'capacity' => 800,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $event = Event::updateOrCreate(
            ['slug' => 'cyan-waterpark-jeddah'],
            [
                'organizer_id' => $organizer->id,
                'city_id' => $jeddah->id,
                'category_id' => $adventure->id,
                'title' => 'Cyan Waterpark - جدة',
                'venue_name' => 'سيان ووتر بارك، أبحر الشمالية',
                'excerpt' => 'مغامرات مائية عائلية تبدأ من 60 ريال مع أيام خاصة للسيدات وجدول يومي مرن.',
                'description' => 'استمتعوا بيوم كامل داخل Cyan Waterpark في جدة مع ألعاب مائية، منطقة أطفال، جلسات عائلية، مطاعم، وتجربة صيفية ممتعة مستوحاة من نمط Platinumlist ولكن بهوية فرح.',
                'banner_image_url' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1600&q=80',
                'terms' => "التذاكر غير مستردة بعد التأكيد.\nيُسمح بدخول الأطفال تحت إشراف بالغ.\nيوم السيدات مخصص للنساء والأطفال فقط.",
                'schedule_notes' => "السبت - الأربعاء: 10:00 ص حتى 10:00 م\nالخميس - الجمعة: 10:00 ص حتى 12:00 ص\nLadies Day: كل ثلاثاء من 2:00 م حتى 10:00 م",
                'map_url' => 'https://maps.google.com/maps?q=Jeddah%20Corniche&t=&z=13&ie=UTF8&iwloc=&output=embed',
                'start_date' => now()->addDays(2)->setTime(10, 0),
                'end_date' => now()->addMonths(2)->setTime(23, 0),
                'status' => 'published',
                'is_featured' => true,
            ]
        );

        $event->images()->delete();
        foreach ([
            'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80',
        ] as $index => $image) {
            $event->images()->create([
                'image_url' => $image,
                'alt_text' => 'Cyan Waterpark Gallery '.($index + 1),
                'sort_order' => $index + 1,
            ]);
        }

        $event->tickets()->delete();
        $scheduleBase = now()->addDays(2)->setTime(10, 0);

        $event->tickets()->createMany([
            ['name' => 'تذكرة كبار', 'type' => 'adult', 'price' => 60, 'quantity' => 250, 'starts_at' => $scheduleBase, 'ends_at' => $scheduleBase->copy()->addHours(12), 'is_active' => true],
            ['name' => 'تذكرة أطفال', 'type' => 'child', 'price' => 40, 'quantity' => 200, 'starts_at' => $scheduleBase, 'ends_at' => $scheduleBase->copy()->addHours(12), 'is_active' => true],
            ['name' => 'VIP Cabana', 'type' => 'vip', 'price' => 180, 'quantity' => 30, 'starts_at' => $scheduleBase, 'ends_at' => $scheduleBase->copy()->addHours(12), 'is_active' => true],
            ['name' => 'Ladies Day Pass', 'type' => 'ladies', 'price' => 75, 'quantity' => 120, 'starts_at' => $scheduleBase->copy()->next('Tuesday')->setTime(14, 0), 'ends_at' => $scheduleBase->copy()->next('Tuesday')->setTime(22, 0), 'is_active' => true],
        ]);

        foreach ([
            ['category' => 'today', 'slug' => 'today-riyadh-family-fun', 'title' => 'فعاليات اليوم - ونتر جاردن', 'venue' => 'بوليفارد رياض سيتي', 'price' => 45, 'days' => 0, 'image' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'experiences', 'slug' => 'aquarabia-riyadh-experience', 'title' => 'أكوارابيا - تجربة الألعاب المائية', 'venue' => 'الرياض', 'price' => 120, 'days' => 1, 'image' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'sports', 'slug' => 'riyadh-sports-cup', 'title' => 'كأس الرياض للرياضات المتنوعة', 'venue' => 'الملعب الرياضي', 'price' => 80, 'days' => 4, 'image' => 'https://images.unsplash.com/photo-1547347298-4074fc3086f0?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'football', 'slug' => 'football-fans-night', 'title' => 'ليلة جماهير كرة القدم', 'venue' => 'مرسول بارك', 'price' => 65, 'days' => 5, 'image' => 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'restaurants', 'slug' => 'webook-fun-restaurants', 'title' => 'WE BOOK FUN RESTAURANTS', 'venue' => 'منطقة المطاعم', 'price' => 35, 'days' => 2, 'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'aviation', 'slug' => 'flying-over-riyadh', 'title' => 'فلاينق اوفر - تجربة الطيران', 'venue' => 'واجهة الرياض', 'price' => 95, 'days' => 7, 'image' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'hotels', 'slug' => 'resort-weekend-pass', 'title' => 'إقامة نهاية الأسبوع في المنتجع', 'venue' => 'ريكسوس أبحر', 'price' => 220, 'days' => 9, 'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'concerts', 'slug' => 'angham-eid-concert-2026', 'title' => 'حفل أنغام - حفلات عيد الأضحى 2026', 'venue' => 'مسرح عبادي الجوهر', 'price' => 250, 'days' => 6, 'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'shows', 'slug' => 'comedy-theatre-night', 'title' => 'ليلة العروض الكوميدية', 'venue' => 'مسرح جدة', 'price' => 85, 'days' => 3, 'image' => 'https://images.unsplash.com/photo-1527224538127-2104bb71c51b?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'store', 'slug' => 'wbk-digital-card-store', 'title' => 'بطاقات WBK الرقمية', 'venue' => 'المتجر الإلكتروني', 'price' => 100, 'days' => 10, 'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'auctions', 'slug' => 'vip-auction-seats', 'title' => 'مزاد مقاعد VIP', 'venue' => 'صالة المزادات', 'price' => 500, 'days' => 8, 'image' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80'],
            ['category' => 'more', 'slug' => 'more-entertainment-pass', 'title' => 'باقة المزيد الترفيهية', 'venue' => 'عدة مواقع', 'price' => 75, 'days' => 11, 'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'],
        ] as $index => $demo) {
            $demoEvent = Event::updateOrCreate(
                ['slug' => $demo['slug']],
                [
                    'organizer_id' => $organizer->id,
                    'city_id' => $jeddah->id,
                    'category_id' => $webookCategories[$demo['category']]->id,
                    'title' => $demo['title'],
                    'title_ar' => $demo['title'],
                    'title_en' => Str::headline($demo['slug']),
                    'venue_name' => $demo['venue'],
                    'excerpt' => 'فعالية تجريبية مرتبطة بتصنيف '.$webookCategories[$demo['category']]->name.' لاختبار الواجهة والتطبيق والداشبورد.',
                    'description' => 'هذه بيانات وهمية قابلة للتعديل من الداشبورد، تم إنشاؤها حتى يظهر التصنيف كتصنيف حقيقي له فعاليات وتذاكر داخل المنصة والتطبيق.',
                    'description_ar' => 'هذه بيانات وهمية قابلة للتعديل من الداشبورد، تم إنشاؤها حتى يظهر التصنيف كتصنيف حقيقي له فعاليات وتذاكر داخل المنصة والتطبيق.',
                    'description_en' => 'Demo content connected to a real dashboard category for testing the public website and mobile app.',
                    'terms' => 'التذكرة تجريبية وغير مخصصة للبيع الحقيقي.',
                    'schedule_notes' => 'يفتح الدخول قبل الموعد بساعة.',
                    'map_url' => 'https://maps.google.com/maps?q=Riyadh&t=&z=13&ie=UTF8&iwloc=&output=embed',
                    'banner_image_url' => $demo['image'],
                    'start_date' => now()->addDays($demo['days'])->setTime(20, 0),
                    'end_date' => now()->addDays($demo['days'])->setTime(23, 30),
                    'status' => 'published',
                    'is_featured' => $index < 6,
                    'show_on_homepage' => true,
                    'display_order' => $index + 1,
                    'capacity' => 300,
                    'is_active' => true,
                ]
            );

            $demoEvent->images()->delete();
            $demoEvent->images()->create([
                'image_url' => $demo['image'],
                'alt_text' => $demo['title'],
                'sort_order' => 1,
            ]);

            $demoEvent->tickets()->delete();
            $demoEvent->tickets()->createMany([
                ['name' => 'دخول عام', 'type' => 'general', 'price' => $demo['price'], 'quantity' => 250, 'purchase_limit_per_user' => 6, 'sort_order' => 1, 'status' => 'active', 'is_active' => true],
                ['name' => 'تذكرة مميزة', 'type' => 'vip', 'price' => $demo['price'] + 120, 'quantity' => 50, 'purchase_limit_per_user' => 4, 'sort_order' => 2, 'status' => 'active', 'is_active' => true],
            ]);
        }

        foreach ([
            ['slug' => 'story-cyan-water', 'title' => 'Cyan Water', 'event' => 'cyan-waterpark-jeddah', 'category' => 'experiences', 'image' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=900&q=80', 'sort' => 1],
            ['slug' => 'story-today-events', 'title' => 'فعاليات اليوم', 'event' => 'today-riyadh-family-fun', 'category' => 'today', 'image' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=900&q=80', 'sort' => 2],
            ['slug' => 'story-aquarabia', 'title' => 'أكوارابيا - تجربة', 'event' => 'aquarabia-riyadh-experience', 'category' => 'experiences', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80', 'sort' => 3],
            ['slug' => 'story-sports-cup', 'title' => 'كأس الرياض', 'event' => 'riyadh-sports-cup', 'category' => 'sports', 'image' => 'https://images.unsplash.com/photo-1547347298-4074fc3086f0?auto=format&fit=crop&w=900&q=80', 'sort' => 4],
            ['slug' => 'story-concerts', 'title' => 'حفلات الموسم', 'event' => 'angham-eid-concert-2026', 'category' => 'concerts', 'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=900&q=80', 'sort' => 5],
        ] as $story) {
            $storyEvent = Event::where('slug', $story['event'])->first();

            HomepageItem::updateOrCreate(
                ['slug' => $story['slug']],
                [
                    'title' => $story['title'],
                    'section_key' => 'app_stories',
                    'content_type' => 'story',
                    'ad_type' => 'app_story',
                    'category_id' => $webookCategories[$story['category']]->id ?? null,
                    'city_id' => $jeddah->id,
                    'event_id' => $storyEvent?->id,
                    'image_url' => $story['image'],
                    'hero_image_url' => $story['image'],
                    'subtitle' => 'استوري تفاعلي داخل تطبيق WBK يعرض تفاصيل مختصرة ويفتح الفعالية المرتبطة.',
                    'cta_label' => 'عرض التفاصيل',
                    'cta_url' => $storyEvent ? null : '/events',
                    'meta_label' => 'استوري التطبيق',
                    'badge' => 'جديد',
                    'sort_order' => $story['sort'],
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(2),
                    'is_active' => true,
                ]
            );
        }

        foreach ([
            ['reference' => 'ASEER-CYAN-001', 'event' => 'cyan-waterpark-jeddah', 'ticket' => 'تذكرة كبار', 'quantity' => 2],
            ['reference' => 'ASEER-FOOT-001', 'event' => 'football-fans-night', 'ticket' => 'دخول عام', 'quantity' => 1],
            ['reference' => 'ASEER-AQUA-001', 'event' => 'aquarabia-riyadh-experience', 'ticket' => 'تذكرة مميزة', 'quantity' => 1],
        ] as $demoBooking) {
            $bookingEvent = Event::where('slug', $demoBooking['event'])->with('tickets')->first();
            $ticket = $bookingEvent?->tickets->firstWhere('name', $demoBooking['ticket']) ?? $bookingEvent?->tickets->first();

            if (! $bookingEvent || ! $ticket) {
                continue;
            }

            $lineTotal = (float) $ticket->price * $demoBooking['quantity'];
            $booking = Booking::updateOrCreate(
                ['reference' => $demoBooking['reference']],
                [
                    'user_id' => $customer->id,
                    'event_id' => $bookingEvent->id,
                    'coupon_id' => null,
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'booking_date' => now()->subDays(rand(1, 5)),
                    'subtotal_amount' => $lineTotal,
                    'discount_amount' => 0,
                    'total_amount' => $lineTotal,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                ]
            );

            $booking->items()->delete();
            $booking->items()->create([
                'ticket_id' => $ticket->id,
                'ticket_name' => $ticket->name,
                'quantity' => $demoBooking['quantity'],
                'unit_price' => $ticket->price,
                'line_total' => $lineTotal,
                'attendee_date' => $bookingEvent->start_date,
                'qr_token' => (string) Str::uuid(),
            ]);

            $booking->payment()->delete();
            $booking->payment()->create([
                'gateway' => 'cash',
                'transaction_reference' => 'PAY-'.$demoBooking['reference'],
                'amount' => $lineTotal,
                'currency' => 'SAR',
                'status' => 'paid',
                'paid_at' => now()->subDays(rand(1, 5)),
                'payload' => ['seeded' => true, 'source' => 'dashboard-demo'],
            ]);
        }

        $resaleBooking = Booking::with(['items', 'event'])
            ->where('reference', 'ASEER-FOOT-001')
            ->first();
        $resaleItem = $resaleBooking?->items->first();

        if ($resaleBooking && $resaleItem) {
            ResaleListing::updateOrCreate(
                ['reference' => 'RSL-DEMO-ASEER-FOOT'],
                [
                    'booking_item_id' => $resaleItem->id,
                    'seller_id' => $customer->id,
                    'buyer_id' => null,
                    'event_id' => $resaleBooking->event_id,
                    'ticket_id' => $resaleItem->ticket_id,
                    'price' => 65,
                    'currency' => 'SAR',
                    'status' => ResaleListing::STATUS_ACTIVE,
                    'listed_at' => now()->subHours(2),
                    'sold_at' => null,
                    'expires_at' => $resaleBooking->event?->end_date?->copy()->subMinutes(30),
                    'seller_note' => 'تذكرة تجريبية معروضة للبيع من بوابة إعادة البيع.',
                ]
            );
        }

        $supportConversation = SupportConversation::updateOrCreate(
            ['username' => 'aseer_guest'],
            [
                'user_id' => $customer->id,
                'access_token' => 'seeded-support-conversation-token',
                'customer_name' => 'عميل منصة عسير',
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'bio' => 'مهتم بالفعاليات المائية والحفلات ويريد الانضمام للمجتمعات داخل التطبيق.',
                'topic' => 'community',
                'status' => 'open',
                'priority' => 'normal',
                'last_message_at' => now(),
            ]
        );

        $supportConversation->messages()->delete();
        $supportConversation->messages()->createMany([
            [
                'sender_type' => 'customer',
                'sender_id' => $customer->id,
                'body' => 'مرحباً، أريد الانضمام للمجتمعات وفتح محادثة مع المسؤول.',
            ],
            [
                'sender_type' => 'admin',
                'sender_id' => $admin->id,
                'body' => 'أهلاً بك في منصة عسير. يسعدنا مساعدتك، أخبرنا بالفعالية أو المجتمع الذي تريد الانضمام له.',
            ],
        ]);

        Coupon::updateOrCreate(
            ['code' => 'FARAH10'],
            ['type' => 'percentage', 'value' => 10, 'starts_at' => now()->subDay(), 'expires_at' => now()->addMonth(), 'usage_limit' => 500, 'used_count' => 0, 'is_active' => true]
        );

        Coupon::updateOrCreate(
            ['code' => 'CYAN25'],
            ['type' => 'fixed', 'value' => 25, 'starts_at' => now()->subDay(), 'expires_at' => now()->addWeeks(2), 'usage_limit' => 100, 'used_count' => 0, 'is_active' => true]
        );

        HomepageItem::updateOrCreate(
            ['title' => 'Cyan Water Carnival', 'section_key' => 'hero_banners'],
            [
                'slug' => 'cyan-water-carnival-jeddah',
                'subtitle' => 'روائع مائية للترفيه تبدأ من 60 ريال مع أنشطة عائلية وعروض موسمية.',
                'image_url' => $event->images()->first()?->image_url ?? 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1600&q=80',
                'hero_image_url' => $event->images()->first()?->image_url ?? 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1600&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                ],
                'cta_label' => 'احجز الآن',
                'ad_type' => 'homepage_hero',
                'content_type' => 'event',
                'category_id' => $adventure->id,
                'city_id' => $jeddah->id,
                'event_id' => $event->id,
                'venue_name' => 'سيان واتر بارك - أبحر الشمالية',
                'date_label' => 'الثلاثاء 31 مارس - الخميس 30 أبريل',
                'description' => 'استمتعوا بيوم ترفيهي يجمع بين المياه، الشاطئ، والأجواء الصيفية في تجربة متكاملة تناسب العائلات والشباب داخل جدة.',
                'includes' => "التذاكر تشمل:\n- دخول الموقع\n- جلسات شاطئية\n- أنشطة خفيفة\n- مناطق تصوير",
                'terms' => "• التذاكر غير قابلة للاسترداد بعد التأكيد.\n• الالتزام بالتعليمات داخل الموقع.\n• يمنع إدخال الممنوعات أو المواد الخطرة.",
                'schedule' => "ساعات العمل\n09:00 - 18:00",
                'directions' => "بالسيارة: الوصول عبر طريق الأمير عبدالله الفيصل.\nبالأجرة: يمكن تحديد الموقع عبر خرائط جوجل أو تطبيقات النقل.",
                'location_title' => 'منتجع القرية',
                'location_code' => '7GHXP38J+6J',
                'map_url' => 'https://maps.google.com/maps?q=Jeddah%20Corniche&t=&z=13&ie=UTF8&iwloc=&output=embed',
                'price_label' => 'SAR 60.00',
                'meta_label' => 'عروض الصيف في جدة',
                'badge' => 'حصري',
                'rating' => 4.5,
                'sort_order' => 1,
                'open_in_new_tab' => false,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(2),
                'is_active' => true,
            ]
        );

        HomepageItem::updateOrCreate(
            ['title' => 'شاطئ لابلايا في جدة', 'section_key' => 'featured_events'],
            [
                'slug' => 'la-playa-jeddah',
                'subtitle' => 'تجربة شاطئية هادئة مع جلسات وموسيقى خفيفة.',
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                'hero_image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80',
                'content_type' => 'place',
                'category_id' => $adventure->id,
                'city_id' => $jeddah->id,
                'venue_name' => 'شاطئ لابلايا',
                'date_label' => 'الثلاثاء 31 مارس - الخميس 30 أبريل',
                'description' => 'شاطئ خاص بأجواء مميزة في جدة مع جلسات مريحة ومناظر بحرية وتجربة مناسبة للاسترخاء والفعاليات النهارية.',
                'includes' => "• دخول الشاطئ\n• جلسات ومظلات\n• موسيقى خفيفة\n• مطاعم وكافيهات قريبة",
                'terms' => "• الالتزام بلباس المكان.\n• التذاكر غير مستردة بعد التفعيل.\n• يمنع إدخال المأكولات الخارجية.",
                'schedule' => "ساعات العمل\n10:00 - 20:00",
                'directions' => "الوصول عبر كورنيش جدة الشمالي، مع توفر مواقف قريبة من موقع الفعالية.",
                'location_title' => 'شاطئ لابلايا',
                'location_code' => 'Jeddah North Corniche',
                'map_url' => 'https://maps.google.com/maps?q=Jeddah%20North%20Corniche&t=&z=13&ie=UTF8&iwloc=&output=embed',
                'price_label' => 'SAR 65.00',
                'meta_label' => 'نضمن أفضل الأسعار',
                'badge' => 'حصري',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        HomepageItem::updateOrCreate(
            ['slug' => 'shaker-standup-jeddah'],
            [
                'title' => 'ليلة ستاند أب كوميدي مع شاكر الشريف',
                'section_key' => 'hero_banners',
                'content_type' => 'event',
                'category_id' => $adventure->id,
                'city_id' => $jeddah->id,
                'image_url' => 'https://images.unsplash.com/photo-1527224538127-2104bb71c51b?auto=format&fit=crop&w=1600&q=80',
                'hero_image_url' => 'https://images.unsplash.com/photo-1527224538127-2104bb71c51b?auto=format&fit=crop&w=1600&q=80',
                'subtitle' => 'تجربة مسرحية كوميدية مميزة في جدة مع مقاعد محدودة وحجز فوري.',
                'cta_label' => 'اكتشف التفاصيل',
                'venue_name' => 'مسرح جدة',
                'date_label' => 'الأربعاء 01 أبريل',
                'description' => 'أمسية كوميدية بطابع سريع وممتع مع جمهور جدة.',
                'includes' => "• دخول العرض\n• مقعد حسب الفئة",
                'terms' => "• يمنع التصوير أثناء العرض.\n• التذاكر غير قابلة للاسترداد.",
                'schedule' => "08:30 م فتح الأبواب\n09:00 م بداية العرض",
                'directions' => 'الوصول عبر وسط جدة مع مواقف قريبة.',
                'location_title' => 'مسرح جدة',
                'location_code' => 'JED-STANDUP',
                'map_url' => 'https://maps.google.com/maps?q=Jeddah%20Theatre&t=&z=13&ie=UTF8&iwloc=&output=embed',
                'price_label' => 'SAR 85.00',
                'meta_label' => 'الأربعاء 01 أبريل',
                'badge' => 'حصري',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        HomepageItem::updateOrCreate(
            ['slug' => 'beach-tanning-day-jeddah'],
            [
                'title' => 'Beach Tanning Day',
                'section_key' => 'hero_banners',
                'content_type' => 'activity',
                'category_id' => $adventure->id,
                'city_id' => $jeddah->id,
                'image_url' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=1600&q=80',
                'hero_image_url' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=1600&q=80',
                'subtitle' => 'أجواء شاطئية نهارية وموسيقى خفيفة على البحر.',
                'cta_label' => 'استعرض الفعالية',
                'venue_name' => 'منتجع القرية',
                'date_label' => 'الثلاثاء 31 مارس - الخميس 30 أبريل',
                'description' => 'فعالية نهارية للبحر والاسترخاء والتان في أجواء راقية.',
                'includes' => "• دخول الشاطئ\n• جلسات مظللة\n• موسيقى خفيفة",
                'terms' => "• الحجز مسبق.\n• الالتزام بزي المكان.",
                'schedule' => "10:00 ص - 08:00 م",
                'directions' => 'كورنيش جدة الشمالي.',
                'location_title' => 'منتجع القرية',
                'location_code' => 'QARYA-BEACH',
                'map_url' => 'https://maps.google.com/maps?q=Jeddah%20North%20Corniche&t=&z=13&ie=UTF8&iwloc=&output=embed',
                'price_label' => 'SAR 55.00',
                'meta_label' => 'حصري في جدة',
                'badge' => 'حصري',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        foreach ([
            [
                'slug' => 'hero-aquarabia-games',
                'title' => 'أكوارابيا - ألعاب وترفيه عائلي',
                'event' => 'aquarabia-riyadh-experience',
                'category' => 'experiences',
                'image' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=1600&q=80',
                'subtitle' => 'مدينة ألعاب مائية وتجارب صيفية للعائلة مع تذاكر فورية.',
                'badge' => 'ألعاب',
                'price' => 'SAR 120.00',
                'sort' => 4,
            ],
            [
                'slug' => 'hero-football-fans-night',
                'title' => 'ليلة جماهير كرة القدم',
                'event' => 'football-fans-night',
                'category' => 'football',
                'image' => 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?auto=format&fit=crop&w=1600&q=80',
                'subtitle' => 'أجواء جماهيرية، شاشات عملاقة، وتذاكر قابلة لإعادة البيع.',
                'badge' => 'رياضة',
                'price' => 'SAR 65.00',
                'sort' => 5,
            ],
            [
                'slug' => 'hero-entertainment-concert',
                'title' => 'حفلات الموسم وتجارب الترفيه',
                'event' => 'angham-eid-concert-2026',
                'category' => 'concerts',
                'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1600&q=80',
                'subtitle' => 'حفلات وموسيقى مباشرة مع مقاعد محدودة وعروض متجددة.',
                'badge' => 'ترفيه',
                'price' => 'SAR 250.00',
                'sort' => 6,
            ],
            [
                'slug' => 'hero-fun-restaurants',
                'title' => 'مطاعم وتجارب ممتعة',
                'event' => 'webook-fun-restaurants',
                'category' => 'restaurants',
                'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1600&q=80',
                'subtitle' => 'اختيارات طعام، عروض خاصة، وتجارب اجتماعية في مكان واحد.',
                'badge' => 'مطاعم',
                'price' => 'SAR 35.00',
                'sort' => 7,
            ],
        ] as $hero) {
            $heroEvent = Event::where('slug', $hero['event'])->first();

            HomepageItem::updateOrCreate(
                ['slug' => $hero['slug']],
                [
                    'title' => $hero['title'],
                    'section_key' => 'hero_banners',
                    'content_type' => 'event',
                    'ad_type' => 'homepage_hero',
                    'category_id' => $webookCategories[$hero['category']]->id ?? $adventure->id,
                    'city_id' => $jeddah->id,
                    'event_id' => $heroEvent?->id,
                    'image_url' => $hero['image'],
                    'hero_image_url' => $hero['image'],
                    'subtitle' => $hero['subtitle'],
                    'cta_label' => 'احجز الآن',
                    'venue_name' => $heroEvent?->venue_name,
                    'date_label' => $heroEvent?->start_date?->translatedFormat('l، d F'),
                    'description' => $hero['subtitle'],
                    'price_label' => $hero['price'],
                    'meta_label' => 'بنر متحرك من الداش بورد',
                    'badge' => $hero['badge'],
                    'sort_order' => $hero['sort'],
                    'open_in_new_tab' => false,
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(3),
                    'is_active' => true,
                ]
            );
        }

        foreach ([
            ['slug' => 'monday-studio-jeddah', 'title' => 'استديو الاثنين - عرض ستاند أب كوميدي في جدة', 'section_key' => 'featured_events', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 200.00', 'meta' => 'الخميس 02 أبريل', 'badge' => 'حصري', 'sort' => 2],
            ['slug' => 'qarya-beach-jeddah', 'title' => 'شاطئ منتجع القرية في جدة', 'section_key' => 'featured_events', 'content_type' => 'place', 'image' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 55.00', 'meta' => 'الثلاثاء 31 مارس - الخميس 30 أبريل', 'badge' => 'حصري', 'sort' => 3],
            ['slug' => 'reda-alharam-jeddah', 'title' => 'رداء الهرم في جدة', 'section_key' => 'featured_events', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 40.00', 'meta' => 'السبت 04 أبريل', 'badge' => 'حصري', 'sort' => 4],
            ['slug' => 'rixos-obhur-jeddah', 'title' => 'ريكسوس أبحر جدة', 'section_key' => 'featured_tourism', 'content_type' => 'place', 'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 150.00', 'meta' => 'تباع سريعاً', 'badge' => null, 'sort' => 1],
            ['slug' => 'tropical-land-jeddah', 'title' => 'تروبيكال لاند', 'section_key' => 'featured_tourism', 'content_type' => 'place', 'image' => 'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 70.00', 'meta' => 'نضمن أفضل الأسعار', 'badge' => null, 'sort' => 2],
            ['slug' => 'laplaya-sunset-pass', 'title' => 'لابلايا - جلسة الغروب', 'section_key' => 'featured_tourism', 'content_type' => 'place', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 95.00', 'meta' => 'تجربة بحرية مميزة', 'badge' => 'جديد', 'sort' => 3],
            ['slug' => 'mountain-escape-abha', 'title' => 'رحلة الجبال في أبها', 'section_key' => 'featured_tourism', 'content_type' => 'place', 'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 120.00', 'meta' => 'أماكن طبيعية', 'badge' => null, 'sort' => 4],
            ['slug' => 'heritage-walk-jeddah', 'title' => 'جولة البلد التاريخية', 'section_key' => 'featured_tourism', 'content_type' => 'activity', 'image' => 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 45.00', 'meta' => 'مرشد محلي', 'badge' => 'حصري', 'sort' => 5],
            ['slug' => 'today-karazal-jeddah', 'title' => 'نادي كارازل الاجتماعي في جدة', 'section_key' => 'today_events', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 200.00', 'meta' => 'الاثنين 30 مارس - الخميس 30 أبريل', 'badge' => 'حصري', 'sort' => 1],
            ['slug' => 'today-food-festival', 'title' => 'مهرجان مذاق جدة اليوم', 'section_key' => 'today_events', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 35.00', 'meta' => 'اليوم 08:00 م', 'badge' => 'اليوم', 'sort' => 2],
            ['slug' => 'today-family-games', 'title' => 'ألعاب عائلية في الواجهة', 'section_key' => 'today_events', 'content_type' => 'activity', 'image' => 'https://images.unsplash.com/photo-1511882150382-421056c89033?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 55.00', 'meta' => 'اليوم 06:00 م', 'badge' => 'عائلي', 'sort' => 3],
            ['slug' => 'today-jazz-lounge', 'title' => 'جلسة جاز لاونج', 'section_key' => 'today_events', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 110.00', 'meta' => 'اليوم 10:00 م', 'badge' => 'موسيقى', 'sort' => 4],
            ['slug' => 'nightlife-enoo-napa', 'title' => 'OBE presents Enoo Napa', 'section_key' => 'nightlife', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 250.00', 'meta' => 'الخميس 02 أبريل', 'badge' => 'جديد', 'sort' => 1],
            ['slug' => 'nightlife-rooftop-session', 'title' => 'Rooftop Session جدة', 'section_key' => 'nightlife', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 180.00', 'meta' => 'الجمعة 10:00 م', 'badge' => 'حصري', 'sort' => 2],
            ['slug' => 'nightlife-deep-house', 'title' => 'Deep House Night', 'section_key' => 'nightlife', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 210.00', 'meta' => 'السبت 11:30 م', 'badge' => 'جديد', 'sort' => 3],
            ['slug' => 'nightlife-ladies-evening', 'title' => 'ليلة السيدات الموسيقية', 'section_key' => 'nightlife', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 160.00', 'meta' => 'الأربعاء 09:00 م', 'badge' => 'مميز', 'sort' => 4],
            ['slug' => 'arabic-fahad-bin-fasla', 'title' => 'حفل فهد بن فصلا في جدة', 'section_key' => 'arabic_guide', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 150.00', 'meta' => 'اسرع بالحجز، ستنفد التذاكر', 'badge' => 'جديد', 'sort' => 1],
            ['slug' => 'arabic-tarab-night', 'title' => 'ليلة طرب عربية', 'section_key' => 'arabic_guide', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 130.00', 'meta' => 'الخميس 09:00 م', 'badge' => 'طرب', 'sort' => 2],
            ['slug' => 'arabic-poetry-evening', 'title' => 'أمسية شعر وغناء', 'section_key' => 'arabic_guide', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 75.00', 'meta' => 'السبت 08:30 م', 'badge' => 'ثقافي', 'sort' => 3],
            ['slug' => 'arabic-oud-maqam', 'title' => 'مقام العود', 'section_key' => 'arabic_guide', 'content_type' => 'concert', 'image' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 95.00', 'meta' => 'الأحد 08:00 م', 'badge' => 'جديد', 'sort' => 4],
            ['slug' => 'theatre-shaker-night', 'title' => 'ليلة ستاند أب كوميدي مع شاكر الشريف 1 أبريل في جدة', 'section_key' => 'theatre', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1527224538127-2104bb71c51b?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 85.00', 'meta' => 'الأربعاء 01 أبريل', 'badge' => 'حصري', 'sort' => 1],
            ['slug' => 'theatre-family-play', 'title' => 'مسرحية العائلة المرحة', 'section_key' => 'theatre', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 70.00', 'meta' => 'الجمعة 07:00 م', 'badge' => 'عائلي', 'sort' => 2],
            ['slug' => 'theatre-improv-night', 'title' => 'ارتجال كوميدي مباشر', 'section_key' => 'theatre', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1515168833906-d2a3b82b302b?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 90.00', 'meta' => 'السبت 09:00 م', 'badge' => 'كوميدي', 'sort' => 3],
            ['slug' => 'theatre-magic-show', 'title' => 'عرض الخفة والدهشة', 'section_key' => 'theatre', 'content_type' => 'event', 'image' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 115.00', 'meta' => 'الأحد 06:30 م', 'badge' => 'حصري', 'sort' => 4],
            ['slug' => 'nearby-miramar-khobar', 'title' => 'شاطئ ميرامار - الخبر', 'section_key' => 'nearby_entertainment', 'content_type' => 'place', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 50.00', 'meta' => 'تباع سريعاً', 'badge' => 'حصري', 'sort' => 1],
            ['slug' => 'nearby-abha-clouds', 'title' => 'ممشى السحاب - أبها', 'section_key' => 'nearby_entertainment', 'content_type' => 'place', 'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 65.00', 'meta' => 'رحلة قريبة', 'badge' => 'طبيعة', 'sort' => 2],
            ['slug' => 'nearby-dammam-sea', 'title' => 'كورنيش الدمام لايف', 'section_key' => 'nearby_entertainment', 'content_type' => 'activity', 'image' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 40.00', 'meta' => 'تباع سريعاً', 'badge' => 'قريب', 'sort' => 3],
            ['slug' => 'nearby-riyadh-games', 'title' => 'منطقة ألعاب الرياض', 'section_key' => 'nearby_entertainment', 'content_type' => 'activity', 'image' => 'https://images.unsplash.com/photo-1511882150382-421056c89033?auto=format&fit=crop&w=900&q=80', 'price' => 'SAR 75.00', 'meta' => 'عائلي', 'badge' => 'جديد', 'sort' => 4],
        ] as $item) {
            HomepageItem::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'section_key' => $item['section_key'],
                    'content_type' => $item['content_type'],
                    'ad_type' => in_array($item['section_key'], ['featured_events', 'featured_tourism', 'today_events', 'nightlife', 'arabic_guide', 'theatre', 'nearby_entertainment']) ? 'homepage_card' : 'homepage_hero',
                    'category_id' => $adventure->id,
                    'city_id' => $jeddah->id,
                    'image_url' => $item['image'],
                    'hero_image_url' => $item['image'],
                    'subtitle' => $item['title'],
                    'price_label' => $item['price'],
                    'meta_label' => $item['meta'],
                    'badge' => $item['badge'],
                    'sort_order' => $item['sort'],
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(3),
                    'is_active' => true,
                ]
            );
        }

        foreach ([
            ['slug' => 'category-waterparks', 'title' => 'المنتزهات المائية', 'section' => 'categories_showcase', 'type' => 'category', 'image' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=600&q=80', 'sort' => 1],
            ['slug' => 'category-outdoor', 'title' => 'أنشطة في الهواء الطلق', 'section' => 'categories_showcase', 'type' => 'category', 'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80', 'sort' => 2],
            ['slug' => 'category-theatre', 'title' => 'العروض والمسرحيات', 'section' => 'categories_showcase', 'type' => 'category', 'image' => 'https://images.unsplash.com/photo-1527224538127-2104bb71c51b?auto=format&fit=crop&w=600&q=80', 'sort' => 3],
            ['slug' => 'category-family', 'title' => 'فعاليات عائلية', 'section' => 'categories_showcase', 'type' => 'category', 'image' => 'https://images.unsplash.com/photo-1511882150382-421056c89033?auto=format&fit=crop&w=600&q=80', 'sort' => 4],
            ['slug' => 'category-concerts', 'title' => 'الحفلات الموسيقية', 'section' => 'categories_showcase', 'type' => 'category', 'image' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=600&q=80', 'sort' => 5],
            ['slug' => 'category-food', 'title' => 'تجارب المطاعم', 'section' => 'categories_showcase', 'type' => 'category', 'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80', 'sort' => 6],
            ['slug' => 'artist-shaker', 'title' => 'شاكر الشريف', 'section' => 'artists', 'type' => 'artist', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=500&q=80', 'sort' => 1],
            ['slug' => 'artist-eno', 'title' => 'انو ناپا', 'section' => 'artists', 'type' => 'artist', 'image' => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=500&q=80', 'sort' => 2],
            ['slug' => 'artist-noura', 'title' => 'نورة لايف', 'section' => 'artists', 'type' => 'artist', 'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=500&q=80', 'sort' => 3],
            ['slug' => 'artist-salem-band', 'title' => 'فرقة سالم', 'section' => 'artists', 'type' => 'artist', 'image' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=500&q=80', 'sort' => 4],
            ['slug' => 'place-laplaya-bahr', 'title' => 'شاطئ لابلايا البحر', 'section' => 'places', 'type' => 'place', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80', 'sort' => 1],
            ['slug' => 'place-shallal', 'title' => 'حديقة الشلال الترفيهية', 'section' => 'places', 'type' => 'place', 'image' => 'https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=900&q=80', 'sort' => 2],
            ['slug' => 'place-old-jeddah', 'title' => 'جدة التاريخية', 'section' => 'places', 'type' => 'place', 'image' => 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=900&q=80', 'sort' => 3],
            ['slug' => 'place-riyadh-front', 'title' => 'واجهة الرياض', 'section' => 'places', 'type' => 'place', 'image' => 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?auto=format&fit=crop&w=900&q=80', 'sort' => 4],
            ['slug' => 'place-abha-view', 'title' => 'مطل أبها', 'section' => 'places', 'type' => 'place', 'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80', 'sort' => 5],
            ['slug' => 'city-riyadh-circle', 'title' => 'الرياض', 'section' => 'city_circles', 'type' => 'city', 'image' => 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?auto=format&fit=crop&w=500&q=80', 'sort' => 1],
            ['slug' => 'city-abha-circle', 'title' => 'أبها', 'section' => 'city_circles', 'type' => 'city', 'image' => 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=500&q=80', 'sort' => 2],
            ['slug' => 'city-jeddah-circle', 'title' => 'جدة', 'section' => 'city_circles', 'type' => 'city', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=500&q=80', 'sort' => 3],
            ['slug' => 'city-dammam-circle', 'title' => 'الدمام', 'section' => 'city_circles', 'type' => 'city', 'image' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=500&q=80', 'sort' => 4],
            ['slug' => 'tag-nightlife', 'title' => 'السهرات الليلية', 'section' => 'other_tags', 'type' => 'tag', 'image' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=300&q=80', 'sort' => 1],
            ['slug' => 'tag-arabic-guide', 'title' => 'دليل الفعاليات العربية', 'section' => 'other_tags', 'type' => 'tag', 'image' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=300&q=80', 'sort' => 2],
            ['slug' => 'tag-comedy', 'title' => 'العروض الكوميدية', 'section' => 'other_tags', 'type' => 'tag', 'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=300&q=80', 'sort' => 3],
            ['slug' => 'tag-family', 'title' => 'فعاليات عائلية', 'section' => 'other_tags', 'type' => 'tag', 'image' => 'https://images.unsplash.com/photo-1511882150382-421056c89033?auto=format&fit=crop&w=300&q=80', 'sort' => 4],
            ['slug' => 'tag-food', 'title' => 'مطاعم وتجارب', 'section' => 'other_tags', 'type' => 'tag', 'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=300&q=80', 'sort' => 5],
        ] as $item) {
            HomepageItem::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'section_key' => $item['section'],
                    'content_type' => $item['type'],
                    'city_id' => $jeddah->id,
                    'image_url' => $item['image'],
                    'hero_image_url' => $item['image'],
                    'ad_type' => $item['section'] === 'city_circles' ? 'category_banner' : 'homepage_card',
                    'meta_label' => $item['section'] === 'places' ? '1 الفعاليات القادمة' : null,
                    'sort_order' => $item['sort'],
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(3),
                    'is_active' => true,
                ]
            );
        }

        foreach ([
            'platform_name' => 'منصة فرح',
            'platform_tagline' => 'منصة حجوزات وفعاليات وتذاكر عربية',
            'platform_logo_url' => asset('branding/aseer-logo.png'),
            'platform_favicon_url' => asset('branding/aseer-logo.png'),
            'support_email' => 'support@farah.sa',
            'support_phone' => '920008640',
            'support_whatsapp' => '0500000000',
            'platform_address' => 'المملكة العربية السعودية',
            'default_currency' => 'SAR',
            'default_locale' => 'ar',
            'service_fee' => '0',
            'tax_percentage' => '15',
            'seo_meta_title' => 'منصة فعاليات وتذاكر',
            'seo_meta_description' => 'منصة عربية لحجز الفعاليات والتذاكر وإدارة المحتوى والإعلانات.',
            'social_instagram' => '',
            'social_x' => '',
            'social_tiktok' => '',
            'social_snapchat' => '',
            'payment_stripe_enabled' => '1',
            'payment_paypal_enabled' => '1',
            'payment_mada_enabled' => '1',
            'homepage_hero_title' => 'اكتشف أفضل الفعاليات',
            'homepage_hero_subtitle' => 'احجز الفعاليات والتجارب في مدينتك',
            'footer_about' => 'منصة لاكتشاف وتسويق المحتوى الترفيهي والفعاليات العربية.',
        ] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['group' => 'general', 'value' => $value]
            );
        }

        foreach ([
            [
                'title' => 'من نحن',
                'slug' => 'about-us',
                'excerpt' => 'تعرف على منصة فرح ورسالتها في تنظيم واكتشاف الفعاليات العربية.',
                'body' => "منصة فرح هي منصة عربية متخصصة في اكتشاف الفعاليات وبيع التذاكر وإدارة المحتوى الترفيهي.\n\nنساعد المستخدم على الوصول السريع إلى الفعالية المناسبة، كما نتيح للمنظمين إدارة فعالياتهم ومبيعاتهم ضمن تجربة احترافية.",
                'sort_order' => 1,
                'show_in_footer' => true,
                'footer_group' => 'about',
            ],
            [
                'title' => 'الشروط والأحكام',
                'slug' => 'terms-and-conditions',
                'excerpt' => 'الشروط العامة لاستخدام المنصة وشراء التذاكر.',
                'body' => "باستخدامك للمنصة فإنك توافق على الشروط والأحكام المعمول بها.\n\nقد تختلف بعض السياسات حسب الفعالية أو الجهة المنظمة، وتكون سياسة الفعالية الخاصة هي المرجع في حال وجودها.",
                'sort_order' => 2,
                'show_in_footer' => true,
                'footer_group' => 'about',
            ],
            [
                'title' => 'سياسة الخصوصية',
                'slug' => 'privacy-policy',
                'excerpt' => 'كيف نتعامل مع بيانات المستخدمين وحمايتها.',
                'body' => "نلتزم بحماية بيانات المستخدمين وعدم مشاركتها خارج الأغراض التشغيلية والقانونية اللازمة.\n\nيتم استخدام البيانات لتحسين تجربة الحجز والتواصل وتأكيد الطلبات.",
                'sort_order' => 3,
                'show_in_footer' => true,
                'footer_group' => 'about',
            ],
            [
                'title' => 'سياسة الاسترجاع',
                'slug' => 'refund-policy',
                'excerpt' => 'متى يمكن استرجاع المبالغ أو إلغاء الحجز.',
                'body' => "سياسة الاسترجاع قد تختلف حسب نوع الفعالية والمنظم.\n\nيرجى مراجعة صفحة الفعالية نفسها قبل إتمام الشراء، حيث تكون الشروط الخاصة بالمنظم جزءاً من سياسة الاسترجاع.",
                'sort_order' => 4,
                'show_in_footer' => true,
                'footer_group' => 'about',
            ],
            [
                'title' => 'تواصل معنا',
                'slug' => 'contact-us',
                'excerpt' => 'قنوات الدعم والتواصل مع فريق المنصة.',
                'body' => "للاستفسارات العامة أو الدعم الفني يمكنكم التواصل عبر البريد الرسمي أو رقم خدمة العملاء أو الواتساب الظاهر في المنصة.",
                'sort_order' => 5,
                'show_in_footer' => true,
                'footer_group' => 'about',
            ],
        ] as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                $pageData + [
                    'meta_title' => $pageData['title'],
                    'meta_description' => $pageData['excerpt'],
                    'is_active' => true,
                ]
            );
        }

        foreach ([
            ['title' => 'أبرز الفعاليات', 'slug' => 'featured-events', 'footer_group' => 'categories', 'sort_order' => 10],
            ['title' => 'العالم السياحية المميزة', 'slug' => 'featured-tourism', 'footer_group' => 'categories', 'sort_order' => 11],
            ['title' => 'السهرات الليلية', 'slug' => 'nightlife-guide', 'footer_group' => 'categories', 'sort_order' => 12],
            ['title' => 'نوادي الشاطئ', 'slug' => 'beach-clubs', 'footer_group' => 'categories', 'sort_order' => 13],
            ['title' => 'دليل الفعاليات العربية', 'slug' => 'arabic-events-guide', 'footer_group' => 'categories', 'sort_order' => 14],
            ['title' => 'إظهار الكل', 'slug' => 'all-categories', 'footer_group' => 'categories', 'sort_order' => 15],
            ['title' => 'انضم لفريقنا', 'slug' => 'join-our-team', 'footer_group' => 'about', 'sort_order' => 20],
            ['title' => 'الأسعار', 'slug' => 'pricing', 'footer_group' => 'about', 'sort_order' => 21],
            ['title' => 'مدونة منصة عسير', 'slug' => 'blog', 'footer_group' => 'about', 'sort_order' => 22],
            ['title' => 'آخر الأخبار', 'slug' => 'latest-news', 'footer_group' => 'about', 'sort_order' => 23],
            ['title' => 'مركز المساعدة', 'slug' => 'help-center', 'footer_group' => 'about', 'sort_order' => 24, 'target_url' => '/faq'],
            ['title' => 'خريطة الموقع', 'slug' => 'sitemap', 'footer_group' => 'about', 'sort_order' => 25],
            ['title' => 'نظرة عامة للمنظمين', 'slug' => 'organizers-overview', 'footer_group' => 'organizers', 'sort_order' => 30],
            ['title' => 'الفعاليات الترفيهية', 'slug' => 'organizers-entertainment', 'footer_group' => 'organizers', 'sort_order' => 31],
            ['title' => 'المغامرات والتجارب الاستثنائية', 'slug' => 'organizers-adventures', 'footer_group' => 'organizers', 'sort_order' => 32],
            ['title' => 'فعاليات قطاع الأعمال', 'slug' => 'business-events', 'footer_group' => 'organizers', 'sort_order' => 33],
            ['title' => 'الأنشطة والأحداث الرياضية', 'slug' => 'sports-activities', 'footer_group' => 'organizers', 'sort_order' => 34],
            ['title' => 'حلول تذاكر الفعاليات', 'slug' => 'ticketing-solutions', 'footer_group' => 'organizers', 'sort_order' => 35],
            ['title' => 'مميزات التذاكر', 'slug' => 'ticket-benefits', 'footer_group' => 'organizers', 'sort_order' => 36],
            ['title' => 'دليل المنظمين', 'slug' => 'organizers-directory', 'footer_group' => 'organizers', 'sort_order' => 37],
            ['title' => 'خدمات إدارة الفعاليات', 'slug' => 'event-management-services', 'footer_group' => 'services', 'sort_order' => 40],
            ['title' => 'خدمات التسويق', 'slug' => 'marketing-services', 'footer_group' => 'services', 'sort_order' => 41],
            ['title' => 'فريق إدارة التذاكر للفعالية', 'slug' => 'ticketing-team-services', 'footer_group' => 'services', 'sort_order' => 42],
            ['title' => 'طباعة التذاكر', 'slug' => 'ticket-printing', 'footer_group' => 'services', 'sort_order' => 43],
            ['title' => 'خدمة إصدار التراخيص بشكل سريع', 'slug' => 'fast-licensing-service', 'footer_group' => 'services', 'sort_order' => 44],
            ['title' => 'برنامج التسويق بالعمولة', 'slug' => 'affiliate-program', 'footer_group' => 'partners', 'sort_order' => 50],
            ['title' => 'Google Play', 'slug' => 'google-play-app', 'footer_group' => 'apps', 'sort_order' => 60, 'target_url' => 'https://play.google.com/store'],
            ['title' => 'App Store', 'slug' => 'app-store-app', 'footer_group' => 'apps', 'sort_order' => 61, 'target_url' => 'https://www.apple.com/app-store/'],
            ['title' => 'AppGallery', 'slug' => 'app-gallery-app', 'footer_group' => 'apps', 'sort_order' => 62, 'target_url' => 'https://appgallery.huawei.com/'],
        ] as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                [
                    'title' => $pageData['title'],
                    'footer_group' => $pageData['footer_group'],
                    'footer_label' => $pageData['title'],
                    'excerpt' => $pageData['title'] . ' - صفحة قابلة للتعديل من لوحة التحكم.',
                    'body' => "هذه الصفحة قابلة للتعديل من لوحة التحكم ويمكنك تغيير محتواها أو حذفها أو إخفاءها من الفوتر في أي وقت.",
                    'meta_title' => $pageData['title'],
                    'meta_description' => $pageData['title'],
                    'sort_order' => $pageData['sort_order'],
                    'show_in_footer' => true,
                    'target_url' => $pageData['target_url'] ?? null,
                    'open_in_new_tab' => filled($pageData['target_url'] ?? null),
                    'is_active' => true,
                ]
            );
        }

        foreach ([
            ['question' => 'كيف يمكنني حجز تذكرة؟', 'answer' => 'اختر الفعالية المناسبة ثم حدد نوع التذكرة والعدد وأكمل الدفع من صفحة الحجز.', 'category' => 'الحجز', 'sort_order' => 1],
            ['question' => 'هل أستطيع استرجاع قيمة التذكرة؟', 'answer' => 'يعتمد ذلك على سياسة الفعالية والمنظم. راجع صفحة الفعالية قبل إتمام الدفع.', 'category' => 'الحجز', 'sort_order' => 2],
            ['question' => 'متى تصلني التذكرة؟', 'answer' => 'بعد تأكيد الدفع يتم إنشاء التذكرة وإرسالها إلى البريد الإلكتروني المسجل في الطلب.', 'category' => 'التذاكر', 'sort_order' => 3],
            ['question' => 'ما طرق الدفع المتاحة؟', 'answer' => 'يمكن تفعيل Stripe وPayPal ومدى من إعدادات المنصة، كما يدعم النظام وضع الدفع التجريبي.', 'category' => 'الدفع', 'sort_order' => 4],
            ['question' => 'هل يمكن للمنظم إدارة فعالياته بنفسه؟', 'answer' => 'نعم، المنظم يملك لوحة خاصة به لإدارة فعالياته وتذاكره ومراجعة الأداء.', 'category' => 'المنظمون', 'sort_order' => 5],
        ] as $faqData) {
            Faq::updateOrCreate(
                ['question' => $faqData['question']],
                $faqData + ['is_active' => true]
            );
        }
    }
}
