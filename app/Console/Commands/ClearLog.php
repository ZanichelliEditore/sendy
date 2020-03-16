<?php

namespace App\Console\Commands;

use Exception;
use DirectoryIterator;
use Illuminate\Console\Command;

class ClearLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:log {--file=  : The single file to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete logs files from storage';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            if (!$this->option('file')) {
                $dir_logs = new DirectoryIterator(storage_path('logs'));

                foreach ($dir_logs as $log) {
                    $logFile = storage_path('logs') . '/' . $log;
                    if (pathinfo($log, PATHINFO_EXTENSION) == 'log') {
                        if ($log == "scheduler.log") {
                            exec('echo "" > ' . $logFile);
                        } else {
                            exec('rm ' . $logFile);
                        }
                    }
                }
            } else {
                $logFile = storage_path('logs') . '/' . $this->option('file');
                if (file_exists($logFile)) {
                    exec('echo "" > ' . $logFile);
                    $this->info('Logs have been cleared');
                } else {
                    $this->info("No file log " . $logFile);
                }
            }
            return 0;
        } catch (Exception $e) {
            $message = ($this->option('v')) ? ': ' . $e->getMessage() : ', please add --v for more details';
            $this->error("An error occurred" . $message);
            return 1;
        }
    }
}
