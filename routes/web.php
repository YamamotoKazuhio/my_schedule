<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. 公開ルート: /schedule に来たら一覧へ飛ばす
Route::get('/', function () {
    return redirect()->route('schedules.index');
});

Route::get('/welcome', function () {
    return view('welcome');
});

// 2. 認証が必要なルートグループ
Route::middleware(['auth', 'verified', 'prevent-back'])->group(function () {

    /**
     * --- スケジュール関連 ---
     * resource の第一引数を 'schedules' に明示することで、
     * URLを /schedule/schedules に固定し、404を回避します。
     */
    Route::patch('schedules/{schedule}/toggle', [ScheduleController::class, 'toggle'])->name('schedules.toggle');
    Route::patch('schedules/{schedule}/update-date', [ScheduleController::class, 'updateDate'])->name('schedules.updateDate');
    
    // リソースルートを一括定義（以前のバラバラな定義を統合）
    Route::resource('schedules', ScheduleController::class);

    // --- プロフィール関連 ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- 管理者専用ルート ---
    Route::middleware(['can:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ダッシュボード
    Route::get('/dashboard', function () {
        return redirect()->route('schedules.index');
    })->name('dashboard');
});

// 3. 認証関連
require __DIR__ . '/auth.php';