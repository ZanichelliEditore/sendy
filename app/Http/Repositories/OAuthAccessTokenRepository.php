<?php
namespace App\Http\Repositories;

use App\Models\OAuthAccessToken;

class OAuthAccessTokenRepository
{
    /**
     * Delete a access tokens from the database
     *
     * @param  DateTime $dateTime
     * @return Response
     *
     */
    public function deleteOlder($dateTime)
    {        
        return OAuthAccessToken::where('created_at', '<', $dateTime)->delete();
    }
}
