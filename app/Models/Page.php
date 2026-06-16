<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    public const FOOTER_GROUPS = [
        'categories' => 'الفئات',
        'about' => 'من نحن',
        'organizers' => 'للمنظمين',
        'services' => 'الخدمات',
        'partners' => 'للشركاء',
        'apps' => 'تحميل التطبيق',
    ];

    protected $fillable = [
        'title',
        'slug',
        'footer_group',
        'footer_label',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'sort_order',
        'show_in_footer',
        'target_url',
        'open_in_new_tab',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_footer' => 'boolean',
            'open_in_new_tab' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function publicUrl(): string
    {
        return $this->target_url ?: route('pages.show', $this);
    }
}
