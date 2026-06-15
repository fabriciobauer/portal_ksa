<?php

use App\Http\Middleware\AdaptSessionToRequestHost;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);

$app = Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            AdaptSessionToRequestHost::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Shared hosts like KingHost expose the web root as "www" instead of "public".
if (is_dir($basePath.'/www') && ! is_dir($basePath.'/public')) {
    $app->usePublicPath($basePath.'/www');
}

return $app;
