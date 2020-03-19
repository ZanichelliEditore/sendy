<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Http\Repositories\OAuthAccessTokenRepository;

class JobController extends Controller
{

    private $oauthAccessTokenRepository;

    public function __construct(OAuthAccessTokenRepository $oauthAccessTokenRepository)
    {
        $this->oauthAccessTokenRepository = $oauthAccessTokenRepository;
    }

    /**
     * @return Respone
     */
    public function getFile()
    {
        $contentFile = $this->tail(storage_path('logs/worker.log'));
        if (empty($contentFile)) {
            return response()->json([], 204);
        }
        return response()->json(['contentFile' => $contentFile]);
    }

    public function deleteLogs()
    {
        $resultCommand = Artisan::call('clear:log');
        if ($resultCommand == 0) {
            return response()->json();
        } else {
            return response()->error500(__('messages.DeleteLogError'));
        }
    }

    public function deleteTokens()
    {
        $result = $this->oauthAccessTokenRepository->deleteOlder(Carbon::now()->format('Y-m-d\TH:i:s.u'));
        if ($result >= 0) {
            return response()->json(['cancelled' => $result]);
        }
        return response()->error500(__('messages.DeleteAccessTokenError'));
    }

    /**
     * @param string $filename
     * @param int $lines
     * @param int $buffer
     * @return array
     */
    private function tail($filename, $lines = 10, $buffer = 4096)
    {
        // Open the file
        $f = fopen($filename, "rb");

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines -= 1;

        $output = $chunk = '';

        // // While we would like more
        while (ftell($f) > 1 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }
        if (!empty($output)) {
            $output = explode("\n", $output);
            array_pop($output);
        }
        // Close file and return
        fclose($f);
        return $output;
    }
}
