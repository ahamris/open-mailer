<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SubscriptionForm extends Model
{
    use HasUuids;
    protected $fillable = ['name', 'audience_id', 'double_opt_in', 'redirect_url', 'confirmation_subject', 'confirmation_html', 'welcome_html', 'active', 'submissions_count'];
    protected $casts = ['double_opt_in' => 'boolean', 'active' => 'boolean'];

    public function audience() { return $this->belongsTo(Audience::class); }
}
