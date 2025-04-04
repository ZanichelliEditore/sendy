<?php

namespace App\Http\Repositories;

use App\Models\OAuthAccessToken;

class OAuthAccessTokenRepository
{
    /**
     * Delete a access tokens from the database
     */
    public function deleteOlder(string $dateTime): bool|null
    {
        return OAuthAccessToken::where('created_at', '<', $dateTime)->delete();
    }
}
