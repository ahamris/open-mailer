<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'status', 'spf_valid', 'dkim_valid', 'dmarc_valid', 'mx_valid',
        'dkim_selector', 'dkim_public_key', 'dkim_private_key', 'dns_records', 'verified_at',
    ];

    protected $casts = [
        'spf_valid' => 'boolean', 'dkim_valid' => 'boolean',
        'dmarc_valid' => 'boolean', 'mx_valid' => 'boolean',
        'dns_records' => 'array', 'verified_at' => 'datetime',
    ];

    protected $hidden = ['dkim_private_key'];
}
