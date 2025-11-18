<?php

namespace App\Models\Modules\SaaS;

use App\Enums\BillingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\SaaS\PaymentFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'invoice_id',
        'provider',
        'provider_payment_id',
        'amount',
        'currency',
        'status',
    ];

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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(related: Invoice::class);
    }
}
