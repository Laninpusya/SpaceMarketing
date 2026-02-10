<?php

use App\Http\Controllers\DomainsController;
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
    Route::resource('domains', DomainsController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::post('/domains/check-now', function () {
        $output = [];
        $return_var = 0;
        exec('php ' . base_path('check_domains.php') . ' 2>&1', $output, $return_var);

        $message = $return_var === 0 ? 'Проверка запущена! Логи уже в Telegram.' : 'Ошибка запуска проверки. Проверьте консоль сервера.';

        return redirect()->back()->with('status', $message);
    })->name('domains.check-now');
    
});

require __DIR__.'/auth.php';
