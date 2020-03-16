<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class FailedJob extends Model
{
    protected $timestamp = false;
    protected $collection = 'failed_jobs';
}
