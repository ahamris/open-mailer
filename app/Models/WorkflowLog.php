<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowLog extends Model
{
    protected $fillable = ['workflow_id', 'email_id', 'action', 'status', 'result'];
    public function workflow() { return $this->belongsTo(Workflow::class); }
    public function email() { return $this->belongsTo(Email::class); }
}
