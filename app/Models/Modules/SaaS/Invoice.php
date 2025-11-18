<?php

namespace App\Models\Modules\SaaS;

use App\Enums\BillingStatus;
use App\Models\Modules\Clients\Client;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\SaaS\InvoiceFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'invoice_id',
        'client_id',
        'subscription_id',
        'total',
        'currency',
        'status',
    ];

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(related: Client::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(related: Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(related: Payment::class);
    }
}
