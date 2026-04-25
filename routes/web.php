<?php

use App\Http\Controllers\AiPatternController;
use App\Http\Controllers\JamController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/patterns', [PatternController::class, 'index'])->name('patterns.index');
    Route::get('/patterns/create', [PatternController::class, 'create'])->name('patterns.create');
    Route::post('/patterns', [PatternController::class, 'store'])->name('patterns.store');

    Route::get('/patterns/generate', [AiPatternController::class, 'create'])->name('patterns.generate.create');
    Route::post('/patterns/generate', [AiPatternController::class, 'store'])->name('patterns.generate.store');
    Route::post('/patterns/generate/save', [AiPatternController::class, 'save'])->name('patterns.generate.save');

    Route::get('/patterns/{pattern}/edit', [PatternController::class, 'edit'])->name('patterns.edit');
    Route::put('/patterns/{pattern}', [PatternController::class, 'update'])->name('patterns.update');
    Route::delete('/patterns/{pattern}', [PatternController::class, 'destroy'])->name('patterns.destroy');

    Route::resource('jams', JamController::class);
    Route::post('/jams/{jam}/patterns', [JamController::class, 'attachPattern'])->name('jams.patterns.attach');
    Route::delete('/jams/{jam}/patterns/{pattern}', [JamController::class, 'detachPattern'])->name('jams.patterns.detach');
});

require __DIR__.'/auth.php';
