<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('match.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Excel Matching Routes
Route::get('/match', [App\Http\Controllers\ExcelMatchController::class, 'index'])->name('match.index');
Route::post('/match/upload', [App\Http\Controllers\ExcelMatchController::class, 'upload'])->name('match.upload');
Route::post('/match/run', [App\Http\Controllers\ExcelMatchController::class, 'run'])->name('match.run');
Route::get('/match/download/{token}', [App\Http\Controllers\ExcelMatchController::class, 'download'])->name('match.download');
Route::get('/match/download-excel/{token}', [App\Http\Controllers\ExcelMatchController::class, 'downloadExcel'])->name('match.downloadExcel');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
