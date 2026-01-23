<?php

declare(strict_types=1);

namespace App\Models\Modules\Clients;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $client_id
 * @property string $company
 * @property string $tax_number
 * @property string $vat_number
 * @property string $address
 * @property string $postal_code
 * @property string $city
 * @property string $country
 * @property string $billing_email
 * @property string $billing_phone
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Client $client
 */
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
