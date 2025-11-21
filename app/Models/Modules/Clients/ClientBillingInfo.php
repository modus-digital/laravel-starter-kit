<?php

declare(strict_types=1);

namespace App\Models\Modules\Clients;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ClientBillingInfo extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Clients\ClientBillingInfoFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

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

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(related: Client::class);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
