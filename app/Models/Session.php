<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Agent\Agent;

/**
 * The Session model represents a user's active session in the application.
 * It provides methods to access session information, expiration status, and device details.
 * This model is linked to the 'sessions' table which is used by Laravel's database session driver.
 *
 * @property string $id Unique identifier for the session
 * @property int $user_id Foreign key to the users table
 * @property string $ip_address IP address where the session originated
 * @property string $user_agent User agent string from the browser
 * @property int $last_activity Timestamp of the last activity
 * @property-read \App\Models\User $user The user that owns the session
 * @property-read array $session_info Information about the session
 * @property-read string $expires_at When the session will expire
 * @property-read bool $is_expired Whether the session has expired
 */
class Session extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'sessions';

    /**
     * The attributes that should be cast.
     * 
     * @var array
     */
    protected $casts = [
        'id' => 'string',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
        );
    }

    /**
     * Get the session information.
     * 
     * @return Attribute<array>
     */
    public function sessionInfo(): Attribute
    {
        $agent = $this->createAgent($this);

        return Attribute::make(
            get: fn () => [
                'id' => $this->id,
                'device' => [
                    'browser' => $agent->browser(),
                    'platform' => $agent->platform(),
                    'is_desktop' => $agent->isDesktop(),
                    'is_mobile' => $agent->isMobile(),
                    'is_tablet' => $agent->isTablet(), 
                ],
                'ip_address' => $this->ip_address,
                'expires_at' => $this->expires_at,
                'is_expired' => $this->is_expired,
                'last_activity' => $this->last_activity,
                'is_current_device' => $this->id === request()->session()->getId(),
            ]
        );
    }

    /**
     * Get the session expiration date.
     * 
     * @return Attribute<string>
     */
    public function expiresAt(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::createFromTimestamp($this->last_activity)
                ->addMinutes(config('session.lifetime'))
                ->toDateTimeString()
        );
    }

    /**
     * Determine if the session has expired.
     * 
     * @return Attribute<bool>
     */
    public function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expires_at < now()
        );
    }

    /**
     * Create an Agent instance from the session.
     * This is used to retrieve information about the user's device.
     * 
     * @param  mixed  $session
     * @return Agent
     */
    private function createAgent(mixed $session)
    {
        return tap(
            value: new Agent(),
            callback: fn ($agent) => $agent->setUserAgent(userAgent: $session->user_agent)
        );
    }
}
