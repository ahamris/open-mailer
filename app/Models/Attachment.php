<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasUuids;

    protected $fillable = ['email_id', 'filename', 'content_type', 'size', 'storage_path', 'content_id'];

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
