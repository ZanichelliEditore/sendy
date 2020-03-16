<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class FailedJob extends Model
{
    protected $collection = 'failed_jobs';
}
