<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessQueueBatch extends Command
{
    protected $signature = 'queue:process-batch {--jobs=5 : Number of jobs to process}';
    protected $description = 'Process a batch of queue jobs (for shared hosting)';

    public function handle()
    {
        $jobCount = $this->option('jobs');
        $processed = 0;
        
        $this->info("Processing up to {$jobCount} queue jobs...");
        
        // Process jobs one by one
        for ($i = 0; $i < $jobCount; $i++) {
            try {
                // Process one job with timeout
                $result = $this->call('queue:work', [
                    'connection' => 'database',
                    '--once' => true,
                    '--timeout' => 60,
                ]);
                
                if ($result === 0) {
                    $processed++;
                    $this->info("✅ Processed job " . ($i + 1));
                } else {
                    // No more jobs to process
                    break;
                }
            } catch (\Exception $e) {
                $this->error("❌ Error processing job: " . $e->getMessage());
                break;
            }
        }
        
        $this->info("Processed {$processed} jobs");
        
        // Show queue status
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        
        $this->table(
            ['Status', 'Count'],
            [
                ['Pending Jobs', $pending],
                ['Failed Jobs', $failed],
                ['Processed This Batch', $processed],
            ]
        );
        
        return 0;
    }
}