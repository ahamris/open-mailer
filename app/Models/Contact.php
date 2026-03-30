<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['email', 'first_name', 'last_name', 'unsubscribed', 'confirmed', 'confirmation_token', 'confirmed_at', 'metadata'];
    protected $casts = ['unsubscribed' => 'boolean', 'confirmed' => 'boolean', 'confirmed_at' => 'datetime', 'metadata' => 'array'];

    public function audiences() { return $this->belongsToMany(Audience::class, 'audience_contact')->withTimestamps(); }
    public function tags() { return $this->belongsToMany(Tag::class, 'contact_tag'); }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->email;
    }
}
