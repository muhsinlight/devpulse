---
description: Core Architecture Rules and Domain Design for DevPulse
always_on: true
---

# DevPulse Architecture Guidelines

## Domain Architecture

- **Projects**: Core multi-tenant container. Everything (Monitors, Webhooks, Incidents) belongs to a `Project`, which belongs to a `User`.
- **Monitors**:
    - Encapsulate health checks against external URLs.
    - Periodic executions are handled asynchronously via Redis Queue (`CheckMonitorJob`).
    - Results are appended to `monitor_results` for time-series analytics (uptime %, latency ms).
- **Webhooks**:
    - Ingestion endpoint: `/hooks/{token}`.
    - Ingested payloads are logged to `webhook_requests` and broadcast immediately via Laravel Reverb on private project/webhook WebSocket channels.
- **Incidents & Notifications**:
    - Monitor transitions (`online` -> `offline` or `offline` -> `recovered`) trigger incident records and user notifications.

## Layering & Separation of Concerns

- **Controllers**: Keep thin. Delegate business logic, calculations, and external HTTP orchestration to Action or Service classes (e.g. `App\Services\MonitorService`, `App\Actions\CheckMonitorAction`).
- **Jobs**: Keep jobs atomic and idempotent. Use Redis queues for `CheckMonitorJob` and `ProcessWebhookJob`.
- **Events**: Use typed Laravel Events implementing `ShouldBroadcast` for real-time capabilities.
