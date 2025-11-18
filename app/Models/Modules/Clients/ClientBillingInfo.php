<?php

namespace App\Models\Modules\Clients;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientBillingInfo extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Clients\ClientBillingInfoFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'client_id',
        'company',
        'tax_number',
        'vat_number',
        'address',
        'postal_code',
        'city',
        'country',
        'billing_email',
        'billing_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(related: Client::class);
    }
}
