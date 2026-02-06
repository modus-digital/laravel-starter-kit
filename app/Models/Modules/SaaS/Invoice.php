<?php

declare(strict_types=1);

namespace App\Models\Modules\SaaS;

use App\Enums\BillingStatus;
use App\Models\Modules\Clients\Client;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $invoice_id
 * @property string $client_id
 * @property string|null $subscription_id
 * @property float $total
 * @property string|null $currency
 * @property BillingStatus $status
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Client $client
 * @property-read Subscription|null $subscription
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 */
final class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\SaaS\InvoiceFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'invoice_id',
        'client_id',
        'subscription_id',
        'total',
        'currency',
        'status',
    ];

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(related: Client::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(related: Subscription::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(related: Payment::class);
    }

    protected function casts(): array
    {
        return [
            'status' => BillingStatus::class,
            'paid_at' => 'datetime',
            'due_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
