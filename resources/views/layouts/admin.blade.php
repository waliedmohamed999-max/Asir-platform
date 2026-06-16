<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ('لوحة إدارة ' . ($appSettings['platform_name'] ?? 'منصة عسير')) }}</title>
    <meta name="description" content="لوحة إدارة {{ $appSettings['platform_name'] ?? 'منصة عسير' }}">
    <link rel="icon" type="image/png" href="{{ $appSettings['platform_favicon_url'] ?? asset('branding/aseer-logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --admin-bg: #f5f7fb;
            --admin-panel: #ffffff;
            --admin-border: #e6ebf3;
            --admin-ink: #0f172a;
            --admin-muted: #64748b;
            --admin-soft: #f8fafc;
            --admin-purple: #6d28d9;
            --admin-pink: #e8356d;
        }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; overflow-x: hidden; }
        body {
            margin: 0;
            font-family: 'Cairo', Tahoma, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at 8% 0%, rgba(124, 58, 237, .08), transparent 32rem),
                linear-gradient(180deg, #fbfcff 0%, var(--admin-bg) 100%);
            color: var(--admin-ink);
        }
        .admin-shell { width: 100%; min-height: 100vh; }
        .admin-layout {
            min-height: 100vh;
            padding: 16px;
            padding-inline-start: 314px;
        }
        .admin-main { min-width: 0; max-width: 1480px; width: 100%; margin-inline: auto; }
        .admin-main > section,
        .admin-main > div {
            min-width: 0;
        }
        .admin-card {
            background: rgba(255, 255, 255, .92);
            border: 1px solid var(--admin-border);
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .06);
        }
        .admin-card.interactive-card {
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .admin-card.interactive-card:hover {
            transform: translateY(-2px);
            border-color: rgba(109, 40, 217, .18);
            box-shadow: 0 22px 52px rgba(15, 23, 42, .085);
        }
        .admin-page-head {
            position: relative;
            overflow: hidden;
            padding: 22px;
            background:
                radial-gradient(circle at 12% 18%, rgba(124, 58, 237, .12), transparent 24rem),
                linear-gradient(135deg, rgba(255,255,255,.98), rgba(248,250,252,.95));
        }
        .admin-page-head::after {
            content: '';
            position: absolute;
            inset-block: 20px;
            inset-inline-start: 0;
            width: 4px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--admin-purple), var(--admin-pink));
        }
        .admin-page-kicker {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: 999px;
            background: #f4f1ff;
            color: var(--admin-purple);
            padding: .42rem .75rem;
            font-size: 12px;
            font-weight: 900;
        }
        .admin-page-title {
            margin-top: 10px;
            font-size: clamp(1.35rem, 2vw, 1.8rem);
            line-height: 1.25;
            font-weight: 900;
            color: #0f172a;
        }
        .admin-page-description {
            margin-top: 7px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.8;
        }
        .admin-primary-btn,
        .admin-secondary-btn,
        .admin-danger-btn,
        .admin-success-btn {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: .65rem 1rem;
            font-size: 13px;
            font-weight: 900;
            transition: .18s ease;
            white-space: nowrap;
        }
        .admin-primary-btn {
            color: #fff;
            background: linear-gradient(135deg, #111827, #6d28d9);
            box-shadow: 0 14px 26px rgba(109,40,217,.18);
        }
        .admin-secondary-btn {
            color: #334155;
            background: #fff;
            border: 1px solid #e2e8f0;
        }
        .admin-success-btn {
            color: #047857;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
        }
        .admin-danger-btn {
            color: #fff;
            background: #e11d48;
            box-shadow: 0 14px 24px rgba(225,29,72,.16);
        }
        .admin-primary-btn:hover,
        .admin-secondary-btn:hover,
        .admin-danger-btn:hover,
        .admin-success-btn:hover {
            transform: translateY(-1px);
        }
        .admin-filter-panel {
            background: rgba(255,255,255,.94);
            border: 1px solid #e8edf5;
            border-radius: 22px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .045);
            padding: 18px;
        }
        .admin-filter-panel input,
        .admin-filter-panel select {
            min-height: 46px;
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            background: #fff;
            padding: .75rem .95rem;
            font-weight: 700;
            color: #0f172a;
            outline: none;
        }
        .admin-filter-panel input:focus,
        .admin-filter-panel select:focus {
            border-color: rgba(109,40,217,.42);
            box-shadow: 0 0 0 4px rgba(109,40,217,.08);
        }
        .admin-list-meta {
            display: grid;
            gap: 8px;
            color: #475569;
            font-size: 13px;
        }
        .admin-form {
            width: 100%;
            max-width: 1480px;
        }
        .admin-form-card {
            padding: 22px;
        }
        .admin-form-card h2,
        .admin-section-title {
            font-size: 1.25rem;
            line-height: 1.35;
            font-weight: 900;
            color: #0f172a;
        }
        .admin-form-card h2::after,
        .admin-section-title::after {
            content: '';
            display: block;
            width: 44px;
            height: 3px;
            margin-top: 10px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--admin-purple), var(--admin-pink));
        }
        .admin-form-card label:not(.media-uploader) {
            color: #334155;
        }
        .admin-form-card input:not([type='checkbox']):not([type='radio']):not([type='file']):not([type='color']),
        .admin-form-card select,
        .admin-form-card textarea {
            min-height: 46px;
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            background: #fff;
            padding: .75rem .95rem;
            color: #0f172a;
            font-weight: 700;
            outline: none;
            transition: .18s ease;
        }
        .admin-form-card textarea {
            line-height: 1.75;
        }
        .admin-form-card input:focus,
        .admin-form-card select:focus,
        .admin-form-card textarea:focus {
            border-color: rgba(109,40,217,.42);
            box-shadow: 0 0 0 4px rgba(109,40,217,.08);
        }
        .admin-checkbox-tile {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
            transition: .18s ease;
        }
        .admin-checkbox-tile:hover {
            border-color: rgba(109,40,217,.26);
            background: #faf7ff;
        }
        .admin-sticky-actions {
            position: sticky;
            bottom: 16px;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid #e5eaf2;
            border-radius: 22px;
            background: rgba(255,255,255,.9);
            padding: 14px;
            box-shadow: 0 18px 44px rgba(15,23,42,.10);
            backdrop-filter: blur(14px);
        }
        .sidebar-panel {
            position: fixed;
            inset-block-start: 16px;
            inset-inline-start: 16px;
            z-index: 40;
            width: 280px;
            height: calc(100vh - 32px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: rgba(255,255,255,.96);
            border: 1px solid var(--admin-border);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .08);
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 64px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(109,40,217,.08), rgba(232,53,109,.08));
            border: 1px solid rgba(109,40,217,.12);
        }
        .sidebar-user {
            border-radius: 18px;
            background: linear-gradient(135deg, #111827, #25124d 65%, #6d28d9);
            color: #fff;
            padding: 14px;
        }
        .sidebar-scroll {
            overflow-y: auto;
            padding-inline-end: 2px;
        }
        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #d8ddea; border-radius: 999px; }
        .sidebar-section-title {
            margin: 14px 8px 7px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0;
        }
        .sidebar-search {
            position: relative;
            margin-top: 12px;
        }
        .sidebar-search input {
            width: 100%;
            height: 42px;
            border: 1px solid #e6ebf3;
            border-radius: 14px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 12px;
            font-weight: 800;
            outline: none;
            padding: 0 38px 0 12px;
            transition: .18s ease;
        }
        .sidebar-search input:focus {
            border-color: rgba(109,40,217,.35);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(109,40,217,.08);
        }
        .sidebar-search svg {
            position: absolute;
            inset-inline-start: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: #94a3b8;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            min-height: 43px;
            border-radius: 13px;
            padding: .68rem .78rem;
            color: #334155;
            font-weight: 800;
            font-size: 13px;
            line-height: 1.2;
            transition: .18s ease;
            white-space: nowrap;
        }
        .sidebar-link-icon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #64748b;
            flex: 0 0 auto;
            transition: .18s ease;
        }
        .sidebar-link-icon svg {
            width: 16px;
            height: 16px;
        }
        .sidebar-link:hover { background: #f4f1ff; color: var(--admin-purple); }
        .sidebar-link:hover .sidebar-link-icon {
            background: #ede9fe;
            color: var(--admin-purple);
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, var(--admin-purple), #8b5cf6);
            color: #fff;
            box-shadow: 0 14px 30px rgba(109, 40, 217, .22);
        }
        .sidebar-link.active .sidebar-link-icon {
            background: rgba(255,255,255,.18);
            color: #fff;
        }
        .sidebar-link-label { overflow: hidden; text-overflow: ellipsis; }
        .sidebar-empty {
            display: none;
            margin: 14px 8px;
            border: 1px dashed #d9e1ec;
            border-radius: 14px;
            padding: 12px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
        }
        .stat-tile { background: linear-gradient(180deg, #ffffff, #fbfcff); border: 1px solid #e7ecf4; border-radius: 22px; box-shadow: 0 14px 35px rgba(15, 23, 42, .05); overflow: hidden; }
        .metric-accent { background: radial-gradient(circle at top right, rgba(139, 92, 246, .16), transparent 40%), #fff; }
        .admin-card-muted { background: #f8fafc; border: 1px solid #edf2f7; border-radius: 18px; }
        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
        .admin-table thead th { color: #64748b; font-size: 12px; font-weight: 800; padding: 0 14px 6px; text-align: right; }
        .admin-table tbody tr { background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .03); }
        .admin-table tbody td { padding: 14px; border-top: 1px solid #eef2f7; border-bottom: 1px solid #eef2f7; }
        .admin-table tbody td:first-child { border-right: 1px solid #eef2f7; border-top-right-radius: 18px; border-bottom-right-radius: 18px; }
        .admin-table tbody td:last-child { border-left: 1px solid #eef2f7; border-top-left-radius: 18px; border-bottom-left-radius: 18px; }
        .badge-pill { display: inline-flex; align-items: center; gap: .4rem; border-radius: 999px; padding: .45rem .8rem; font-size: 12px; font-weight: 800; }
        .badge-neutral { background: #f1f5f9; color: #475569; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-pill-muted { background: #f1f5f9; color: #475569; }
        .badge-pill-success { background: #dcfce7; color: #15803d; }
        .badge-pill-warning { background: #fef3c7; color: #b45309; }
        .badge-pill-danger { background: #fee2e2; color: #b91c1c; }
        .badge-pill-info { background: #e0f2fe; color: #0369a1; }
        .page-actions a, .page-actions button { transition: .2s ease; }
        .page-actions a:hover, .page-actions button:hover { transform: translateY(-1px); }
        .admin-card input:not([type='checkbox']):not([type='radio']):not([type='color']),
        .admin-card select,
        .admin-card textarea {
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            padding: .85rem 1rem;
            width: 100%;
            background: #fff;
            transition: .2s ease;
        }
        .admin-card input:focus,
        .admin-card select:focus,
        .admin-card textarea:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, .08);
        }
        .admin-empty {
            border: 1px dashed #d8e1ec;
            border-radius: 24px;
            background: linear-gradient(180deg, #ffffff, #fafcff);
            padding: 2.25rem 1.5rem;
            text-align: center;
            color: #64748b;
        }
        .topbar-chip {
            border: 1px solid #e5eaf2;
            border-radius: 999px;
            padding: .58rem .85rem;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            background: #fff;
        }
        .admin-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border-radius: 999px;
            padding: .55rem .9rem;
            font-size: 12px;
            font-weight: 900;
            transition: .18s ease;
            white-space: nowrap;
        }
        .admin-action-btn:hover {
            transform: translateY(-1px);
        }
        .top-bar {
            position: sticky;
            top: 16px;
            z-index: 30;
            background: rgba(255,255,255,.92);
            border: 1px solid var(--admin-border);
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .055);
            backdrop-filter: blur(14px);
        }
        nav[role='navigation'] > div:first-child { display: none; }
        nav[role='navigation'] span[aria-current='page'],
        nav[role='navigation'] a {
            border-radius: 12px !important;
        }
        .media-uploader {
            border: 1px dashed #cbd5e1;
            border-radius: 24px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            padding: 1.25rem;
            display: block;
            cursor: pointer;
            transition: .2s ease;
        }
        .media-uploader:hover,
        .media-uploader.is-dragover {
            border-color: #8b5cf6;
            background: linear-gradient(180deg, #faf7ff, #f6f5ff);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, .08);
        }
        .media-preview-grid {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        }
        .media-preview-tile {
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        .media-preview-tile img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            display: block;
        }
        .media-preview-tile span {
            display: block;
            padding: .5rem .7rem;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .app-stories-page {
            font-family: 'Cairo', Tahoma, 'Segoe UI', sans-serif;
        }
        .font-outfit,
        .badge-pill {
            font-family: 'Outfit', 'Cairo', sans-serif;
        }
        @media (max-width: 1023px) {
            .admin-layout { padding: 10px; }
            .sidebar-panel { position: static; width: 100%; height: auto; }
            .top-bar { position: static; }
            .sidebar-scroll { max-height: 300px; }
            .sidebar-link { white-space: normal; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <div class="admin-layout">
            <aside class="sidebar-panel min-w-0 p-3">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                    <img src="{{ $appSettings['platform_logo_url'] ?? asset('branding/aseer-logo.png') }}" alt="{{ $appSettings['platform_name'] ?? 'منصة عسير' }}" class="h-9 w-auto object-contain">
                </a>

                <div class="sidebar-user mt-3">
                    <p class="text-xs text-slate-300">لوحة الإدارة</p>
                    <p class="mt-1 text-sm font-black">{{ auth()->user()?->name }}</p>
                    <p class="mt-1 text-xs text-slate-300">{{ auth()->user()?->email }}</p>
                </div>

                <label class="sidebar-search">
                    <input type="search" data-sidebar-search placeholder="بحث سريع في اللوحة">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.35-4.35M10.8 18.1a7.3 7.3 0 1 1 0-14.6 7.3 7.3 0 0 1 0 14.6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </label>

                <nav class="sidebar-scroll mt-3 flex-1 space-y-1">
                    <p class="sidebar-section-title">نظرة عامة</p>
                    <a href="{{ route('admin.dashboard') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                        <span class="sidebar-link-label">الرئيسية</span>
                    </a>

                    <p class="sidebar-section-title">العمليات</p>
                    <a href="{{ route('admin.events.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3M17 3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v11.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                        <span class="sidebar-link-label">إدارة الفعاليات</span>
                    </a>
                    <a href="{{ route('admin.bookings.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7.5h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4v-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 8v8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                        <span class="sidebar-link-label">الحجوزات والطلبات</span>
                    </a>
                    <a href="{{ route('admin.payments.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16v10H4V7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M4 10h16M8 15h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                        <span class="sidebar-link-label">المدفوعات</span>
                    </a>
                    <a href="{{ route('admin.resale-listings.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.resale-listings.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7 7h10l2 3-7 9-7-9 2-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 11h6M13 4l3 3M11 4 8 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                        <span class="sidebar-link-label">إعادة البيع</span>
                    </a>

                    <p class="sidebar-section-title">المحتوى والتطبيق</p>
                    <a href="{{ route('admin.support-conversations.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.support-conversations.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M5 6.5A3.5 3.5 0 0 1 8.5 3h7A3.5 3.5 0 0 1 19 6.5v5A3.5 3.5 0 0 1 15.5 15H11l-4.5 4v-4A3.5 3.5 0 0 1 3 11.5v-5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 8h8M8 11h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">محادثات التطبيق</span></a>
                    <a href="{{ route('admin.app-stories.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.app-stories.*') || (request()->routeIs('admin.homepage-items.*') && request('section') === 'app_stories') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M8 5.5A4.5 4.5 0 0 1 12.5 1h.5a8 8 0 1 1-8 8v-.5A3 3 0 0 1 8 5.5Z" stroke="currentColor" stroke-width="1.7"/><path d="M14 7h4M16 5v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">استوري التطبيق</span></a>
                    <a href="{{ route('admin.homepage-items.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.homepage-items.*') && request('section') !== 'app_stories' ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.7"/><path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">الإعلانات والبنرات</span></a>
                    <a href="{{ route('admin.categories.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 5h7v7H4V5ZM13 5h7v7h-7V5ZM4 14h7v5H4v-5ZM13 14h7v5h-7v-5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span><span class="sidebar-link-label">التصنيفات</span></a>
                    <a href="{{ route('admin.cities.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z" stroke="currentColor" stroke-width="1.7"/><path d="M12 12.2a2.2 2.2 0 1 0 0-4.4 2.2 2.2 0 0 0 0 4.4Z" stroke="currentColor" stroke-width="1.7"/></svg></span><span class="sidebar-link-label">المدن</span></a>
                    <a href="{{ route('admin.venues.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.venues.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 20h16M6 20V9l6-4 6 4v11M9 20v-6h6v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="sidebar-link-label">المواقع والقاعات</span></a>
                    <a href="{{ route('admin.pages.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.pages.*') && !request('scope') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3h7l4 4v14H7V3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 3v5h5M9 12h6M9 16h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">الصفحات الثابتة</span></a>
                    <a href="{{ route('admin.pages.index', ['scope' => 'footer']) }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.pages.*') && request('scope') === 'footer' ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M5 5h14v14H5V5Z" stroke="currentColor" stroke-width="1.7"/><path d="M5 15h14M9 9h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">روابط الفوتر</span></a>
                    <a href="{{ route('admin.faqs.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 18h.01M9.7 9a2.4 2.4 0 1 1 3.25 2.25c-.8.35-.95.82-.95 1.75" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.7"/></svg></span><span class="sidebar-link-label">الأسئلة الشائعة</span></a>
                    <a href="{{ route('admin.coupons.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 8h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V8Z" stroke="currentColor" stroke-width="1.7"/><path d="M9 10h.01M15 16h.01M15.5 10.5l-7 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">الكوبونات</span></a>

                    <p class="sidebar-section-title">الإدارة</p>
                    <a href="{{ route('admin.users.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M16 19c0-2.2-1.8-4-4-4s-4 1.8-4 4M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM18 11a3 3 0 0 1 2 2.8V19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">المستخدمون</span></a>
                    <a href="{{ route('admin.organizers.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.organizers.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 20V8l8-4 8 4v12M8 20v-7h8v7" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M10 9h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">المنظمون</span></a>
                    <a href="{{ route('admin.reports.sales') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M5 19V5M5 19h14M9 16v-5M13 16V8M17 16v-7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span class="sidebar-link-label">التقارير</span></a>
                    <a href="{{ route('admin.activity-logs.index') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 8v5l3 2M21 12a9 9 0 1 1-3-6.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M21 4v5h-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="sidebar-link-label">سجل النشاطات</span></a>
                    <a href="{{ route('admin.settings.edit') }}" data-sidebar-link class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="1.7"/><path d="M19 12a7.2 7.2 0 0 0-.08-1.05l2.02-1.58-2-3.46-2.38.96a7 7 0 0 0-1.82-1.05L14.4 3h-4.8l-.34 2.82c-.66.25-1.27.6-1.82 1.05l-2.38-.96-2 3.46 2.02 1.58A7.2 7.2 0 0 0 5 12c0 .36.03.71.08 1.05l-2.02 1.58 2 3.46 2.38-.96c.55.45 1.16.8 1.82 1.05l.34 2.82h4.8l.34-2.82c.66-.25 1.27-.6 1.82-1.05l2.38.96 2-3.46-2.02-1.58c.05-.34.08-.69.08-1.05Z" stroke="currentColor" stroke-width="1.25" stroke-linejoin="round"/></svg></span><span class="sidebar-link-label">الإعدادات العامة</span></a>
                    <div class="sidebar-empty" data-sidebar-empty>لا توجد نتائج مطابقة</div>
                </nav>

                <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-black text-slate-600">حالة النظام</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-black text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            مستقر
                        </span>
                    </div>
                </div>
            </aside>

            <main class="admin-main">
                <header class="top-bar mb-4 flex flex-col gap-3 p-3.5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-lg font-black text-slate-950 lg:text-xl">{{ $pageTitle ?? 'لوحة الإدارة' }}</h1>
                        @isset($pageDescription)
                            <p class="mt-1 text-xs text-slate-500 lg:text-sm">{{ $pageDescription }}</p>
                        @endisset
                    </div>
                    <div class="page-actions flex flex-wrap items-center gap-2.5">
                        <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[#E8356D] to-[#7C3AED]"></div>
                            <div class="leading-tight">
                                <p class="text-xs font-black text-slate-900">{{ auth()->user()?->name }}</p>
                                <p class="text-[10px] font-bold text-slate-500">{{ auth()->user()?->role }}</p>
                            </div>
                        </div>
                        <span class="topbar-chip">اليوم: {{ now()->translatedFormat('d M Y') }}</span>
                        <a href="{{ route('home') }}" class="admin-action-btn border border-slate-200 text-slate-700 hover:border-violet-300 hover:text-violet-700">عرض المنصة</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="admin-action-btn border border-rose-200 bg-rose-50 text-rose-700">تسجيل الخروج</button>
                        </form>
                    </div>
                </header>

                @if(session('success'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                        <p class="font-black">تعذر حفظ البيانات. راجع الحقول وأعد المحاولة.</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarSearch = document.querySelector('[data-sidebar-search]');
            const sidebarLinks = Array.from(document.querySelectorAll('[data-sidebar-link]'));
            const sidebarEmpty = document.querySelector('[data-sidebar-empty]');

            if (sidebarSearch && sidebarLinks.length) {
                const sectionTitles = Array.from(document.querySelectorAll('.sidebar-section-title'));

                const syncSidebarSearch = function () {
                    const query = sidebarSearch.value.trim().toLowerCase();
                    let visibleCount = 0;

                    sidebarLinks.forEach(function (link) {
                        const label = link.textContent.trim().toLowerCase();
                        const isVisible = !query || label.includes(query);
                        link.style.display = isVisible ? '' : 'none';
                        if (isVisible) {
                            visibleCount += 1;
                        }
                    });

                    sectionTitles.forEach(function (title) {
                        let node = title.nextElementSibling;
                        let hasVisibleLink = false;

                        while (node && !node.classList.contains('sidebar-section-title')) {
                            if (node.matches && node.matches('[data-sidebar-link]') && node.style.display !== 'none') {
                                hasVisibleLink = true;
                                break;
                            }
                            node = node.nextElementSibling;
                        }

                        title.style.display = hasVisibleLink ? '' : 'none';
                    });

                    if (sidebarEmpty) {
                        sidebarEmpty.style.display = visibleCount ? 'none' : 'block';
                    }
                };

                sidebarSearch.addEventListener('input', syncSidebarSearch);
                syncSidebarSearch();
            }

            document.querySelectorAll('[data-media-upload]').forEach(function (wrapper) {
                const input = wrapper.querySelector('input[type="file"]');
                const text = wrapper.querySelector('[data-media-text]');
                const preview = document.getElementById(wrapper.dataset.previewTarget || '');

                if (!input) {
                    return;
                }

                const renderPreview = function () {
                    if (text) {
                        if (!input.files || input.files.length === 0) {
                            text.textContent = wrapper.dataset.placeholder || 'اسحب الملفات هنا أو اضغط للاختيار';
                        } else if (input.files.length === 1) {
                            text.textContent = input.files[0].name;
                        } else {
                            text.textContent = `تم اختيار ${input.files.length} ملفات`;
                        }
                    }

                    if (!preview) {
                        return;
                    }

                    preview.innerHTML = '';

                    Array.from(input.files || []).forEach(function (file) {
                        if (!file.type.startsWith('image/')) {
                            return;
                        }

                        const tile = document.createElement('div');
                        tile.className = 'media-preview-tile';

                        const image = document.createElement('img');
                        image.src = URL.createObjectURL(file);
                        image.onload = function () { URL.revokeObjectURL(image.src); };

                        const caption = document.createElement('span');
                        caption.textContent = file.name;

                        tile.appendChild(image);
                        tile.appendChild(caption);
                        preview.appendChild(tile);
                    });
                };

                input.addEventListener('change', renderPreview);
                wrapper.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    wrapper.classList.add('is-dragover');
                });
                ['dragleave', 'dragend', 'drop'].forEach(function (eventName) {
                    wrapper.addEventListener(eventName, function () {
                        wrapper.classList.remove('is-dragover');
                    });
                });

                renderPreview();
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
