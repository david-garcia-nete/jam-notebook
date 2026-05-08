<?php

use App\Http\Controllers\AiPatternController;
use App\Http\Controllers\AiJamDevelopmentController;
use App\Http\Controllers\AiPatternDevelopmentController;
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

    Route::get('/patterns/{pattern}/develop', [AiPatternDevelopmentController::class, 'create'])->name('patterns.develop.create');
    Route::post('/patterns/{pattern}/develop', [AiPatternDevelopmentController::class, 'store'])->name('patterns.develop.store');
    Route::post('/patterns/{pattern}/develop/save', [AiPatternDevelopmentController::class, 'save'])->name('patterns.develop.save');

    Route::get('/patterns/{pattern}', [PatternController::class, 'show'])->name('patterns.show');
    Route::get('/patterns/{pattern}/edit', [PatternController::class, 'edit'])->name('patterns.edit');
    Route::put('/patterns/{pattern}', [PatternController::class, 'update'])->name('patterns.update');
    Route::delete('/patterns/{pattern}', [PatternController::class, 'destroy'])->name('patterns.destroy');

    Route::resource('jams', JamController::class);
    Route::get('/jams/{jam}/sheet', [JamController::class, 'sheet'])->name('jams.sheet');
    Route::post('/jams/{jam}/patterns', [JamController::class, 'attachPattern'])->name('jams.patterns.attach');
    Route::post('/jams/{jam}/patterns/{pattern}/update', [JamController::class, 'updatePatternPlacement'])->name('jams.patterns.update');
    Route::post('/jams/{jam}/patterns/{pattern}/move-up', [JamController::class, 'movePatternUp'])->name('jams.patterns.move-up');
    Route::post('/jams/{jam}/patterns/{pattern}/move-down', [JamController::class, 'movePatternDown'])->name('jams.patterns.move-down');
    Route::delete('/jams/{jam}/patterns/{pattern}', [JamController::class, 'detachPattern'])->name('jams.patterns.detach');
    Route::get('/jams/{jam}/develop', [AiJamDevelopmentController::class, 'create'])->name('jams.develop.create');
    Route::post('/jams/{jam}/develop', [AiJamDevelopmentController::class, 'store'])->name('jams.develop.store');
    Route::post('/jams/{jam}/develop/save', [AiJamDevelopmentController::class, 'save'])->name('jams.develop.save');
});

require __DIR__.'/auth.php';
