<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorQueues extends Command
{
    protected $signature = 'queue:health-check';

    protected $description = 'Monitor queue health and status';

    public function handle()
    {
        $this->info('=== Queue Health Check ===');

        // Check pending jobs
        $pendingJobs = DB::table('jobs')->count();
        $this->info("Pending jobs: {$pendingJobs}");

        // Check failed jobs
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $this->warn("Failed jobs: {$failedJobs}");
        } else {
            $this->info("Failed jobs: {$failedJobs}");
        }

        // Check recent failed jobs
        $recentFailures = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHour())
            ->count();

        if ($recentFailures > 0) {
            $this->error("Recent failures (last hour): {$recentFailures}");
        }

        // Queue statistics
        $stats = [
            'translation_jobs' => DB::table('jobs')->where('queue', 'default')->count(),
            'total_processed' => DB::table('failed_jobs')->count() + DB::table('jobs')->count(),
        ];

        $this->table(
            ['Metric', 'Value'],
            [
                ['Translation Jobs in Queue', $stats['translation_jobs']],
                ['Total Jobs Processed', $stats['total_processed']],
            ]
        );

        // Recommendations
        if ($pendingJobs > 100) {
            $this->warn('⚠️  High number of pending jobs. Consider adding more workers.');
        }

        if ($failedJobs > 10) {
            $this->warn('⚠️  High number of failed jobs. Check logs and retry if needed.');
        }

        if ($recentFailures === 0 && $pendingJobs < 10) {
            $this->info('✅ Queue system is healthy!');
        }

        return 0;
    }
}
