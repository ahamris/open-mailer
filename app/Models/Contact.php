<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasUuids;

    protected $fillable = ['email', 'first_name', 'last_name', 'unsubscribed', 'metadata'];

    protected $casts = [
        'unsubscribed' => 'boolean',
        'metadata' => 'array',
    ];
}
