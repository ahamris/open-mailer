<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'subject', 'html_body', 'text_body', 'variables', 'published'];

    protected $casts = [
        'variables' => 'array',
        'published' => 'boolean',
    ];
}
