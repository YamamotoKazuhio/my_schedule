<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController; // ユーザー管理用
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. トップページにアクセスしたらスケジュール（カレンダー）にリダイレクト
Route::get('/', function () {
    return redirect()->route('schedules.index');
});

// 2. 標準のウェルカムページ（必要なければ削除可）
Route::get('/welcome', function () {
    return view('welcome');
});

// 3. 認証が必要なルートグループ
Route::middleware(['auth', 'verified'])->group(function () {

    // --- スケジュール関連 ---
    // カレンダー表示 (index)
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    // 新規作成画面 (create)
    Route::get('/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    // その他のリソースルート（store, edit, update, destroy）
    Route::resource('schedules', ScheduleController::class)->except(['index', 'create']);

    // スケジュール固有のカスタム操作
    Route::patch('/schedules/{schedule}/toggle', [ScheduleController::class, 'toggle'])->name('schedules.toggle');
    Route::patch('/schedules/{schedule}/update-date', [ScheduleController::class, 'updateDate'])->name('schedules.updateDate');

    // --- プロフィール関連 ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- 管理者専用ルート ---
    // Gate 'admin' を持っているユーザーのみアクセス可能
    Route::middleware(['can:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ダッシュボード（Breeze標準。schedulesに統合した場合は削除してもOK）
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// 4. 認証関連のルート（Breeze）を読み込み
require __DIR__ . '/auth.php';
