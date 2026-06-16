<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_items', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->string('hero_image_url')->nullable()->after('image_url');
            $table->json('gallery')->nullable()->after('hero_image_url');
            $table->string('venue_name')->nullable()->after('event_id');
            $table->string('date_label')->nullable()->after('venue_name');
            $table->longText('description')->nullable()->after('date_label');
            $table->text('includes')->nullable()->after('description');
            $table->text('terms')->nullable()->after('includes');
            $table->text('schedule')->nullable()->after('terms');
            $table->text('directions')->nullable()->after('schedule');
            $table->string('location_title')->nullable()->after('directions');
            $table->string('location_code')->nullable()->after('location_title');
            $table->string('map_url')->nullable()->after('location_code');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_items', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'hero_image_url',
                'gallery',
                'venue_name',
                'date_label',
                'description',
                'includes',
                'terms',
                'schedule',
                'directions',
                'location_title',
                'location_code',
                'map_url',
            ]);
        });
    }
};
