<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\ImageProxyController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\ResaleListingController;
use App\Http\Controllers\Api\V1\SocialController;
use App\Http\Controllers\Api\V1\SupportConversationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

    Route::get('/images/proxy', ImageProxyController::class)->name('api.v1.images.proxy');
    Route::get('/home', HomeController::class);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event:slug}', [EventController::class, 'show']);
    Route::get('/offers', [CatalogController::class, 'offers']);
    Route::get('/services', [CatalogController::class, 'services']);
    Route::get('/venues', [CatalogController::class, 'venues']);
    Route::get('/cities', [CatalogController::class, 'cities']);
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/resale-listings', [ResaleListingController::class, 'index']);
    Route::get('/recommendations', RecommendationController::class);
    Route::get('/support/conversations/current', [SupportConversationController::class, 'current']);
    Route::post('/support/conversations', [SupportConversationController::class, 'store']);
    Route::get('/support/conversations/{conversation}', [SupportConversationController::class, 'show']);
    Route::post('/support/conversations/{conversation}/messages', [SupportConversationController::class, 'message']);
    Route::get('/events/{event:slug}/reviews', [SocialController::class, 'reviews']);
    Route::get('/events/{event:slug}/comments', [SocialController::class, 'comments']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/me', [AuthController::class, 'updateProfile']);
        Route::get('/profile', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/wallet', [BookingController::class, 'index']);
        Route::get('/tickets', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::get('/resale-listings/mine', [ResaleListingController::class, 'mine']);
        Route::post('/resale-listings', [ResaleListingController::class, 'store']);
        Route::patch('/resale-listings/{resaleListing}', [ResaleListingController::class, 'update']);
        Route::get('/favorites', [ProfileController::class, 'favorites']);
        Route::post('/favorites/{event:slug}', [ProfileController::class, 'toggleFavorite']);
        Route::get('/notifications', [ProfileController::class, 'notifications']);
        Route::post('/devices', [ProfileController::class, 'registerDevice']);
        Route::post('/events/{event:slug}/reviews', [SocialController::class, 'storeReview']);
        Route::post('/events/{event:slug}/comments', [SocialController::class, 'storeComment']);
        Route::post('/organizers/{organizer}/follow', [SocialController::class, 'followOrganizer']);
    });
});
