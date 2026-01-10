<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ROUTE VERIFICATION ===\n\n";

$routes = \Illuminate\Support\Facades\Route::getRoutes();

$webCards = [];
$apiCards = [];

foreach ($routes as $route) {
    $name = $route->getName();
    $uri = $route->uri;
    $action = $route->action['uses'] ?? $route->action['controller'] ?? 'Unknown';
    
    // Check for card routes
    if (strpos($name, 'cards') !== false && strpos($name, 'admin') === false) {
        if (strpos($uri, 'api/') === 0) {
            $apiCards[] = ['name' => $name, 'uri' => $uri, 'action' => $action];
        } else {
            $webCards[] = ['name' => $name, 'uri' => $uri, 'action' => $action];
        }
    }
    
    // Check for theme routes
    if (strpos($name, 'themes') !== false && strpos($name, 'admin') === false) {
        if (strpos($uri, 'api/') === 0) {
            $apiCards[] = ['name' => $name, 'uri' => $uri, 'action' => $action, 'type' => 'theme'];
        } else {
            $webCards[] = ['name' => $name, 'uri' => $uri, 'action' => $action, 'type' => 'theme'];
        }
    }
}

echo "API Routes:\n";
foreach ($apiCards as $route) {
    $type = $route['type'] ?? 'card';
    echo "  {$route['name']}: {$route['uri']} => {$route['action']} ({$type})\n";
}

echo "\nWeb Routes:\n";
foreach ($webCards as $route) {
    $type = $route['type'] ?? 'card';
    echo "  {$route['name']}: {$route['uri']} => {$route['action']} ({$type})\n";
}

echo "\nRoute Helper Tests:\n";
echo "  route('cards.index') = " . route('cards.index') . "\n";
echo "  route('cards.create') = " . route('cards.create') . "\n";
echo "  route('themes.index') = " . route('themes.index') . "\n";
echo "  route('api.cards.index') = " . route('api.cards.index') . "\n";

echo "\nIs there duplicate names?\n";
$names = array_map(fn($r) => $r['name'], array_merge($webCards, $apiCards));
$duplicates = array_filter(array_count_values($names), fn($count) => $count > 1);
if ($duplicates) {
    echo "  YES! Duplicate names: " . implode(', ', array_keys($duplicates)) . "\n";
} else {
    echo "  No duplicates found.\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
