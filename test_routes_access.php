<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "=== ROUTE ACCESS TEST ===\n\n";

// 1. Verify routes exist
echo "1. Route Names Available:\n";
$requiredRoutes = [
    'cards.index', 'cards.create', 'cards.store', 'cards.edit', 'cards.update', 'cards.publish',
    'themes.index', 'themes.create', 'themes.store', 'themes.edit', 'themes.update', 'themes.duplicate',
    'dashboard', 'login', 'register'
];

foreach ($requiredRoutes as $route) {
    try {
        $url = route($route, [], false);
        echo "   ✓ $route: $url\n";
    } catch (\Exception $e) {
        echo "   ✗ $route: MISSING\n";
    }
}

echo "\n2. API Routes (Should have api.* prefix):\n";
$apiRoutes = ['api.cards.index', 'api.cards.store', 'api.themes.index'];
foreach ($apiRoutes as $route) {
    try {
        $url = route($route, [], false);
        $hasCorrectPrefix = strpos($url, '/api/') !== false;
        echo "   " . ($hasCorrectPrefix ? "✓" : "✗") . " $route: $url\n";
    } catch (\Exception $e) {
        echo "   ✗ $route: MISSING\n";
    }
}

echo "\n3. Database Tables:\n";
try {
    $tables = ['users', 'business_cards', 'themes', 'card_sections', 'subscriptions', 'payments'];
    foreach ($tables as $table) {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        echo "   " . ($exists ? "✓" : "✗") . " $table\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n4. Sample User & Data:\n";
try {
    $user = User::first();
    if ($user) {
        echo "   ✓ User exists: {$user->name} (ID: {$user->id})\n";
        $cardCount = $user->cards()->count();
        $themeCount = $user->themes()->count();
        echo "   - Cards: $cardCount\n";
        echo "   - Themes: $themeCount\n";
        
        if ($cardCount == 0) {
            echo "   ⚠ No cards found - user needs to create some\n";
        }
    } else {
        echo "   ✗ No users in database\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Controllers Available:\n";
$controllers = [
    'App\\Http\\Controllers\\CardController',
    'App\\Http\\Controllers\\ThemeController',
    'App\\Http\\Controllers\\SectionController',
    'App\\Http\\Controllers\\DashboardController',
];
foreach ($controllers as $controller) {
    echo "   " . (class_exists($controller) ? "✓" : "✗") . " " . basename($controller) . "\n";
}

echo "\n6. Vue Pages Available:\n";
$vuePages = [
    'resources/js/Pages/Cards/Index.vue',
    'resources/js/Pages/Cards/Create.vue',
    'resources/js/Pages/Themes/Index.vue',
    'resources/js/Pages/Dashboard.vue',
];
foreach ($vuePages as $page) {
    echo "   " . (file_exists($page) ? "✓" : "✗") . " " . basename(dirname($page)) . "/" . basename($page) . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nSummary:\n";
echo "- Routes: " . (route('cards.index') === 'http://qard.test/cards' ? 'CORRECT' : 'WRONG') . "\n";
echo "- Example URL to test: http://qard.test/login (then access /cards)\n";
