<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'api_key_id', 'mailbox_id', 'thread_id', 'parent_id',
        'direction', 'status', 'is_read', 'is_starred', 'folder',
        'from_address', 'from_name', 'to_addresses', 'cc_addresses', 'bcc_addresses', 'reply_to',
        'subject', 'html_body', 'text_body', 'headers', 'tags', 'metadata',
        'message_id', 'scheduled_at', 'sent_at', 'delivered_at',
        'bounced_at', 'bounce_reason', 'idempotency_key',
        'spf_result', 'dkim_result', 'dmarc_result', 'sender_ip',
    ];

    protected $casts = [
        'to_addresses' => 'array', 'cc_addresses' => 'array', 'bcc_addresses' => 'array',
        'reply_to' => 'array', 'headers' => 'array', 'tags' => 'array', 'metadata' => 'array',
        'scheduled_at' => 'datetime', 'sent_at' => 'datetime', 'delivered_at' => 'datetime', 'bounced_at' => 'datetime',
        'is_read' => 'boolean', 'is_starred' => 'boolean',
    ];

    public function apiKey() { return $this->belongsTo(ApiKey::class); }
    public function mailbox() { return $this->belongsTo(Mailbox::class); }
    public function attachments() { return $this->hasMany(Attachment::class); }
    public function parent() { return $this->belongsTo(Email::class, 'parent_id'); }
    public function replies() { return $this->hasMany(Email::class, 'parent_id'); }
    public function aiConversations() { return $this->hasMany(AiConversation::class); }
}
