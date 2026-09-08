<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\WebhookRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $webhook_endpoint_id
 * @property string|null $ip_address
 * @property string|null $ip_iso_code
 * @property string|null $ip_country
 * @property string|null $ip_city
 * @property string $method
 * @property array<string, mixed> $headers
 * @property array<string, mixed>|null $query_params
 * @property array<string, mixed>|null $payload
 * @property string|null $raw_body
 * @property string|null $content_type
 * @property CarbonImmutable $received_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read WebhookEndpoint $endpoint
 */
#[Fillable([
    'ip_address',
    'ip_iso_code',
    'ip_country',
    'ip_city',
    'method',
    'headers',
    'query_params',
    'payload',
    'raw_body',
    'content_type',
    'received_at',
])]
class WebhookRequest extends Model
{
    /** @use HasFactory<WebhookRequestFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'query_params' => 'array',
            'payload' => 'array',
            'received_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WebhookEndpoint, $this>
     */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
