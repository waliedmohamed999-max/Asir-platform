<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Resource Route Permissions
    |--------------------------------------------------------------------------
    |
    | Slug format:
    | filament.admin.resources.{slug}.*
    |
    */
    'resource_permissions' => [
        'attachments' => ['permission' => 'manage attachments', 'label' => 'المرفقات', 'path' => '/admin/attachments'],
        'blog-categories' => ['permission' => 'manage blog categories', 'label' => 'تصنيفات المدونة', 'path' => '/admin/blog-categories'],
        'blog-posts' => ['permission' => 'manage blog posts', 'label' => 'مقالات المدونة', 'path' => '/admin/blog-posts'],
        'blog-tags' => ['permission' => 'manage blog tags', 'label' => 'وسوم المدونة', 'path' => '/admin/blog-tags'],
        'contact-messages' => ['permission' => 'manage contact messages', 'label' => 'رسائل التواصل', 'path' => '/admin/contact-messages'],
        'course-categories' => ['permission' => 'manage course categories', 'label' => 'تصنيفات الكورسات', 'path' => '/admin/course-categories'],
        'courses' => ['permission' => 'manage courses', 'label' => 'الكورسات', 'path' => '/admin/courses'],
        'lessons' => ['permission' => 'manage lessons', 'label' => 'الدروس', 'path' => '/admin/lessons'],
        'modules' => ['permission' => 'manage modules', 'label' => 'الوحدات', 'path' => '/admin/modules'],
        'news-posts' => ['permission' => 'manage news', 'label' => 'الأخبار', 'path' => '/admin/news-posts'],
        'orders' => ['permission' => 'manage orders', 'label' => 'الطلبات والمدفوعات', 'path' => '/admin/orders'],
        'pages' => ['permission' => 'manage pages', 'label' => 'الصفحات', 'path' => '/admin/pages'],
        'permissions' => ['permission' => 'manage permissions', 'label' => 'الصلاحيات', 'path' => '/admin/permissions'],
        'roles' => ['permission' => 'manage roles', 'label' => 'الأدوار', 'path' => '/admin/roles'],
        'services' => ['permission' => 'manage services', 'label' => 'الخدمات', 'path' => '/admin/services'],
        'site-settings' => ['permission' => 'manage site settings', 'label' => 'إعدادات النظام', 'path' => '/admin/site-settings'],
        'tracks' => ['permission' => 'manage tracks', 'label' => 'المسارات', 'path' => '/admin/tracks'],
        'users' => ['permission' => 'manage users', 'label' => 'المستخدمون', 'path' => '/admin/users'],
        'video-assets' => ['permission' => 'manage video assets', 'label' => 'أصول الفيديو', 'path' => '/admin/video-assets'],
    ],
];

