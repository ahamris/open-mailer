<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['name', 'key_hash', 'key_prefix', 'permission', 'domain_restriction', 'last_used_at'];
    protected $hidden = ['key_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public static function generate(string $name, string $permission = 'full_access'): array
    {
        $rawKey = 'clom_' . Str::random(40);

        $apiKey = static::create([
            'name' => $name,
            'key_hash' => hash('sha256', $rawKey),
            'key_prefix' => substr($rawKey, 0, 12),
            'permission' => $permission,
        ]);

        return ['api_key' => $apiKey, 'raw_key' => $rawKey];
    }

    public static function findByRawKey(string $rawKey): ?static
    {
        return static::where('key_hash', hash('sha256', $rawKey))->first();
    }

    public function emails()
    {
        return $this->hasMany(Email::class);
    }
}
