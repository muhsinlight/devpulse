<?php

namespace App\Models;

use App\Enums\HttpMethod;
use App\Enums\MonitorStatus;
use Carbon\CarbonImmutable;
use Database\Factories\MonitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property string $url
 * @property HttpMethod $method
 * @property array<string, string>|null $headers
 * @property string|null $body
 * @property int $check_interval
 * @property int $expected_status_code
 * @property int $timeout_seconds
 * @property MonitorStatus $status
 * @property CarbonImmutable|null $last_checked_at
 * @property int|null $last_status_code
 * @property int|null $last_response_time_ms
 * @property string $uptime_percentage
 * @property bool $is_active
 * @property CarbonImmutable|null $next_check_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Project $project
 * @property-read Collection<int, MonitorResult> $results
 */
#[Fillable([
    'name',
    'url',
    'method',
    'headers',
    'body',
    'check_interval',
    'expected_status_code',
    'timeout_seconds',
    'status',
    'last_checked_at',
    'last_status_code',
    'last_response_time_ms',
    'uptime_percentage',
    'is_active',
    'next_check_at',
])]
class Monitor extends Model
{
    /** @use HasFactory<MonitorFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => HttpMethod::class,
            'headers' => 'array',
            'check_interval' => 'integer',
            'expected_status_code' => 'integer',
            'timeout_seconds' => 'integer',
            'status' => MonitorStatus::class,
            'last_checked_at' => 'datetime',
            'last_status_code' => 'integer',
            'last_response_time_ms' => 'integer',
            'uptime_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'next_check_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<MonitorResult, $this>
     */
    public function results(): HasMany
    {
        return $this->hasMany(MonitorResult::class);
    }

    /**
     * @param  Builder<Monitor>  $query
     * @return Builder<Monitor>
     */
    #[Scope]
    protected function due(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('next_check_at')
            ->where('next_check_at', '<=', now());
    }

    public function scheduleNextCheck(): void
    {
        $this->update([
            'next_check_at' => now()->addMinutes($this->check_interval),
        ]);
    }

    public function recalculateUptimePercentage(): float
    {
        $total = $this->results()->count();

        if ($total === 0) {
            return 100.0;
        }

        $successful = $this->results()->where('is_success', true)->count();

        return round(($successful / $total) * 100, 2);
    }
}
