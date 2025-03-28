<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProcessEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process-emails {--timeout=60 : Time limit in seconds} {--tries=3 : Number of attempts}'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the email queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timeout = $this->option('timeout');
        $tries = $this->option('tries');
        
        $this->info('Starting email queue processing...');
        
        try {
            $this->info("Processing 'emails' queue with timeout of {$timeout}s and {$tries} attempts.");
            
            // Execute the queue worker specifically for the email queue
            $exitCode = Artisan::call('queue:work', [
                '--queue' => 'emails',
                '--timeout' => $timeout,
                '--tries' => $tries,
                '--stop-when-empty' => true,
                '--memory' => 128,
            ]);
            
            $this->info('Email queue processing completed.');
            $this->info('Exit code: ' . $exitCode);
            
            return $exitCode;
        } catch (\Exception $e) {
            $this->error('Error processing email queue: ' . $e->getMessage());
            Log::error('Error processing email queue: ' . $e->getMessage());
            return 1;
        }
    }
}
