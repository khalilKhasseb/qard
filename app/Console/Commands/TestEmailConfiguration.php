<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestEmailConfiguration extends Command
{
    protected $signature = 'email:test {--to=admin@example.com : Email address to send test to}';

    protected $description = 'Test email configuration and send a test email';

    public function handle()
    {
        $this->info('🧪 Testing email configuration...');

        // Check basic email configuration
        $this->checkEmailConfig();

        // Test SMTP connection
        $this->testSmtpConnection();

        // Send test email
        $this->sendTestEmail();

        return 0;
    }

    private function checkEmailConfig()
    {
        $this->info('📧 Email Configuration:');

        $config = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name'),
        ];

        foreach ($config as $key => $value) {
            if (str_contains($key, 'PASSWORD')) {
                $value = $value ? '***HIDDEN***' : 'NOT SET';
            }

            $status = $value ? '✅' : '❌';
            $this->line("{$status} {$key}: {$value}");
        }
    }

    private function testSmtpConnection()
    {
        $this->info('🔌 Testing SMTP connection...');

        try {
            // Laravel 12 uses Symfony Mailer - test with actual email send
            $this->info('ℹ️  Testing connection by sending test email...');
        } catch (\Exception $e) {
            $this->error('❌ SMTP connection failed: '.$e->getMessage());
        }
    }

    private function sendTestEmail()
    {
        $to = $this->option('to');
        $this->info("📤 Sending test email to: {$to}");

        try {
            Mail::raw('This is a test email from QCard application.', function ($message) use ($to) {
                $message->to($to)
                    ->subject('QCard Test Email - '.now()->format('Y-m-d H:i:s'));
            });

            $this->info('✅ Test email sent successfully');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email: '.$e->getMessage());

            // Log the error for debugging
            Log::error('Email test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
