<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailEvent extends Model
{
    protected $fillable = ['email_id', 'type', 'url', 'ip', 'user_agent'];

    public function email() { return $this->belongsTo(Email::class); }
}
