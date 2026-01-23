<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Validator;

echo "=== Laravel 'required' Rule ===\n\n";

$tests = [
    ['value' => [], 'desc' => 'Empty array []'],
    ['value' => (object) [], 'desc' => 'Empty object {}'],
    ['value' => [1, 2, 3], 'desc' => 'Non-empty array'],
    ['value' => ['key' => 'value'], 'desc' => 'Associative array'],
    ['value' => '', 'desc' => 'Empty string'],
    ['value' => null, 'desc' => 'Null'],
];

foreach ($tests as $test) {
    $validator = Validator::make(['content' => $test['value']], ['content' => 'required']);
    $result = $validator->passes() ? 'PASS' : 'FAIL';
    echo "[$result] {$test['desc']}: ".json_encode($test['value'])."\n";
}

echo "\n=== What Does 'required' Actually Check? ===\n";
echo "In Laravel, 'required' means the field must exist AND not be empty.\n";
echo "Empty arrays are considered 'empty' by Laravel.\n";
echo "Empty objects are also considered 'empty'.\n";
echo "But when sent via JSON, they might be interpreted differently.\n\n";

// JSON encoding test
echo "JSON Encoding:\n";
echo '[] encodes to: '.json_encode([])."\n";
echo '(object)[] encodes to: '.json_encode((object) [])."\n";
echo '{} (literal) is not valid PHP but would encode to: '.json_encode(['key' => 'value'])."\n";
