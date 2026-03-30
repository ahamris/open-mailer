<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Suppression extends Model
{
    use HasUuids;
    protected $fillable = ['email', 'reason', 'note'];

    public static function isSuppressed(string $email): bool
    {
        return static::where('email', strtolower($email))->exists();
    }
}
