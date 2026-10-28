<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
->withRouting(
    web: __DIR__.'/../routes/web.php',
    then: function () {
        \Illuminate\Support\Facades\Route::middleware('api')
            ->group(base_path('routes/api.php'));
    },
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (
        \App\Exceptions\OrderConflictException $e,
        \Illuminate\Http\Request $request
    ) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 409);
    });

    $exceptions->render(function (
        \App\Exceptions\UpstreamServiceException $e,
        \Illuminate\Http\Request $request
    ) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 503);
    });
})->create();
