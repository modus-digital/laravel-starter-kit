<?php

declare(strict_types=1);

namespace App\Models\Modules\Mailgun;

use App\Enums\Modules\Mailgun\EmailEventType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $email_message_id
 * @property EmailEventType $event_type
 * @property string|null $mailgun_event_id
 * @property string|null $severity
 * @property string|null $reason
 * @property string|null $recipient
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $url
 * @property array|null $raw_payload
 * @property \Carbon\Carbon|null $occurred_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read EmailMessage $emailMessage
 */
final class EmailEvent extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Mailgun\EmailEventFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'email_message_id',
        'event_type',
        'mailgun_event_id',
        'severity',
        'reason',
        'recipient',
        'ip_address',
        'user_agent',
        'url',
        'raw_payload',
        'occurred_at',
    ];

    /**
     * @return BelongsTo<EmailMessage, $this>
     */
    public function emailMessage(): BelongsTo
    {
        return $this->belongsTo(related: EmailMessage::class);
    }

    /**
     * Scope to filter by event type.
     *
     * @param  \Illuminate\Support\Collection<int, EmailEventType>|EmailEventType  $eventTypes
     */
    public function scopeOfType($query, \Illuminate\Support\Collection|EmailEventType $eventTypes): void
    {
        $eventTypes = $eventTypes instanceof \Illuminate\Support\Collection ? $eventTypes->toArray() : [$eventTypes];
        $values = array_map(fn (EmailEventType $type): string => $type->value, $eventTypes);

        $query->whereIn('event_type', $values);
    }

    protected function casts(): array
    {
        return [
            'event_type' => EmailEventType::class,
            'raw_payload' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
