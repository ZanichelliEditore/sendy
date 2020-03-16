<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Repositories\OAuthAccessTokenRepository;

class ClearAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:accessToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete access tokens from database';

    /**
     * The repository to delete access token
     *
     * @var object
     */
    private $oauthAccessTokenRepository;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OAuthAccessTokenRepository $oauthAccessTokenRepository)
    {
        parent::__construct();
        $this->oauthAccessTokenRepository = $oauthAccessTokenRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $response = $this->oauthAccessTokenRepository->deleteOlder(now()->subDay()->format('Y-m-d\TH:i:s.u'));
        if ($response >= 0) {
            $this->info('Access token cancelled: ' . $response);
            return 0;
        }
        return 1;
    }
}