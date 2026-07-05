<?php

use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StatsController as AdminStatsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NeighborhoodController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/login', 'pages.login')->name('login');
Route::view('/register', 'pages.register')->name('register');
Route::view('/water-feeds', 'pages.water-feeds')->name('water-feeds');
Route::view('/dashboard', 'pages.dashboard')->name('dashboard');
Route::view('/notifications', 'pages.notifications')->name('notifications');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::view('/reports', 'admin.reports')->name('reports');
    Route::view('/users', 'admin.users')->name('users');
    Route::view('/monitoring', 'admin.monitoring')->name('monitoring');
});

Route::prefix('api')->group(function () {
    Route::get('/neighborhoods', [NeighborhoodController::class, 'index']);

    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store'])->middleware('api.auth');

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::middleware('api.auth')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/notifications/subscriptions', [NotificationController::class, 'subscriptions']);
        Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::put('/notifications/subscriptions', [NotificationController::class, 'saveSubscriptions']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    });

    Route::prefix('admin')->middleware('api.admin')->group(function () {
        Route::get('/reports', [AdminReportController::class, 'index']);
        Route::put('/reports/{id}/verify', [AdminReportController::class, 'verify']);
        Route::delete('/reports/{id}', [AdminReportController::class, 'destroy']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{id}/suspend', [AdminUserController::class, 'suspend']);
        Route::put('/users/{id}/activate', [AdminUserController::class, 'activate']);

        Route::get('/stats', [AdminStatsController::class, 'index']);
    });
});
