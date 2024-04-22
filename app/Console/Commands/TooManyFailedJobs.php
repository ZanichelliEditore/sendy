<?php

namespace App\Console\Commands;

use App\Notifications\SlackNotification;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use App\Http\Repositories\FailedJobRepository;

class TooManyFailedJobs extends Command
{
    use Notifiable;

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
    protected $description = 'Check if failedJobs are more than 100 and send a slack message';

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
        $failedJobsCount = $this->failedJobRepository->count();
        if ($failedJobsCount >= 100) {
            Notification::route('slack', config('notifications.SLACK_BOT_USER_DEFAULT_CHANNEL'))
                ->notify(new SlackNotification("There are failed jobs on SENDY"));
        }

        return 0;
    }
}
