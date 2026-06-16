<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('logo_url')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('logo_url');
            $table->string('whatsapp')->nullable()->after('bio');
            $table->string('instagram_url')->nullable()->after('whatsapp');
            $table->string('x_url')->nullable()->after('instagram_url');
            $table->string('website_url')->nullable()->after('x_url');
            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn([
                'is_active',
                'logo_url',
                'bio',
                'whatsapp',
                'instagram_url',
                'x_url',
                'website_url',
            ]);
        });
    }
};
