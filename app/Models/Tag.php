<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasUuids;
    protected $fillable = ['name', 'color'];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_tag');
    }
}
