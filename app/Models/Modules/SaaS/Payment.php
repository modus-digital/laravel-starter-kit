<?php

declare(strict_types=1);

namespace App\Models\Modules\SaaS;

use App\Enums\BillingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
