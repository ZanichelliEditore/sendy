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
        $failedJobsCount = $this->failedJobRepository->count();
        if ($failedJobsCount >= 1) {

            try {
                $this->client->post(
                    env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
                    [
                        'body' => json_encode([
                            'channel' => env('SLACK_CHANNEL_NAME'),
                            'username' => env('SLACK_CHANNEL_USERNAME'),
                            'text' => $failedJobsCount == 1 ? ":alert_siren: c'e' " . $failedJobsCount . " email non spedita su Sendy :alert_siren: " : ":alert_siren: Ci sono " . $failedJobsCount . " email non spedite su Sendy :alert_siren: "
                        ])
                    ]
                );
            } catch (ClientException $e) {
                Log::warning("MyZanichelliService login exception: " . $e->getCode() . " " . $e->getResponse()->getBody()->getContents());
            }
        }

        return 0;
    }
}
