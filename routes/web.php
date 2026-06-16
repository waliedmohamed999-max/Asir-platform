<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\CityManagementController;
use App\Http\Controllers\Admin\CouponManagementController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\Admin\FaqManagementController;
use App\Http\Controllers\Admin\HomepageItemController;
use App\Http\Controllers\Admin\OrganizerManagementController;
use App\Http\Controllers\Admin\PaymentManagementController;
use App\Http\Controllers\Admin\ResaleListingManagementController;
use App\Http\Controllers\Admin\PageManagementController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupportConversationManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VenueManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomepageItemShowController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ResaleMarketplaceController;
use App\Http\Controllers\Organizer\OrganizerDashboardController;
use App\Http\Controllers\Organizer\OrganizerEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/discover/{homepageItem}', HomepageItemShowController::class)->name('homepage-items.show');
Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');

Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/resale', ResaleMarketplaceController::class)->name('resale.index');
Route::get('/events/{event:slug}/checkout', [BookingController::class, 'create'])->middleware('auth')->name('bookings.create');
Route::post('/events/{event:slug}/book', [BookingController::class, 'store'])->middleware('auth')->name('bookings.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/my-tickets', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/my-tickets/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/my-tickets/{booking}/pdf', [BookingController::class, 'pdf'])->name('bookings.pdf');
});

Route::get('/admin', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'role:admin,super_admin,event_manager,content_manager,finance,support']);

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,super_admin,event_manager,content_manager,finance,support'])->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('events', EventManagementController::class)->except(['show']);
    Route::get('/bookings', [BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/export/csv', [BookingManagementController::class, 'exportCsv'])->name('bookings.export.csv');
    Route::get('/bookings/export/xlsx', [BookingManagementController::class, 'exportXlsx'])->name('bookings.export.xlsx');
    Route::get('/bookings/export/pdf', [BookingManagementController::class, 'exportPdf'])->name('bookings.export.pdf');
    Route::get('/bookings/export/print', [BookingManagementController::class, 'print'])->name('bookings.export.print');
    Route::get('/bookings/{booking}', [BookingManagementController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}', [BookingManagementController::class, 'update'])->name('bookings.update');
    Route::post('/bookings/{booking}/resend', [BookingManagementController::class, 'resend'])->name('bookings.resend');
    Route::resource('categories', CategoryManagementController::class)->except(['show', 'destroy']);
    Route::resource('cities', CityManagementController::class)->except(['show', 'destroy']);
    Route::resource('venues', VenueManagementController::class)->except(['show', 'destroy']);
    Route::get('/app-stories', [HomepageItemController::class, 'appStories'])->name('app-stories.index');
    Route::resource('homepage-items', HomepageItemController::class)->except(['show']);
    Route::resource('pages', PageManagementController::class)->except(['show']);
    Route::resource('faqs', FaqManagementController::class)->except(['show', 'destroy']);
    Route::get('/payments', [PaymentManagementController::class, 'index'])->name('payments.index');
    Route::get('/payments/export/csv', [PaymentManagementController::class, 'exportCsv'])->name('payments.export.csv');
    Route::get('/payments/export/xlsx', [PaymentManagementController::class, 'exportXlsx'])->name('payments.export.xlsx');
    Route::get('/payments/export/pdf', [PaymentManagementController::class, 'exportPdf'])->name('payments.export.pdf');
    Route::get('/payments/export/print', [PaymentManagementController::class, 'print'])->name('payments.export.print');
    Route::get('/payments/{payment}', [PaymentManagementController::class, 'show'])->name('payments.show');
    Route::patch('/payments/{payment}', [PaymentManagementController::class, 'update'])->name('payments.update');
    Route::get('/support-conversations', [SupportConversationManagementController::class, 'index'])->name('support-conversations.index');
    Route::get('/support-conversations/{supportConversation}', [SupportConversationManagementController::class, 'show'])->name('support-conversations.show');
    Route::post('/support-conversations/{supportConversation}/reply', [SupportConversationManagementController::class, 'reply'])->name('support-conversations.reply');
    Route::patch('/support-conversations/{supportConversation}', [SupportConversationManagementController::class, 'update'])->name('support-conversations.update');
    Route::get('/resale-listings', [ResaleListingManagementController::class, 'index'])->name('resale-listings.index');
    Route::patch('/resale-listings/{resaleListing}', [ResaleListingManagementController::class, 'update'])->name('resale-listings.update');
    Route::resource('users', UserManagementController::class)->except(['show', 'destroy']);
    Route::resource('organizers', OrganizerManagementController::class)->except(['destroy']);
    Route::get('/reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('/reports/sales/export/csv', [SalesReportController::class, 'exportCsv'])->name('reports.sales.export.csv');
    Route::get('/reports/sales/export/xlsx', [SalesReportController::class, 'exportXlsx'])->name('reports.sales.export.xlsx');
    Route::get('/reports/sales/export/pdf', [SalesReportController::class, 'exportPdf'])->name('reports.sales.export.pdf');
    Route::get('/reports/sales/export/print', [SalesReportController::class, 'print'])->name('reports.sales.export.print');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/export/csv', [ActivityLogController::class, 'exportCsv'])->name('activity-logs.export.csv');
    Route::get('/activity-logs/export/xlsx', [ActivityLogController::class, 'exportXlsx'])->name('activity-logs.export.xlsx');
    Route::get('/activity-logs/export/pdf', [ActivityLogController::class, 'exportPdf'])->name('activity-logs.export.pdf');
    Route::get('/activity-logs/export/print', [ActivityLogController::class, 'print'])->name('activity-logs.export.print');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::resource('coupons', CouponManagementController::class)->except(['show', 'destroy']);
});

Route::prefix('organizer')->name('organizer.')->middleware(['auth', 'role:organizer'])->group(function () {
    Route::get('/dashboard', OrganizerDashboardController::class)->name('dashboard');
    Route::resource('events', OrganizerEventController::class)->except(['show', 'destroy']);
});
