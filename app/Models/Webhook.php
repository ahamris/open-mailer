<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasUuids;

    protected $fillable = ['url', 'events', 'secret', 'active'];

    protected $casts = [
        'events' => 'array',
        'active' => 'boolean',
    ];
}
