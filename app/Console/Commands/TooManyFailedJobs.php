<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
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
    private $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FailedJobRepository $failedJobRepository, Client $client)
    {
        parent::__construct();
        $this->failedJobRepository = $failedJobRepository;
        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!config('services.slackk.notifications'))
            return 0;
        try {
            $failedJobsCount = $this->failedJobRepository->count();
            if ($failedJobsCount >= 1) {
                $message = $failedJobsCount == 1 ? "C'è una mail non spedita su Sendy" : "Ci sono $failedJobsCount email non spedite su Sendy";
                $this->client->post(
                    config('services.slack.notifications.channel'),
                    [
                        'body' => json_encode([
                            'channel' => config('services.slack.notifications.channel_name'),
                            'username' => config('services.slack.notifications.channel_username'),
                            'text' => ":alert_siren: $message :alert_siren:"
                        ])
                    ]
                );
            }
        } catch (ClientException $e) {
            Log::warning("Check failed jobs exception: " . $e->getCode() . " " . $e->getResponse()->getBody()->getContents());
        }

        return 0;
    }
}
