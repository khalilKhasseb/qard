<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Requests\CreateSectionRequest;
use Illuminate\Http\Request;

// Test data as it would come from the frontend
$testData1 = [
    'section_type' => 'contact',
    'title' => 'Test Section',
    'content' => [],  // Array
    'is_visible' => true,
    'display_order' => 0,
];

$testData2 = [
    'section_type' => 'contact',
    'title' => 'Test Section',
    'content' => (object)[],  // Object
    'is_visible' => true,
    'display_order' => 0,
];

echo "=== Testing Section Validation ===\n\n";

// Test 1: Empty array
echo "Test 1: content = [] (array)\n";
$rules = (new CreateSectionRequest())->rules();
$validator1 = \Illuminate\Support\Facades\Validator::make($testData1, $rules);
echo "Passes: " . ($validator1->passes() ? 'YES' : 'NO') . "\n";
if (!$validator1->passes()) {
    echo "Errors: " . json_encode($validator1->errors()->toArray()) . "\n";
}
echo "\n";

// Test 2: Empty object
echo "Test 2: content = {} (object)\n";
$validator2 = \Illuminate\Support\Facades\Validator::make($testData2, $rules);
echo "Passes: " . ($validator2->passes() ? 'YES' : 'NO') . "\n";
if (!$validator2->passes()) {
    echo "Errors: " . json_encode($validator2->errors()->toArray()) . "\n";
}
echo "\n";

// What the current validation rule actually checks
echo "Current validation rule for 'content':\n";
var_dump($rules['content']);
echo "\n";

// Test what happens with Laravel's array rule
echo "Laravel array rule with []: " . (\Illuminate\Support\Facades\Validator::make(['content' => []], ['content' => 'array'])->passes() ? 'PASS' : 'FAIL') . "\n";
echo "Laravel array rule with {}: " . (\Illuminate\Support\Facades\Validator::make(['content' => (object)[]], ['content' => 'array'])->passes() ? 'PASS' : 'FAIL') . "\n";
echo "Laravel array rule with empty object array: " . (\Illuminate\Support\Facades\Validator::make(['content' => ['key' => 'value']], ['content' => 'array'])->passes() ? 'PASS' : 'FAIL') . "\n";
