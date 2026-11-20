<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Mahasiswa\RoomController as MahasiswaRoomController;
use App\Http\Controllers\Mahasiswa\BookingController as MahasiswaBookingController;
use App\Http\Controllers\Petugas\BookingController as PetugasBookingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.submit');

    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/dashboard', [AuthController::class, 'dashboard'])
        ->middleware(RoleMiddleware::class.':admin,petugas,mahasiswa')
        ->name('dashboard');

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->middleware(RoleMiddleware::class.':admin')
        ->name('admin.dashboard');

    Route::get('/petugas/dashboard', [DashboardController::class, 'index'])
        ->middleware(RoleMiddleware::class.':petugas')
        ->name('petugas.dashboard');

    Route::get('/mahasiswa/dashboard', [DashboardController::class, 'index'])
        ->middleware(RoleMiddleware::class.':mahasiswa')
        ->name('mahasiswa.dashboard');
});

Route::middleware([
    'auth',
    RoleMiddleware::class.':admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('facilities', FacilityController::class)
            ->except(['show']);

        Route::resource('rooms', RoomController::class)
            ->only([
                'index',
                'create',
                'store',
                'edit',
                'update',
            ]);
        Route::resource('users', UserController::class)
            ->only([
                'index',
                'create',
                'store',
                'edit',
                'update',
            ]);
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/print', [ReportController::class, 'print'])
            ->name('reports.print');
});

Route::middleware([
    'auth',
    RoleMiddleware::class.':mahasiswa',
])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/rooms', [MahasiswaRoomController::class, 'index'])
            ->name('rooms.index');

        Route::get('/rooms/{room}', [MahasiswaRoomController::class, 'show'])
            ->name('rooms.show');

        Route::post('/rooms/{room}/bookings', [MahasiswaBookingController::class, 'store'])
            ->name('bookings.store');

        Route::get('/bookings', [MahasiswaBookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [MahasiswaBookingController::class, 'show'])
            ->whereNumber('booking')
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/cancel', [
            MahasiswaBookingController::class,
            'cancel',
        ])
            ->whereNumber('booking')
            ->name('bookings.cancel');
                
            });

Route::middleware([
    'auth',
    RoleMiddleware::class.':petugas',
])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {
        Route::get('/bookings', [
            PetugasBookingController::class,
            'index',
        ])->name('bookings.index');

        Route::get('/bookings/{booking}', [
            PetugasBookingController::class,
            'show',
        ])
            ->whereNumber('booking')
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/status', [
            PetugasBookingController::class,
            'updateStatus',
        ])
            ->whereNumber('booking')
            ->name('bookings.status');
    });