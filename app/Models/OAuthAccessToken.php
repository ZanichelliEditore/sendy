<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class OAuthAccessToken extends Model
{
    protected $collection = 'oauth_access_tokens';
}
