<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\WebhookEndpointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property string $token
 * @property bool $is_active
 * @property bool $hmac_required
 * @property string|null $hmac_secret
 * @property CarbonImmutable|null $last_received_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Project $project
 * @property-read Collection<int, WebhookRequest> $requests
 */
#[Fillable(['name', 'token', 'is_active', 'hmac_required', 'hmac_secret', 'last_received_at'])]
#[Hidden(['hmac_secret'])]
class WebhookEndpoint extends Model
{
    /** @use HasFactory<WebhookEndpointFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'hmac_required' => 'boolean',
            'hmac_secret' => 'encrypted',
            'last_received_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WebhookEndpoint $endpoint): void {
            if (blank($endpoint->token)) {
                $endpoint->token = static::generateToken();
            }
        });

        static::saving(function (WebhookEndpoint $endpoint): void {
            if ($endpoint->hmac_required && blank($endpoint->hmac_secret)) {
                $endpoint->hmac_secret = static::generateHmacSecret();
            }
        });
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public static function generateHmacSecret(): string
    {
        return Str::random(48);
    }

    public function rotateToken(): void
    {
        $this->update([
            'token' => static::generateToken(),
        ]);
    }

    public function rotateHmacSecret(): void
    {
        $this->update([
            'hmac_secret' => static::generateHmacSecret(),
            'hmac_required' => true,
        ]);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<WebhookRequest, $this>
     */
    public function requests(): HasMany
    {
        return $this->hasMany(WebhookRequest::class);
    }
}
