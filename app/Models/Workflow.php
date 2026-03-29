<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasUuids;
    protected $fillable = ['name', 'description', 'active', 'priority', 'triggers', 'actions', 'times_triggered', 'last_triggered_at'];
    protected $casts = ['active' => 'boolean', 'triggers' => 'array', 'actions' => 'array', 'last_triggered_at' => 'datetime'];

    public function logs() { return $this->hasMany(WorkflowLog::class); }
}
