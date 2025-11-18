<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    protected $fillable = [
        'query',
        'results_count',
        'provider',
        'results_raw',
    ];

    protected $casts = [
        'results_raw' => 'array',
    ];
}
