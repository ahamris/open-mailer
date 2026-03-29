<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Mailbox extends Model
{
    use HasUuids;
    protected $fillable = ['email', 'name', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'imap_host', 'imap_port', 'signature_html', 'active'];
    protected $hidden = ['smtp_password'];
    protected $casts = ['active' => 'boolean', 'smtp_password' => 'encrypted'];

    public function emails() { return $this->hasMany(Email::class); }
}
