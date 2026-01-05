<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SYSTEM DIAGNOSTIC ===\n\n";

// Check models exist
echo "1. Models:\n";
echo "   - User model: " . (class_exists('App\Models\User') ? 'EXISTS' : 'MISSING') . "\n";
echo "   - BusinessCard model: " . (class_exists('App\Models\BusinessCard') ? 'EXISTS' : 'MISSING') . "\n";
echo "   - Theme model: " . (class_exists('App\Models\Theme') ? 'EXISTS' : 'MISSING') . "\n\n";

// Check controllers exist
echo "2. Controllers:\n";
echo "   - CardController: " . (class_exists('App\Http\Controllers\CardController') ? 'EXISTS' : 'MISSING') . "\n";
echo "   - ThemeController: " . (class_exists('App\Http\Controllers\ThemeController') ? 'EXISTS' : 'MISSING') . "\n\n";

// Check routes
echo "3. Route Names:\n";
try {
    $route = route('cards.index');
    echo "   - cards.index: " . $route . "\n";
} catch (\Exception $e) {
    echo "   - cards.index: ERROR - " . $e->getMessage() . "\n";
}

try {
    $route = route('cards.create');
    echo "   - cards.create: " . $route . "\n";
} catch (\Exception $e) {
    echo "   - cards.create: ERROR - " . $e->getMessage() . "\n";
}

try {
    $route = route('themes.index');
    echo "   - themes.index: " . $route . "\n";
} catch (\Exception $e) {
    echo "   - themes.index: ERROR - " . $e->getMessage() . "\n";
}

echo "\n4. Database Tables:\n";
try {
    $schema = app('db')->getSchemaBuilder();
    $tables = $schema->getAllTables();
    $expected = ['users', 'business_cards', 'themes', 'card_sections'];
    foreach ($expected as $table) {
        $exists = in_array($table, array_map(function($t) { return $t->Tables_in_qard; }, $tables));
        echo "   - $table: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
} catch (\Exception $e) {
    echo "   - ERROR: " . $e->getMessage() . "\n";
}

echo "\n5. Sample Data:\n";
try {
    $user = \App\Models\User::first();
    if ($user) {
        echo "   - User: {$user->name} (ID: {$user->id})\n";
        echo "   - Cards: " . $user->cards()->count() . "\n";
        echo "   - Themes: " . $user->themes()->count() . "\n";
    } else {
        echo "   - No users found in database\n";
    }
} catch (\Exception $e) {
    echo "   - ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
