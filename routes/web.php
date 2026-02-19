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

// 1. トップページにアクセスしたらスケジュールにリダイレクト
Route::get('/', function () {
    return redirect()->route('schedules.index');
});

// 2. 標準のウェルカムページ
Route::get('/welcome', function () {
    return view('welcome');
});

// 3. 認証が必要なルートグループ
Route::middleware(['auth', 'verified', 'prevent-back'])->group(function () {

    /**
     * --- スケジュール関連 ---
     * 全てのパスを /schedule/schedule に統一します
     */
    Route::prefix('schedule')->group(function () {

        // 一覧表示: GET /schedule
        Route::get('/', [ScheduleController::class, 'index'])->name('schedules.index');

        // 保存処理をリソースより前に明示的に定義: POST /schedule/schedule
        Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedules.store');

        // その他のリソースルート
        Route::resource('schedule', ScheduleController::class)->except(['index', 'store'])->names([
            'create'  => 'schedules.create',
            'edit'    => 'schedules.edit',
            'update'  => 'schedules.update',
            'destroy' => 'schedules.destroy',
        ]);

        // カスタム操作
        Route::patch('/schedule/{schedule}/toggle', [ScheduleController::class, 'toggle'])->name('schedules.toggle');
        Route::patch('/schedule/{schedule}/update-date', [ScheduleController::class, 'updateDate'])->name('schedules.updateDate');
    });

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
        return view('dashboard');
    })->name('dashboard');
});

// 4. 認証関連のルート
require __DIR__ . '/auth.php';
