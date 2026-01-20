<?php

use App\Models\User;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

// Create or get test user
$user = User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test User',
        'password' => bcrypt('password'),
        'email_verified_at' => null,
    ]
);

echo "Testing email for: {$user->email}\n";
echo "-----------------------------------\n";

// Send verification email
echo "1. Sending email verification...\n";
$user->sendEmailVerificationNotification();
echo "✓ Queued!\n\n";

// Check queue
$jobs = DB::table('jobs')->count();
echo "Jobs in queue: {$jobs}\n";

echo "\nThe queue worker should now process this email and send it via Gmail SMTP.\n";
echo "Check storage/logs/laravel.log for the email content.\n";
