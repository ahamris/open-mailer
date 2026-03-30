<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DripCampaign extends Model
{
    use HasUuids;
    protected $fillable = ['name', 'description', 'audience_id', 'trigger_type', 'trigger_value', 'from_address', 'from_name', 'active', 'enrolled_count', 'completed_count'];
    protected $casts = ['active' => 'boolean'];

    public function audience() { return $this->belongsTo(Audience::class); }
    public function steps() { return $this->hasMany(DripStep::class)->orderBy('position'); }
    public function enrollments() { return $this->hasMany(DripEnrollment::class); }
}
