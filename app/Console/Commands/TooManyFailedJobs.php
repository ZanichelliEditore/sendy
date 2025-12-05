<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Repositories\FailedJobRepository;
use App\Utils\FailedJobsUtils;

class TooManyFailedJobs extends Command
{
    use FailedJobsUtils;

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
        if (!config('logging.channels.slack.url')) return 0;

        try {
            $failedJobsCount = $this->failedJobRepository->count();

            if (!$failedJobsCount) {
                $this->info("It'a all right");
                return 0;
            }

            if ($failedJobsCount == $this->getFailedJobsCache()) {
                $this->info("$failedJobsCount failed jobs - skip notification");
                return 0;
            }

            $this->setFailedJobsCache($failedJobsCount);

            $message = $failedJobsCount == 1 ? "C'è una mail non spedita su Sendy" : "Ci sono $failedJobsCount email non spedite su Sendy";
            Log::channel('slack')->error(":alert_siren: $message :alert_siren:");
            $this->info("Notification on slack sent");

            return 0;
        } catch (\Exception $e) {
            Log::warning("Check failed jobs exception: " . $e->getCode() . " " . $e->getMessage());
            return 1;
        }
    }
}
