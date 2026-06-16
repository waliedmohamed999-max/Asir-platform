<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'platform_name' => ['required', 'string', 'max:255'],
            'platform_tagline' => ['nullable', 'string', 'max:255'],
            'platform_logo_url' => ['nullable', 'url'],
            'platform_favicon_url' => ['nullable', 'url'],
            'platform_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'platform_favicon_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,ico', 'max:2048'],
            'remove_platform_logo' => ['nullable', 'boolean'],
            'remove_platform_favicon' => ['nullable', 'boolean'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'support_whatsapp' => ['nullable', 'string', 'max:30'],
            'platform_address' => ['nullable', 'string', 'max:255'],
            'default_currency' => ['nullable', 'string', 'max:10'],
            'default_locale' => ['nullable', 'string', 'max:10'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0'],
            'seo_meta_title' => ['nullable', 'string', 'max:255'],
            'seo_meta_description' => ['nullable', 'string', 'max:500'],
            'social_instagram' => ['nullable', 'url'],
            'social_x' => ['nullable', 'url'],
            'social_tiktok' => ['nullable', 'url'],
            'social_snapchat' => ['nullable', 'url'],
            'payment_stripe_enabled' => ['nullable', 'boolean'],
            'payment_paypal_enabled' => ['nullable', 'boolean'],
            'payment_mada_enabled' => ['nullable', 'boolean'],
            'homepage_hero_title' => ['nullable', 'string', 'max:255'],
            'homepage_hero_subtitle' => ['nullable', 'string'],
            'footer_about' => ['nullable', 'string'],
            'footer_categories_title' => ['nullable', 'string', 'max:255'],
            'footer_about_title' => ['nullable', 'string', 'max:255'],
            'footer_organizers_title' => ['nullable', 'string', 'max:255'],
            'footer_services_title' => ['nullable', 'string', 'max:255'],
            'footer_partners_title' => ['nullable', 'string', 'max:255'],
            'footer_apps_title' => ['nullable', 'string', 'max:255'],
            'payment_section_title' => ['nullable', 'string', 'max:255'],
            'customer_service_title' => ['nullable', 'string', 'max:255'],
            'support_section_title' => ['nullable', 'string', 'max:255'],
            'support_section_subtitle' => ['nullable', 'string', 'max:255'],
            'support_button_text' => ['nullable', 'string', 'max:255'],
            'footer_support_button_text' => ['nullable', 'string', 'max:255'],
            'organizer_cta_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        foreach (['payment_stripe_enabled', 'payment_paypal_enabled', 'payment_mada_enabled'] as $key) {
            $validated[$key] = $this->boolean($key);
        }

        return $validated;
    }
}
