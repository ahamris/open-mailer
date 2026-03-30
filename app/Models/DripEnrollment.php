<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DripEnrollment extends Model
{
    use HasUuids;
    protected $fillable = ['drip_campaign_id', 'contact_id', 'current_step', 'status', 'next_action_at'];
    protected $casts = ['next_action_at' => 'datetime'];

    public function campaign() { return $this->belongsTo(DripCampaign::class, 'drip_campaign_id'); }
    public function contact() { return $this->belongsTo(Contact::class); }
}
