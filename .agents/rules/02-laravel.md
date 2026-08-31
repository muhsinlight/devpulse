---
description: Laravel and PHP standards for DevPulse
always_on: true
---

# Laravel & PHP Standards

- **PHP 8.4**:
    - Always use constructor property promotion: `public function __construct(public MonitorService $monitorService) {}`
    - Always declare explicit parameter types and return types on all methods.
    - Use Enums for statuses (`MonitorStatus`, `HttpMethod`, `IncidentType`).
- **Artisan & Migrations**:
    - Use `php artisan make:` commands with `--no-interaction`.
    - Always index foreign keys and query columns: `(project_id, is_active)`, `(monitor_id, checked_at)`, `token`.
- **Testing**:
    - Write Pest tests with `php artisan make:test --pest {NameTest}`.
    - Feature tests for controllers, jobs, and webhook receivers.
- **Code Style**:
    - Follow Laravel Pint formatting.
