<?php

namespace App\Actions;

use App\Enums\MonitorStatus;
use App\Models\Monitor;
use App\Models\MonitorResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class CheckMonitorAction
{
    public function handle(Monitor $monitor): MonitorResult
    {
        $startedAt = hrtime(true);
        $statusCode = null;
        $errorMessage = null;
        $isSuccess = false;

        try {
            $pending = Http::timeout($monitor->timeout_seconds)
                ->withHeaders($monitor->headers ?? [])
                ->withOptions(['http_errors' => false]);

            $response = match ($monitor->method->value) {
                'GET' => $pending->get($monitor->url),
                'POST' => $pending->withBody($monitor->body ?? '', 'application/json')->post($monitor->url),
                'PUT' => $pending->withBody($monitor->body ?? '', 'application/json')->put($monitor->url),
                'PATCH' => $pending->withBody($monitor->body ?? '', 'application/json')->patch($monitor->url),
                'DELETE' => $pending->withBody($monitor->body ?? '', 'application/json')->delete($monitor->url),
                'HEAD' => $pending->head($monitor->url),
                default => $pending->get($monitor->url),
            };

            $statusCode = $response->status();
            $isSuccess = $statusCode === $monitor->expected_status_code;

            if (! $isSuccess) {
                $errorMessage = "Expected status {$monitor->expected_status_code}, got {$statusCode}";
            }
        } catch (ConnectionException $exception) {
            $errorMessage = $exception->getMessage();
        } catch (RequestException $exception) {
            $statusCode = $exception->response?->status();
            $errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }

        $responseTimeMs = (int) max(0, round((hrtime(true) - $startedAt) / 1_000_000));

        $result = $monitor->results()->create([
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'is_success' => $isSuccess,
            'error_message' => $errorMessage,
            'checked_at' => now(),
        ]);

        $monitor->update([
            'status' => $isSuccess ? MonitorStatus::Online : MonitorStatus::Offline,
            'last_checked_at' => $result->checked_at,
            'last_status_code' => $statusCode,
            'last_response_time_ms' => $responseTimeMs,
            'uptime_percentage' => $monitor->recalculateUptimePercentage(),
            'next_check_at' => $monitor->is_active
                ? now()->addMinutes($monitor->check_interval)
                : null,
        ]);

        return $result;
    }
}
