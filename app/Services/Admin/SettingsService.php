<?php

namespace App\Services\Admin;

use App\Models\Setting;
use Illuminate\Support\Str;

class SettingsService
{
    public function defaults(): array
    {
        return [
            'platform_name' => 'منصة عسير',
            'platform_tagline' => 'منصة حجوزات وفعاليات وتذاكر عربية',
            'platform_logo_url' => asset('branding/aseer-logo.png'),
            'platform_favicon_url' => asset('branding/aseer-logo.png'),
            'support_email' => 'support@aseer.sa',
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
            'footer_categories_title' => 'الفئات',
            'footer_about_title' => 'من نحن',
            'footer_organizers_title' => 'للمنظمين',
            'footer_services_title' => 'الخدمات',
            'footer_partners_title' => 'للشركاء',
            'footer_apps_title' => 'تحميل التطبيق',
            'payment_section_title' => 'نقبل طرق الدفع التالية',
            'customer_service_title' => 'خدمة العملاء',
            'support_section_title' => 'هل لديك أي أسئلة أو استفسارات أخرى؟',
            'support_section_subtitle' => 'يسعدنا تواصلك معنا',
            'support_button_text' => 'فريق الدعم',
            'footer_support_button_text' => 'مركز الدعم',
            'organizer_cta_text' => 'إضافة فعالية',
        ];
    }

    public function getMany(array $defaults): array
    {
        $stored = Setting::whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->all();

        $settings = [];

        foreach ($defaults as $key => $default) {
            $settings[$key] = array_key_exists($key, $stored) ? $stored[$key] : $default;
        }

        $settings['platform_logo_url'] = $this->normalizeAssetUrl($settings['platform_logo_url'] ?? null, 'branding/aseer-logo.png');
        $settings['platform_favicon_url'] = $this->normalizeAssetUrl($settings['platform_favicon_url'] ?? null, 'branding/aseer-logo.png');

        return $settings;
    }

    public function publicSettings(): array
    {
        return $this->getMany($this->defaults());
    }

    public function saveGroup(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value === null ? null : (string) $value),
                ]
            );
        }
    }

    private function normalizeAssetUrl(?string $value, string $fallback): string
    {
        $fallbackUrl = '/'.ltrim($fallback, '/');

        if (blank($value)) {
            return $fallbackUrl;
        }

        $value = trim($value);

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';

            if (Str::contains($path, '/public/branding/')) {
                return '/'.ltrim(Str::after($path, '/public/'), '/');
            }

            if (Str::contains($path, '/public/uploads/')) {
                return '/'.ltrim(Str::after($path, '/public/'), '/');
            }

            if (Str::startsWith($path, ['/branding/', '/uploads/'])) {
                return $path;
            }

            return $value;
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $value) || str_contains($value, '\\')) {
            $basename = basename(str_replace('\\', '/', $value));
            $brandingPath = public_path('branding/'.$basename);

            return file_exists($brandingPath) ? '/branding/'.$basename : $fallbackUrl;
        }

        if (Str::startsWith($value, ['/branding/', 'branding/'])) {
            return '/'.ltrim($value, '/');
        }

        if (Str::startsWith($value, ['/uploads/', 'uploads/'])) {
            return '/'.ltrim($value, '/');
        }

        return $fallbackUrl;
    }
}
