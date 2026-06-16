<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->text('description')->nullable()->after('name');
            $table->string('image_url')->nullable()->after('icon');
            $table->unsignedInteger('sort_order')->default(0)->after('image_url');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->string('meta_title')->nullable()->after('is_active');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->index(['parent_id', 'is_active', 'sort_order']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('slug');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('homepage_items', function (Blueprint $table) {
            $table->string('ad_type')->nullable()->after('section_key');
            $table->boolean('open_in_new_tab')->default(false)->after('cta_url');
            $table->timestamp('starts_at')->nullable()->after('sort_order');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->index(['section_key', 'ad_type', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('address')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['city_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');

        Schema::table('homepage_items', function (Blueprint $table) {
            $table->dropIndex(['section_key', 'ad_type', 'is_active']);
            $table->dropIndex(['starts_at', 'ends_at']);
            $table->dropColumn(['ad_type', 'open_in_new_tab', 'starts_at', 'ends_at']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'sort_order']);
            $table->dropColumn(['sort_order', 'is_active']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'is_active', 'sort_order']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'description',
                'image_url',
                'sort_order',
                'is_active',
                'meta_title',
                'meta_description',
            ]);
        });
    }
};
