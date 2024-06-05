<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Repositories\FailedJobRepository;

class TooManyFailedJobs extends Command
{

    /** 
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:failed-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if failedJobs are more or equal to 1 and send a slack message';

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
        if (!config('logging.channels.slack.url'))
            return 0;
        try {
            $failedJobsCount = $this->failedJobRepository->count();
            if ($failedJobsCount >= 1) {
                $message = $failedJobsCount == 1 ? "C'è una mail non spedita su Sendy" : "Ci sono $failedJobsCount email non spedite su Sendy";
                Log::channel('slack')->error(":alert_siren: $message :alert_siren:");
                $this->info("Notification on slack sent");
            } else {
                $this->info("It'a all right");
            }
        } catch (\Exception $e) {
            Log::warning("Check failed jobs exception: " . $e->getCode() . " " . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
