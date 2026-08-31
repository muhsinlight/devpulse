---
description: Real-time Reverb & Echo guidelines for DevPulse
always_on: true
---

# Real-Time & WebSockets Guidelines

- **Laravel Reverb**:
    - Run broadcasting driver `reverb`.
    - Channel authorization rules in `routes/channels.php`.
    - Use private channels: `projects.{id}` and `webhooks.{id}` to enforce authorization.
- **Frontend Laravel Echo**:
    - Connect via `laravel-echo` + `pusher-js` targeting local/VPS Reverb host and port.
    - In Vue components, listen on private channels on mount and cleanup listeners on unmount (`onBeforeUnmount`).
    - Animate new incoming items (highlight new row / trigger audio chime or badge pulse).
