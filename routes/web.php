<?php

use App\Http\Controllers\PersonalDataController;
use App\Http\Controllers\SheetController;
use App\Http\Middleware\Instrument;
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
    'middleware' => ['auth:sanctum', 'verified', Instrument::class],
], function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::prefix('/personal-data')
        ->name('personal-data.')
        ->group(function () {
            Route::get('edit', [PersonalDataController::class, 'edit'])->name('edit');
        });
    Route::prefix('/sheets')
        ->name('sheets.')
        ->group(function () {
            Route::get('', [SheetController::class, 'index'])->name('index');
            Route::get('/{instrument}', [SheetController::class, 'show'])->name('show');
            Route::get('/{sheet}/{instrument}/{variant}', [SheetController::class, 'download'])->name('download');
        });
});

Route::middleware(['auth:sanctum', 'verified'])
    ->name('set-instrument.')
    ->prefix('/user/set-instrument')
    ->group(function () {
        Route::get('', [PersonalDataController::class, 'setInstrument'])
            ->name('form');
        Route::post('', [PersonalDataController::class, 'saveInstrument'])
            ->name('save');
    });

