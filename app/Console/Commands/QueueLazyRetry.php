<?php

namespace App\Console\Commands;

use App\Http\Repositories\FailedJobRepository;
use Illuminate\Console\Command;

class QueueLazyRetry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:lazy-retry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry 50 failed jobs every 30 seconds';

    private $failedJobRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FailedJobRepository $failedJobRepository)
    {
        parent::__construct();
        $this->failedJobRepository = $failedJobRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $jobsBatches = $this->failedJobRepository->all()->pluck("id")->chunk(50);
        foreach ($jobsBatches as $batch) {
            $this->callSilently('queue:retry', ["--range" => $batch->first() . "-" . $batch->last()]);
            if ($batch != $jobsBatches->last()) sleep(30);
        }

        return 0;
    }
}
