<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //リダイアル先の変更
        $middleware->redirectTo(
            guests: '/login',      // 未ログイン時の飛ばし先
            users: '/schedules',   // ログイン済みの時の飛ばし先 (ここを dashboard から変更)
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
