<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('footer_group')->nullable()->after('slug');
            $table->string('footer_label')->nullable()->after('footer_group');
            $table->boolean('show_in_footer')->default(false)->after('sort_order');
            $table->string('target_url')->nullable()->after('show_in_footer');
            $table->boolean('open_in_new_tab')->default(false)->after('target_url');

            $table->index(['show_in_footer', 'footer_group', 'sort_order'], 'pages_footer_index');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex('pages_footer_index');
            $table->dropColumn([
                'footer_group',
                'footer_label',
                'show_in_footer',
                'target_url',
                'open_in_new_tab',
            ]);
        });
    }
};
