<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('venue_name_en')->nullable()->after('venue_name');
            $table->string('banner_image_url')->nullable()->after('map_url');
            $table->string('video_url')->nullable()->after('banner_image_url');
            $table->decimal('location_lat', 10, 7)->nullable()->after('video_url');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            $table->boolean('show_on_homepage')->default(false)->after('is_featured');
            $table->unsignedInteger('display_order')->default(0)->after('show_on_homepage');
            $table->text('refund_policy')->nullable()->after('terms');
            $table->json('faqs')->nullable()->after('refund_policy');
            $table->string('meta_title')->nullable()->after('faqs');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->unsignedInteger('capacity')->nullable()->after('meta_description');
            $table->boolean('is_active')->default(true)->after('capacity');
            $table->index(['status', 'is_featured', 'show_on_homepage']);
            $table->index(['city_id', 'category_id', 'start_date']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('price_before_discount', 10, 2)->nullable()->after('price');
            $table->text('description')->nullable()->after('quantity');
            $table->json('features')->nullable()->after('description');
            $table->unsignedInteger('purchase_limit_per_user')->nullable()->after('features');
            $table->string('label_color', 20)->nullable()->after('purchase_limit_per_user');
            $table->unsignedInteger('sort_order')->default(0)->after('label_color');
            $table->boolean('uses_qr')->default(true)->after('sort_order');
            $table->boolean('is_hidden')->default(false)->after('uses_qr');
            $table->string('status')->default('active')->after('is_hidden');
            $table->index(['event_id', 'is_active', 'is_hidden']);
            $table->index(['event_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'is_active', 'is_hidden']);
            $table->dropIndex(['event_id', 'status', 'sort_order']);
            $table->dropColumn([
                'price_before_discount',
                'description',
                'features',
                'purchase_limit_per_user',
                'label_color',
                'sort_order',
                'uses_qr',
                'is_hidden',
                'status',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_featured', 'show_on_homepage']);
            $table->dropIndex(['city_id', 'category_id', 'start_date']);
            $table->dropColumn([
                'title_en',
                'venue_name_en',
                'banner_image_url',
                'video_url',
                'location_lat',
                'location_lng',
                'show_on_homepage',
                'display_order',
                'refund_policy',
                'faqs',
                'meta_title',
                'meta_description',
                'capacity',
                'is_active',
            ]);
        });
    }
};
