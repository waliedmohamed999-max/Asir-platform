@php
    use App\Models\HomepageItem;
    use Illuminate\Support\Str;

    $storyStatsQuery = HomepageItem::query()->where('section_key', 'app_stories');
    $totalStories = (clone $storyStatsQuery)->count();
    $activeStories = (clone $storyStatsQuery)->where('is_active', true)->count();
    $inactiveStories = max(0, $totalStories - $activeStories);
    $endingThisMonth = (clone $storyStatsQuery)
        ->whereNotNull('ends_at')
        ->whereBetween('ends_at', [now()->startOfMonth(), now()->endOfMonth()])
        ->count();
    $storyItems = $items->getCollection();
@endphp

@once
    @push('styles')
        <style>
            .app-stories-page {
                --story-bg: #0F0F14;
                --story-surface: #1A1A24;
                --story-deeper: #0D0D12;
                --story-pink: #E8356D;
                --story-purple: #7C3AED;
                --story-text: #F0F0F5;
                --story-muted: #8B8B9E;
            }

            .app-stories-page .page-header {
                background:
                    radial-gradient(circle at 92% 20%, rgba(232, 53, 109, .22), transparent 34%),
                    linear-gradient(135deg, rgba(26, 26, 36, .98), rgba(15, 15, 20, .98));
                border: 1px solid rgba(255,255,255,.06);
                border-radius: 16px;
                box-shadow: 0 0 34px rgba(232, 53, 109, .08);
            }

            .app-stories-page .story-card {
                animation: fadeInUp .4s ease forwards;
                opacity: 0;
            }

            .app-stories-page .story-card:nth-child(1) { animation-delay: .05s; }
            .app-stories-page .story-card:nth-child(2) { animation-delay: .10s; }
            .app-stories-page .story-card:nth-child(3) { animation-delay: .15s; }
            .app-stories-page .story-card:nth-child(4) { animation-delay: .20s; }
            .app-stories-page .story-card:nth-child(5) { animation-delay: .25s; }
            .app-stories-page .story-card:nth-child(6) { animation-delay: .30s; }

            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .app-stories-page .story-card:hover {
                transform: translateY(-2px);
            }

            .app-stories-page .status-active::before {
                content: '';
                display: inline-block;
                width: 6px;
                height: 6px;
                background: #10B981;
                border-radius: 999px;
                margin-left: 6px;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: .45; transform: scale(1.6); }
            }
        </style>
    @endpush
@endonce

<section class="app-stories-page space-y-5" dir="rtl">
    <div class="page-header overflow-hidden p-6 md:p-7">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#E8356D] to-[#7C3AED] shadow-[0_0_24px_rgba(232,53,109,.24)]">
                    <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 5.75A2.75 2.75 0 0 1 7.75 3h8.5A2.75 2.75 0 0 1 19 5.75v12.5A2.75 2.75 0 0 1 16.25 21h-8.5A2.75 2.75 0 0 1 5 18.25V5.75Z" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M9 7.5h6M9 11.5h6M9 15.5h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-cairo text-3xl font-black leading-tight text-[#F0F0F5] md:text-4xl">استوري التطبيق</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-[#8B8B9E]">
                        تحكم في دوائر الاستوري التي تظهر أعلى تطبيق الموبايل، واربط كل استوري بفعالية أو تصنيف مع ترتيب ومدة عرض واضحة.
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.homepage-items.create', ['section' => 'app_stories', 'content_type' => 'story']) }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-[#E8356D] to-[#7C3AED] px-5 py-3 text-sm font-black text-white shadow-[0_0_24px_rgba(232,53,109,.22)] transition hover:scale-[1.01]">
                <span>إضافة استوري جديد</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-white/5 border-t-[#E8356D] bg-[#1A1A24] p-5">
            <p class="text-sm font-bold text-[#8B8B9E]">إجمالي الاستوريات</p>
            <p class="font-outfit mt-3 text-[32px] font-bold leading-none text-[#E8356D]">{{ $totalStories }}</p>
        </div>
        <div class="rounded-2xl border border-white/5 border-t-[#10B981] bg-[#1A1A24] p-5">
            <p class="text-sm font-bold text-[#8B8B9E]">المفعّلة</p>
            <p class="font-outfit mt-3 text-[32px] font-bold leading-none text-[#10B981]">{{ $activeStories }}</p>
        </div>
        <div class="rounded-2xl border border-white/5 border-t-[#EF4444] bg-[#1A1A24] p-5">
            <p class="text-sm font-bold text-[#8B8B9E]">المعطلة</p>
            <p class="font-outfit mt-3 text-[32px] font-bold leading-none text-[#EF4444]">{{ $inactiveStories }}</p>
        </div>
        <div class="rounded-2xl border border-white/5 border-t-[#7C3AED] bg-[#1A1A24] p-5">
            <p class="text-sm font-bold text-[#8B8B9E]">تنتهي هذا الشهر</p>
            <p class="font-outfit mt-3 text-[32px] font-bold leading-none text-[#7C3AED]">{{ $endingThisMonth }}</p>
        </div>
    </div>

    <div class="filters-bar rounded-2xl border border-white/5 bg-[#1A1A24] p-4">
        <div class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_auto_auto] lg:items-center">
            <label class="relative block">
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[#8B8B9E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.35-4.35M10.8 18.1a7.3 7.3 0 1 1 0-14.6 7.3 7.3 0 0 1 0 14.6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <input data-story-search type="search" placeholder="ابحث باسم الاستوري أو التصنيف"
                       class="h-12 w-full rounded-xl border border-white/10 bg-[#0F0F14] pr-10 text-sm text-[#F0F0F5] outline-none transition placeholder:text-[#8B8B9E] focus:border-[#E8356D]/50">
            </label>

            <div class="flex flex-wrap gap-2" data-story-filters>
                <button type="button" data-filter="all" class="story-filter rounded-full bg-[#E8356D] px-4 py-2 text-xs font-black text-white">الكل</button>
                <button type="button" data-filter="active" class="story-filter rounded-full border border-white/10 px-4 py-2 text-xs font-bold text-[#F0F0F5]">مفعّل</button>
                <button type="button" data-filter="inactive" class="story-filter rounded-full border border-white/10 px-4 py-2 text-xs font-bold text-[#F0F0F5]">معطّل</button>
                <button type="button" data-filter="ending" class="story-filter rounded-full border border-white/10 px-4 py-2 text-xs font-bold text-[#F0F0F5]">ينتهي قريباً</button>
            </div>

            <select data-story-sort class="h-12 rounded-xl border border-white/10 bg-[#0F0F14] px-4 text-sm text-[#F0F0F5] outline-none">
                <option value="order">ترتيب العرض</option>
                <option value="newest">الأحدث</option>
                <option value="ending">الأقرب انتهاءً</option>
                <option value="title">العنوان</option>
            </select>
        </div>
    </div>

    <div data-story-grid class="grid gap-5 md:grid-cols-2">
        @forelse($storyItems as $story)
            @php
                $image = $story->image_url ?: $story->hero_image_url ?: asset('branding/aseer-logo.png');
                $startsAt = $story->starts_at?->translatedFormat('d M Y') ?? 'فوري';
                $endsAt = $story->ends_at?->translatedFormat('d M Y') ?? 'مفتوح';
                $endingSoon = $story->ends_at && $story->ends_at->between(now(), now()->addDays(30));
                $searchText = implode(' ', [
                    $story->title,
                    $story->category?->name,
                    $story->city?->name,
                    $story->content_type,
                    $story->ad_type,
                ]);
            @endphp

            <article
                data-story-card
                data-status="{{ $story->is_active ? 'active' : 'inactive' }}"
                data-ending="{{ $endingSoon ? '1' : '0' }}"
                data-order="{{ $story->sort_order }}"
                data-title="{{ $story->title }}"
                data-created="{{ $story->created_at?->timestamp ?? 0 }}"
                data-ends="{{ $story->ends_at?->timestamp ?? 9999999999 }}"
                data-search="{{ Str::lower($searchText) }}"
                class="story-card group relative rounded-2xl border border-white/5 bg-[#1A1A24] p-5 transition-all duration-300 hover:border-[#E8356D]/40 hover:shadow-[0_0_24px_rgba(232,53,109,.10)]">

                <div class="mb-4 flex items-start justify-between gap-4">
                    <div class="flex flex-wrap gap-1.5">
                        <span class="rounded-full bg-[#7C3AED]/15 px-2 py-0.5 text-xs font-medium text-purple-300">{{ $story->content_type ?: 'story' }}</span>
                        <span class="rounded-full bg-[#7C3AED]/15 px-2 py-0.5 text-xs font-medium text-purple-300">{{ $story->ad_type ?: 'app_story' }}</span>
                        @if($story->badge)
                            <span class="rounded-full bg-[#E8356D]/15 px-2 py-0.5 text-xs font-medium text-pink-300">{{ $story->badge }}</span>
                        @endif
                    </div>

                    <div class="rounded-full p-0.5" style="background: linear-gradient(135deg, #E8356D, #7C3AED)">
                        <img src="{{ $image }}" class="block h-14 w-14 rounded-full object-cover" alt="{{ $story->title }}">
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="font-cairo mb-1 text-lg font-bold text-white">{{ $story->title }}</h3>
                    <p class="text-sm text-[#8B8B9E]">{{ $story->category?->name ?? 'بدون تصنيف' }} • {{ $story->city?->name ?? 'بدون موقع' }}</p>
                </div>

                <div class="mb-4 border-t border-white/5"></div>

                <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="mb-0.5 text-xs text-[#8B8B9E]">بداية العرض</p>
                        <p class="font-medium text-white">{{ $startsAt }}</p>
                    </div>
                    <div>
                        <p class="mb-0.5 text-xs text-[#8B8B9E]">نهاية العرض</p>
                        <p class="font-medium text-white">{{ $endsAt }}</p>
                    </div>
                </div>

                <div class="mb-4 flex items-center gap-1.5">
                    <span class="text-xs text-[#8B8B9E]">الترتيب:</span>
                    <span class="font-outfit text-sm font-bold text-[#7C3AED]">{{ $story->sort_order }}</span>
                    @if($story->event)
                        <span class="ms-auto truncate rounded-full bg-white/5 px-2 py-1 text-xs text-[#8B8B9E]">{{ $story->event->title }}</span>
                    @endif
                </div>

                <div class="mb-4 border-t border-white/5"></div>

                <div class="flex items-center gap-2">
                    @if($story->is_active)
                        <span class="status-active flex items-center rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">نشط</span>
                    @else
                        <span class="flex items-center rounded-full border border-gray-500/20 bg-gray-500/10 px-3 py-1 text-xs font-medium text-gray-400">معطّل</span>
                    @endif

                    <div class="flex-1"></div>

                    <a href="{{ route('admin.homepage-items.edit', $story) }}"
                       class="rounded-lg border border-white/10 px-4 py-1.5 text-sm text-gray-300 transition-colors duration-200 hover:bg-white/5">
                        تعديل
                    </a>

                    <button type="button"
                            data-delete-trigger
                            data-delete-title="{{ $story->title }}"
                            data-delete-action="{{ route('admin.homepage-items.destroy', $story) }}"
                            class="rounded-lg border border-red-500/20 px-4 py-1.5 text-sm text-red-400 transition duration-200 hover:scale-[1.03] hover:bg-red-500/10">
                        حذف
                    </button>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 rounded-2xl border border-dashed border-white/10 bg-[#1A1A24] p-10 text-center text-[#8B8B9E]">
                لا توجد استوريات حتى الآن. ابدأ بإضافة أول استوري للتطبيق.
            </div>
        @endforelse
    </div>

    <div>{{ $items->links() }}</div>
</section>

<div data-delete-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl border border-white/10 bg-[#1A1A24] p-6 shadow-[0_0_40px_rgba(232,53,109,.12)]">
        <h3 class="text-xl font-black text-white">حذف الاستوري؟</h3>
        <p class="mt-2 text-sm leading-7 text-[#8B8B9E]">سيتم حذف <span data-delete-title class="font-bold text-white"></span> من استوري التطبيق. لا يمكن التراجع عن هذه الخطوة.</p>
        <form method="POST" data-delete-form class="mt-6 flex items-center justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" data-delete-cancel class="rounded-lg border border-white/10 px-4 py-2 text-sm font-bold text-[#F0F0F5]">إلغاء</button>
            <button class="rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-2 text-sm font-bold text-red-300 transition hover:bg-red-500/20">حذف نهائي</button>
        </form>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const grid = document.querySelector('[data-story-grid]');
                const cards = Array.from(document.querySelectorAll('[data-story-card]'));
                const search = document.querySelector('[data-story-search]');
                const filters = Array.from(document.querySelectorAll('[data-filter]'));
                const sort = document.querySelector('[data-story-sort]');
                let activeFilter = 'all';

                const applyFilters = () => {
                    const query = (search?.value || '').trim().toLowerCase();

                    cards.forEach((card) => {
                        const matchesSearch = !query || card.dataset.search.includes(query);
                        const matchesFilter =
                            activeFilter === 'all'
                            || card.dataset.status === activeFilter
                            || (activeFilter === 'ending' && card.dataset.ending === '1');

                        card.classList.toggle('hidden', !(matchesSearch && matchesFilter));
                    });
                };

                const applySort = () => {
                    if (!grid || !sort) return;
                    const sorted = [...cards].sort((a, b) => {
                        if (sort.value === 'newest') return Number(b.dataset.created) - Number(a.dataset.created);
                        if (sort.value === 'ending') return Number(a.dataset.ends) - Number(b.dataset.ends);
                        if (sort.value === 'title') return a.dataset.title.localeCompare(b.dataset.title, 'ar');
                        return Number(a.dataset.order) - Number(b.dataset.order);
                    });

                    sorted.forEach((card) => grid.appendChild(card));
                };

                search?.addEventListener('input', applyFilters);
                sort?.addEventListener('change', () => {
                    applySort();
                    applyFilters();
                });

                filters.forEach((button) => {
                    button.addEventListener('click', () => {
                        activeFilter = button.dataset.filter;
                        filters.forEach((item) => {
                            item.classList.toggle('bg-[#E8356D]', item === button);
                            item.classList.toggle('text-white', item === button);
                            item.classList.toggle('border', item !== button);
                            item.classList.toggle('border-white/10', item !== button);
                        });
                        applyFilters();
                    });
                });

                const modal = document.querySelector('[data-delete-modal]');
                const form = document.querySelector('[data-delete-form]');
                const title = document.querySelector('[data-delete-title]');

                document.querySelectorAll('[data-delete-trigger]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (!modal || !form || !title) return;
                        form.action = button.dataset.deleteAction;
                        title.textContent = button.dataset.deleteTitle;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    });
                });

                document.querySelector('[data-delete-cancel]')?.addEventListener('click', () => {
                    modal?.classList.add('hidden');
                    modal?.classList.remove('flex');
                });

                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                });

                applySort();
                applyFilters();
            });
        </script>
    @endpush
@endonce
