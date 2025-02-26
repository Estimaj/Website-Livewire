<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'type',
        'user_ip',
        'metadata',
        'user_agent',
    ];
}
