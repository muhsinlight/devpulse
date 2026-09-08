<?php

namespace App\Models;

use App\Enums\IncidentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $monitor_id
 * @property IncidentStatus $status
 * @property CarbonImmutable $opened_at
 * @property CarbonImmutable|null $resolved_at
 * @property string|null $last_error_message
 * @property int|null $opened_status_code
 * @property int|null $resolved_status_code
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Monitor $monitor
 */
#[Fillable([
    'status',
    'opened_at',
    'resolved_at',
    'last_error_message',
    'opened_status_code',
    'resolved_status_code',
])]
class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => IncidentStatus::class,
            'opened_at' => 'datetime',
            'resolved_at' => 'datetime',
            'opened_status_code' => 'integer',
            'resolved_status_code' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Monitor, $this>
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    /**
     * @param  Builder<Incident>  $query
     * @return Builder<Incident>
     */
    #[Scope]
    protected function open(Builder $query): Builder
    {
        return $query->where('status', IncidentStatus::Open);
    }
}
