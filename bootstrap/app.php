<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {        
        // Đăng ký middleware với alias
        $middleware->alias([
            'set.locale' => \App\Http\Middleware\SetLocale::class,
        ]);

        // Áp dụng cho tất cả web routes (hoặc chỉ định group cụ thể)
        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,  // Hoặc dùng alias: 'set.locale'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
