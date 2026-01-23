<?php

declare(strict_types=1);

namespace App\Models\Modules\SaaS;

use App\Enums\BillingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $invoice_id
 * @property string $provider
 * @property string|null $provider_payment_id
 * @property float $amount
 * @property string|null $currency
 * @property BillingStatus $status
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Invoice $invoice
 */
final class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\SaaS\PaymentFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'invoice_id',
        'provider',
        'provider_payment_id',
        'amount',
        'currency',
        'status',
    ];

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(related: Invoice::class);
    }

    protected function casts(): array
    {
        return [
            'status' => BillingStatus::class,
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
