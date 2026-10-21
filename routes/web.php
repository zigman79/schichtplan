<?php

use App\Http\Controllers\ArbeitszeitController;
use App\Http\Controllers\JobGroupController;
use App\Http\Controllers\MinijobGroupController;
use App\Http\Controllers\MinijobVorgabeController;
use App\Http\Controllers\PermalinkController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Schreibgeschützte Wochenansicht per Permalink, ohne Login
Route::middleware('throttle:120,1')->prefix('plan/{token}')->name('permalink.')->group(function () {
    Route::get('/', [PermalinkController::class, 'show'])
        ->name('show')
        ->where('token', '[A-Za-z0-9]{32,64}');
    Route::get('woche/{montag}', [PermalinkController::class, 'woche'])
        ->name('woche')
        ->where(['token' => '[A-Za-z0-9]{32,64}', 'montag' => '\d{4}-\d{2}-\d{2}']);
});

Route::group(['middleware' => ['auth', 'verified']], function () {

    Route::get('/dashboard/{year?}/{month?}', [ArbeitszeitController::class, 'index'])->name('dashboard');

    Route::prefix('arbeitszeiten')->group(function () {
        Route::post('/{user}', [ArbeitszeitController::class, 'store'])->name('store.arbeitszeit');
        Route::put('/{arbeitszeit}', [ArbeitszeitController::class, 'update'])->name('update.arbeitszeit');
        Route::delete('/{arbeitszeit}', [ArbeitszeitController::class, 'destroy'])->name('destroy.arbeitszeit');
        Route::get('/print/{year?}/{month?}/{preview?}', [ArbeitszeitController::class, 'print'])->name('print.arbeitszeit');
        Route::get('/summary/{year?}/{month?}/{preview?}', [ArbeitszeitController::class, 'summary'])->name('print.uebersicht');
        Route::get('/overview/{year}/{user}/{preview?}', [ArbeitszeitController::class, 'overview'])->name('print.overview');
        Route::get('/wochenplan/{preview?}', [ArbeitszeitController::class, 'showWochenplan'])->name('print.wochenplan');
    });

    Route::get('wochenplan', [ArbeitszeitController::class, 'wochenplan'])->name('wochenplan');

    Route::prefix('minijobvorgaben')->group(function () {
        Route::get('/{year?}', [MinijobVorgabeController::class, 'index'])->name('minijobvorgabe.index');
        Route::post('', [MinijobVorgabeController::class, 'store'])->name('minijobvorgabe.store');
        Route::post('/apply-to-all', [MinijobVorgabeController::class, 'applyToAllMonths'])->name('minijobvorgabe.applyToAll');
        Route::put('/{minijobvorgabe}', [MinijobVorgabeController::class, 'update'])->name('minijobvorgabe.update');
        Route::delete('/{minijobvorgabe}', [MinijobVorgabeController::class, 'destroy'])->name('minijobvorgabe.destroy');
    });


    Route::group(['middleware' => 'admin'], function () {
        Route::put('order/users', [UserController::class, 'order'])->name('order.users');
        Route::get('users/switch/{id}', [UserController::class, 'switch'])->name('switch-user');
        Route::post('users/{user}/generate-token', [UserController::class, 'generateToken'])->name('users.generate-token');
        Route::post('users/{user}/permalink', [UserController::class, 'generatePermalink'])->name('users.permalink.store');
        Route::delete('users/{user}/permalink', [UserController::class, 'destroyPermalink'])->name('users.permalink.destroy');
        Route::put('users/{user}/minijob-uebertrag/{year}', [UserController::class, 'updateMinijobUebertrag'])->whereNumber('year')->name('users.minijob-uebertrag.update');
        Route::resource('users', UserController::class)->except('show');
        Route::resource('minijobGroups', MinijobGroupController::class)->except(['show']);
    });
    Route::group(['middleware' => 'teamLeader'], function () {
        Route::get('users/{user}/trinkgeldliste', [UserController::class, 'trinkgeldliste'])->name('users.trinkgeldliste');
        Route::get('jahresuebersicht/pdf/{year?}/{preview?}', [ArbeitszeitController::class, 'printJahresuebersicht'])->name('print.jahresuebersicht');
        Route::get('jahresuebersicht/{year?}', [ArbeitszeitController::class, 'jahresuebersicht'])->name('jahresuebersicht');
        Route::resource('jobGroups', JobGroupController::class)->except(['show']);
        Route::resource('shifts', ShiftController::class)->except(['show']);
        Route::post('shifts/multiple', [ShiftController::class, 'storeMultiple'])->name('shifts.storeMultiple');
        Route::delete('shifts/multiple/destroy', [ShiftController::class, 'destroyMultiple'])->name('shifts.destroyMultiple');
        Route::post('shifts/{shift}/users', [ShiftController::class, 'addUser'])->name('shifts.addUser');
        Route::delete('shifts/{shift}/users/{user}', [ShiftController::class, 'removeUser'])->name('shifts.removeUser');
        Route::post('shifts/{shift}/decline', [ShiftController::class, 'declineUser'])->name('shifts.declineUser');
        Route::delete('shifts/{shift}/decline/{user}', [ShiftController::class, 'removeDecline'])->name('shifts.removeDecline');
    });

    Route::get('switch-back', [UserController::class, 'switchBack'])->name('switch-back');

});

require __DIR__.'/auth.php';
