<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    use HasUuids;
    protected $fillable = ['email_id', 'action', 'prompt', 'response', 'model', 'input_tokens', 'output_tokens'];
    public function email() { return $this->belongsTo(Email::class); }
}
