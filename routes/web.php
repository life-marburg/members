<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PersonalDataController;
use App\Http\Controllers\SheetController;
use App\Http\Middleware\CheckIfActive;
use App\Http\Middleware\MustHaveInstrument;
use App\Http\Middleware\MustHavePersonalData;
use App\Models\User;
use App\Rights;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
    return redirect(route('dashboard'));
});

Route::group([
    'middleware' => ['auth:sanctum', 'verified', MustHaveInstrument::class, MustHavePersonalData::class, CheckIfActive::class],
], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('/sheets')
        ->name('sheets.')
        ->group(function () {
            Route::get('', [SheetController::class, 'index'])->name('index');
            Route::get('/{instrument}', [SheetController::class, 'show'])->name('show');
            Route::get('/{sheet}/{instrument}/{variant}', [SheetController::class, 'download'])->name('download');
        });
    Route::view('/calendar', 'pages.calendar')->name('calendar');
    Route::middleware(['can:' . Rights::P_EDIT_PAGES])->resource('pages', PageController::class);
    Route::middleware(['can:' . Rights::P_MANAGE_MEMBERS])->resource('members', MemberController::class);
});

Route::middleware(['auth:sanctum', 'verified'])
    ->name('set-instrument.')
    ->prefix('/user/set-instrument')
    ->group(function () {
        Route::get('', [InstrumentController::class, 'setInstrument'])
            ->name('form');
        Route::post('', [InstrumentController::class, 'saveInstrument'])
            ->name('save');
    });
Route::middleware(['auth:sanctum', 'verified'])
    ->name('set-personal-data.')
    ->prefix('/user/set-personal-data')
    ->group(function () {
        Route::get('', [PersonalDataController::class, 'set'])
            ->name('form');
    });

Route::get('not-yet-active', function () {
    if (Auth::user()->status === User::STATUS_UNLOCKED) {
        return redirect(route('dashboard'));
    }

    return view('pages.not-yet-active');
})->name('not-yet-active');

Route::get('/calendar/caldav/internal', [CalendarController::class, 'getCalDAVCalendarOutput'])
    ->name('caldav.calendar.internal')
    ->middleware('auth.basic');
