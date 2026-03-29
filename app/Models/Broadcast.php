<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasUuids;
    protected $fillable = ['name', 'template_id', 'audience_id', 'from_address', 'from_name', 'subject', 'html_body', 'status', 'total_recipients', 'sent_count', 'failed_count', 'sent_at'];
    protected $casts = ['sent_at' => 'datetime'];

    public function template() { return $this->belongsTo(Template::class); }
    public function audience() { return $this->belongsTo(Audience::class); }
}
