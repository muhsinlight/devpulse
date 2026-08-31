---
description: Frontend guidelines for Vue 3, Inertia.js v3, Tailwind CSS, and DaisyUI
always_on: true
---

# Frontend Guidelines

- **Framework**: Vue 3 with `<script setup lang="ts">`.
- **Inertia v3**:
    - Always have a single root element in Vue templates.
    - Use Wayfinder route generation: `import { index, show } from '@/actions/...'` or `@/routes/...`.
    - Use deferred props with skeleton loaders for high-latency data.
- **UI & Styling**:
    - Tailwind CSS v4 + DaisyUI component classes (`btn`, `card`, `badge`, `table`, `stat`, `modal`, `alert`).
    - Developer-focused sleek aesthetic: default dark/dim themes, high contrast badges (green `badge-success` for online, red `badge-error` for offline).
    - Include micro-animations, pulse indicators on live data, and copy-to-clipboard utilities.
- **Reactivity & TypeScript**:
    - Define explicit interfaces in `resources/js/types/` for all entities (`Project`, `Monitor`, `MonitorResult`, `WebhookEndpoint`, `WebhookRequest`).
