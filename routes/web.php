<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecordingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';

Route::get('check/auth', [DashboardController::class, 'connect'])->name('auth.check');
Route::get('check/auth/error', [DashboardController::class, 'authError'])->name('auth.error');
Route::get('checking/auth', [DashboardController::class, 'authChecking'])->name('auth.checking');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware('auth', 'auto_auth')->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/password', [UserController::class, 'password'])->name('password');
    Route::post('/password', [UserController::class, 'updatePassword'])->name('password.update');

    Route::resource('plans', PlanController::class);

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/status/{user}', [UserController::class, 'status'])->name('status');
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::post('/{user}', [UserController::class, 'update'])->name('update');
        Route::get('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::get('/create', [ContactController::class, 'create'])->name('create');
        Route::post('/', [ContactController::class, 'store'])->name('store');
        Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('edit');
        Route::post('/{contact}', [ContactController::class, 'update'])->name('update');
        Route::get('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
        Route::get('/sync/crm', [ContactController::class, 'sync'])->name('sync');
    });

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    Route::get('authorization/crm/oauth/callback', [SettingController::class, 'goHighLevelCallback'])->name('authorization.gohighlevel.callback');
    Route::get('/loginwith/{user}', [UserController::class, 'loginWith'])->name('users.loginwith');
    Route::get('/backtoadmin', [UserController::class, 'backToAdmin'])->name('backtoadmin');
});


Route::resource('recordings', RecordingController::class);
Route::get('contact', [ContactController::class, 'contacts'])->name('ghl.contacts');
Route::post('sendData', [ContactController::class, 'processConv'])->name('ghl.sendData');
Route::get('tags', [ContactController::class, 'tags'])->name('ghl.tags');

// agency user login data
// Route::get('user', [UserController::class, 'index'])->name('user');
