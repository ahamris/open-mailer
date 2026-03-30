<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DripStep extends Model
{
    use HasUuids;
    protected $fillable = ['drip_campaign_id', 'position', 'type', 'subject', 'html_body', 'template_id', 'delay_days', 'delay_hours', 'condition_field', 'condition_value', 'sent_count', 'opens_count', 'clicks_count'];

    public function campaign() { return $this->belongsTo(DripCampaign::class, 'drip_campaign_id'); }
    public function template() { return $this->belongsTo(Template::class); }
}
