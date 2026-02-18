<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\PersonalDataController;
use App\Http\Controllers\SheetController;
use App\Http\Middleware\CheckIfActive;
use App\Http\Middleware\MustHaveInstrument;
use App\Http\Middleware\MustHavePersonalData;
use App\Models\User;
use App\Services\SharedFolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
            Route::get('/{song}', [SheetController::class, 'show'])->name('show');
            Route::get('/download/{sheet}', [SheetController::class, 'download'])->name('download');
        });
    Route::get('/files', \App\Livewire\FileBrowser::class)->name('files.index');
    Route::get('/files/download', function (Request $request, SharedFolderService $service) {
        $path = $request->query('path');

        if (! $path || str_contains($path, '..')) {
            abort(404);
        }

        if (! Storage::disk('shared')->exists($path)) {
            abort(404);
        }

        if (! $service->canAccess($request->user(), $path)) {
            abort(403);
        }

        return response()->download(
            Storage::disk('shared')->path($path),
            basename($path),
        );
    })->name('files.download')->middleware('signed');
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
