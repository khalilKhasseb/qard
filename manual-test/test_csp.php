<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Create a simple test route
Route::get('/test-csp', function (Request $request) {
    $middleware = new \App\Http\Middleware\SecurityHeaders;

    $response = new \Illuminate\Http\Response('Test');
    $processedResponse = $middleware->handle($request, function () use ($response) {
        return $response;
    });

    echo 'Environment: '.app()->environment()."\n";
    echo 'APP_DEBUG: '.config('app.debug')."\n\n";
    echo "CSP Header:\n";
    echo $processedResponse->headers->get('Content-Security-Policy')."\n\n";

    $csp = $processedResponse->headers->get('Content-Security-Policy');

    if (str_contains($csp, 'localhost:5173') || str_contains($csp, '[::1]:5173')) {
        echo "✅ CSP is allowing Vite dev server\n";
    } else {
        echo "❌ CSP is NOT allowing Vite dev server\n";
    }

    if (str_contains($csp, 'fonts.bunny.net')) {
        echo "✅ CSP is allowing fonts.bunny.net\n";
    } else {
        echo "❌ CSP is NOT allowing fonts.bunny.net\n";
    }
});

$app->boot();
$app->make('router')->getRoutes()->match(Request::create('/test-csp'));

// Or just run it directly
$app->handle(Request::create('/test-csp'));
