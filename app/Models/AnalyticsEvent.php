<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'event_type',
        'query',
        'results_count',
        'provider',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
