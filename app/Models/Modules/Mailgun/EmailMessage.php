<?php

declare(strict_types=1);

namespace App\Models\Modules\Mailgun;

use App\Enums\Modules\Mailgun\EmailStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string|null $tenant_id
 * @property string|null $mailable_class
 * @property string $subject
 * @property string $from_address
 * @property string|null $from_name
 * @property string $to_address
 * @property string|null $to_name
 * @property array|null $cc
 * @property array|null $bcc
 * @property array|null $tags
 * @property string|null $mailgun_message_id
 * @property string|null $correlation_id
 * @property EmailStatus $status
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmailEvent> $events
 */
final class EmailMessage extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Mailgun\EmailMessageFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'mailable_class',
        'subject',
        'from_address',
        'from_name',
        'to_address',
        'to_name',
        'cc',
        'bcc',
        'tags',
        'mailgun_message_id',
        'correlation_id',
        'status',
        'sent_at',
    ];

    /**
     * @return HasMany<EmailEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(related: EmailEvent::class)
            ->orderBy('occurred_at', 'desc');
    }

    /**
     * Get the latest event for this email message.
     */
    public function latestEvent(): ?EmailEvent
    {
        return $this->events()->latest('occurred_at')->first();
    }

    /**
     * Check if the email has been delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === EmailStatus::DELIVERED;
    }

    /**
     * Check if the email has failed.
     */
    public function hasFailed(): bool
    {
        return in_array($this->status, [
            EmailStatus::FAILED,
            EmailStatus::DROPPED,
            EmailStatus::BOUNCED,
        ], true);
    }

    /**
     * Scope to filter by status.
     *
     * @param  Collection<int, EmailStatus>|EmailStatus  $statuses
     */
    public function scopeWithStatus($query, Collection|EmailStatus $statuses): void
    {
        $statuses = $statuses instanceof Collection ? $statuses->toArray() : [$statuses];
        $values = array_map(fn (EmailStatus $status): string => $status->value, $statuses);

        $query->whereIn('status', $values);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeSentBetween($query, ?\Carbon\Carbon $start, ?\Carbon\Carbon $end): void
    {
        if ($start instanceof \Carbon\Carbon) {
            $query->where('sent_at', '>=', $start);
        }

        if ($end instanceof \Carbon\Carbon) {
            $query->where('sent_at', '<=', $end);
        }
    }

    /**
     * Scope to filter by recipient.
     */
    public function scopeForRecipient($query, string $email): void
    {
        $query->where('to_address', $email);
    }

    /**
     * Scope to filter by mailable class.
     */
    public function scopeForMailable($query, string $mailableClass): void
    {
        $query->where('mailable_class', $mailableClass);
    }

    protected function casts(): array
    {
        return [
            'status' => EmailStatus::class,
            'cc' => 'array',
            'bcc' => 'array',
            'tags' => 'array',
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
