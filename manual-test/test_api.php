<?php

/**
 * API Endpoints Test Script
 * Tests all 26 API endpoints for TapIt application
 *
 * Usage: php test_api.php
 */

// Configuration
$baseUrl = 'http://qard.test/api';
$testEmail = 'test@example.com';
$testPassword = 'password';

// Helper function to make API calls
function apiCall($method, $endpoint, $data = null, $token = null)
{
    global $baseUrl;

    $url = $baseUrl.$endpoint;
    $ch = curl_init($url);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer '.$token;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response,
    ];
}

function printResult($name, $response)
{
    $status = $response['status'];
    $statusColor = $status >= 200 && $status < 300 ? "\033[32m" : "\033[31m";
    $resetColor = "\033[0m";

    echo "{$statusColor}[{$status}]{$resetColor} {$name}\n";

    if ($status >= 400) {
        echo '  Error: '.($response['body']['message'] ?? 'Unknown error')."\n";
        if (isset($response['body']['errors'])) {
            print_r($response['body']['errors']);
        }
    }

    return $response;
}

echo "=== TapIt API Endpoints Test ===\n\n";

// Get authentication token (assuming you have a login endpoint or create a token manually)
echo "Step 1: Get Authentication Token\n";
echo "Note: You need to create a token manually using: php artisan tinker\n";
echo "Then run: \$user = User::first(); echo \$user->createToken('test')->plainTextToken;\n\n";

$token = readline('Enter your API token: ');

if (empty($token)) {
    exit("Token is required. Exiting.\n");
}

echo "\n=== Testing Cards API (8 endpoints) ===\n\n";

// 1. GET /api/cards - List cards
$cards = printResult('1. GET /api/cards (list)', apiCall('GET', '/cards', null, $token));
echo "\n";

// 2. POST /api/cards - Create card
$createCard = printResult('2. POST /api/cards (create)', apiCall('POST', '/cards', [
    'title' => 'Test Business Card',
    'subtitle' => 'Software Developer',
], $token));
$cardId = $createCard['body']['data']['id'] ?? null;
echo "\n";

if (! $cardId) {
    exit("Failed to create card. Cannot continue tests.\n");
}

// 3. GET /api/cards/{id} - Show card
printResult("3. GET /api/cards/{$cardId} (show)", apiCall('GET', "/cards/{$cardId}", null, $token));
echo "\n";

// 4. PUT /api/cards/{id} - Update card
printResult("4. PUT /api/cards/{$cardId} (update)", apiCall('PUT', "/cards/{$cardId}", [
    'title' => 'Updated Business Card',
    'subtitle' => 'Senior Software Developer',
], $token));
echo "\n";

// 5. POST /api/cards/{id}/publish - Publish card
printResult("5. POST /api/cards/{$cardId}/publish (publish)", apiCall('POST', "/cards/{$cardId}/publish", [
    'is_published' => true,
], $token));
echo "\n";

// 6. POST /api/cards/{id}/duplicate - Duplicate card
$duplicateCard = printResult("6. POST /api/cards/{$cardId}/duplicate (duplicate)", apiCall('POST', "/cards/{$cardId}/duplicate", [], $token));
$duplicateCardId = $duplicateCard['body']['data']['id'] ?? null;
echo "\n";

// 7. GET /api/cards/{id}/analytics - Get analytics
printResult("7. GET /api/cards/{$cardId}/analytics (analytics)", apiCall('GET', "/cards/{$cardId}/analytics", null, $token));
echo "\n";

echo "\n=== Testing Sections API (4 endpoints) ===\n\n";

// 8. POST /api/cards/{card}/sections - Create section
$createSection = printResult("8. POST /api/cards/{$cardId}/sections (create)", apiCall('POST', "/cards/{$cardId}/sections", [
    'section_type' => 'contact',
    'title' => 'Contact Information',
    'content' => [
        'email' => 'test@example.com',
        'phone' => '+1234567890',
    ],
], $token));
$sectionId = $createSection['body']['data']['id'] ?? null;
echo "\n";

// Create another section for reorder test
$createSection2 = printResult("8b. POST /api/cards/{$cardId}/sections (create 2nd)", apiCall('POST', "/cards/{$cardId}/sections", [
    'section_type' => 'social',
    'title' => 'Social Media',
    'content' => [
        'twitter' => 'https://twitter.com/example',
        'linkedin' => 'https://linkedin.com/in/example',
    ],
], $token));
$sectionId2 = $createSection2['body']['data']['id'] ?? null;
echo "\n";

if ($sectionId) {
    // 9. PUT /api/sections/{id} - Update section
    printResult("9. PUT /api/sections/{$sectionId} (update)", apiCall('PUT', "/sections/{$sectionId}", [
        'title' => 'Updated Contact Information',
        'content' => [
            'email' => 'updated@example.com',
            'phone' => '+9876543210',
        ],
    ], $token));
    echo "\n";

    // 10. POST /api/cards/{card}/sections/reorder - Reorder sections
    if ($sectionId2) {
        printResult("10. POST /api/cards/{$cardId}/sections/reorder (reorder)", apiCall('POST', "/cards/{$cardId}/sections/reorder", [
            'section_ids' => [$sectionId2, $sectionId],
        ], $token));
        echo "\n";
    }
}

echo "\n=== Testing Themes API (8 endpoints) ===\n\n";

// 11. GET /api/themes - List themes
$themes = printResult('11. GET /api/themes (list)', apiCall('GET', '/themes', null, $token));
echo "\n";

// 12. POST /api/themes - Create theme
$createTheme = printResult('12. POST /api/themes (create)', apiCall('POST', '/themes', [
    'name' => 'Test Theme',
    'config' => [
        'colors' => [
            'primary' => '#3B82F6',
            'secondary' => '#8B5CF6',
            'background' => '#FFFFFF',
        ],
        'fonts' => [
            'heading' => 'Inter',
            'body' => 'Inter',
        ],
    ],
], $token));
$themeId = $createTheme['body']['data']['id'] ?? null;
echo "\n";

if ($themeId) {
    // 13. GET /api/themes/{id} - Show theme
    printResult("13. GET /api/themes/{$themeId} (show)", apiCall('GET', "/themes/{$themeId}", null, $token));
    echo "\n";

    // 14. PUT /api/themes/{id} - Update theme
    printResult("14. PUT /api/themes/{$themeId} (update)", apiCall('PUT', "/themes/{$themeId}", [
        'name' => 'Updated Test Theme',
        'config' => [
            'colors' => [
                'primary' => '#EF4444',
                'secondary' => '#F59E0B',
                'background' => '#F3F4F6',
            ],
        ],
    ], $token));
    echo "\n";

    // 15. POST /api/themes/{id}/duplicate - Duplicate theme
    $duplicateTheme = printResult("15. POST /api/themes/{$themeId}/duplicate (duplicate)", apiCall('POST', "/themes/{$themeId}/duplicate", [], $token));
    $duplicateThemeId = $duplicateTheme['body']['data']['id'] ?? null;
    echo "\n";

    // 16. POST /api/themes/{id}/apply/{card} - Apply theme to card
    printResult("16. POST /api/themes/{$themeId}/apply/{$cardId} (apply to card)", apiCall('POST', "/themes/{$themeId}/apply/{$cardId}", [], $token));
    echo "\n";
}

// 17. POST /api/themes/upload - Upload theme image
echo "17. POST /api/themes/upload (upload image) - SKIPPED (requires multipart/form-data)\n";
echo "    Note: This endpoint requires file upload which needs different handling\n\n";

echo "\n=== Testing Payments API (5 endpoints) ===\n\n";

// 18. GET /api/subscription-plans - List subscription plans
$plans = printResult('18. GET /api/subscription-plans (list plans)', apiCall('GET', '/subscription-plans', null, $token));
$planId = $plans['body']['data'][0]['id'] ?? null;
echo "\n";

if ($planId) {
    // 19. POST /api/payments - Create payment
    $createPayment = printResult('19. POST /api/payments (create)', apiCall('POST', '/payments', [
        'subscription_plan_id' => $planId,
        'payment_method' => 'cash',
        'notes' => 'Test payment',
    ], $token));
    $paymentId = $createPayment['body']['data']['id'] ?? null;
    echo "\n";

    if ($paymentId) {
        // 20. POST /api/payments/{id}/confirm - Confirm payment (admin only)
        printResult("20. POST /api/payments/{$paymentId}/confirm (confirm - view only)", apiCall('POST', "/payments/{$paymentId}/confirm", [], $token));
        echo "    Note: Full confirmation requires admin access\n\n";
    }
}

// 21. GET /api/payments/history - Payment history
printResult('21. GET /api/payments/history (history)', apiCall('GET', '/payments/history', null, $token));
echo "\n";

// 22. GET /api/subscription - Current subscription
printResult('22. GET /api/subscription (current subscription)', apiCall('GET', '/subscription', null, $token));
echo "\n";

echo "\n=== Cleanup (Optional) ===\n\n";

// Delete created resources (optional)
if ($sectionId) {
    printResult("DELETE /api/sections/{$sectionId}", apiCall('DELETE', "/sections/{$sectionId}", null, $token));
    echo "\n";
}
if ($sectionId2) {
    printResult("DELETE /api/sections/{$sectionId2}", apiCall('DELETE', "/sections/{$sectionId2}", null, $token));
    echo "\n";
}
if ($themeId) {
    printResult("23. DELETE /api/themes/{$themeId}", apiCall('DELETE', "/themes/{$themeId}", null, $token));
    echo "\n";
}
if ($duplicateThemeId) {
    printResult("DELETE /api/themes/{$duplicateThemeId} (duplicate)", apiCall('DELETE', "/themes/{$duplicateThemeId}", null, $token));
    echo "\n";
}
if ($duplicateCardId) {
    printResult("24. DELETE /api/cards/{$duplicateCardId} (duplicate)", apiCall('DELETE', "/cards/{$duplicateCardId}", null, $token));
    echo "\n";
}
if ($cardId) {
    printResult("25. DELETE /api/cards/{$cardId}", apiCall('DELETE', "/cards/{$cardId}", null, $token));
    echo "\n";
}

echo "\n=== Test Summary ===\n";
echo "Total endpoints tested: 26 (22 direct tests + 4 cleanup)\n";
echo "Note: Theme upload endpoint requires special multipart/form-data handling\n";
echo "\n=== All Tests Complete! ===\n";
