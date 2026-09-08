<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Plus, Radio, TriangleAlert, Webhook } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Auth } from '@/types/auth';
import type { DashboardStats, Incident, Monitor, Project } from '@/types';
import { create as createProject } from '@/actions/App/Http/Controllers/ProjectController';
import {
    index as monitorsIndex,
    show as showMonitor,
} from '@/actions/App/Http/Controllers/MonitorController';
import { index as webhooksIndex } from '@/actions/App/Http/Controllers/WebhookEndpointController';
import {
    index as incidentsIndex,
    show as showIncident,
} from '@/actions/App/Http/Controllers/IncidentController';

defineProps<{
    stats: DashboardStats;
    openIncidents: (Incident & {
        monitor?: Pick<Monitor, 'id' | 'name' | 'url' | 'status'> & {
            project?: Pick<Project, 'id' | 'name'>;
        };
    })[];
}>();

const page = usePage();
const user = (page.props.auth as Auth).user;
</script>

<template>
    <AppLayout>
        <Head title="Dashboard - DevPulse" />

        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
                <p class="text-base-content/55 mt-1 text-sm">
                    Your projects and monitors at a glance
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Link
                    :href="createProject.url()"
                    class="btn btn-primary btn-sm gap-1.5"
                >
                    <Plus class="h-4 w-4" />
                    Create project
                </Link>
                <Link
                    :href="monitorsIndex.url()"
                    class="btn btn-outline btn-sm gap-1.5"
                >
                    <Radio class="h-4 w-4" />
                    Monitors
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-base-100 border-base-300 border p-4">
                <p class="text-base-content/55 text-xs font-medium uppercase">
                    Monitors
                </p>
                <p class="mt-2 font-mono text-2xl font-semibold">
                    {{ stats.total_monitors }}
                </p>
                <p class="text-base-content/50 mt-1 text-[11px]">
                    {{ stats.online_monitors }} online ·
                    {{ stats.offline_monitors }} offline
                </p>
            </div>
            <div class="bg-base-100 border-base-300 border p-4">
                <p class="text-base-content/55 text-xs font-medium uppercase">
                    Avg response
                </p>
                <p class="mt-2 font-mono text-2xl font-semibold">
                    <template v-if="stats.average_response_time_ms != null">
                        {{ stats.average_response_time_ms }}ms
                    </template>
                    <template v-else>—</template>
                </p>
            </div>
            <div class="bg-base-100 border-base-300 border p-4">
                <p class="text-base-content/55 text-xs font-medium uppercase">
                    Uptime
                </p>
                <p class="mt-2 font-mono text-2xl font-semibold">
                    <template v-if="stats.average_uptime != null">
                        {{ Number(stats.average_uptime).toFixed(1) }}%
                    </template>
                    <template v-else>—</template>
                </p>
            </div>
            <div class="bg-base-100 border-base-300 border p-4">
                <p class="text-base-content/55 text-xs font-medium uppercase">
                    Open incidents
                </p>
                <p class="mt-2 font-mono text-2xl font-semibold">
                    {{ stats.open_incidents }}
                </p>
                <p class="text-base-content/50 mt-1 text-[11px]">
                    {{ stats.total_webhook_requests_today }} webhook
                    {{
                        stats.total_webhook_requests_today === 1
                            ? 'request'
                            : 'requests'
                    }}
                    today
                </p>
            </div>
        </div>

        <div
            v-if="stats.total_projects === 0"
            class="bg-base-100 border-base-300 border p-6 sm:p-8"
        >
            <div class="mx-auto max-w-lg space-y-3 text-center">
                <h2 class="text-lg font-semibold">Welcome, {{ user?.name }}</h2>
                <p class="text-base-content/60 text-sm">
                    Create a project, then add a monitor to start checking an
                    endpoint.
                </p>
                <Link
                    :href="createProject.url()"
                    class="btn btn-primary btn-sm gap-2"
                >
                    <Plus class="h-4 w-4" />
                    Create first project
                </Link>
            </div>
        </div>

        <div
            v-else
            class="card bg-base-100 border-base-300/80 border shadow-sm"
        >
            <div
                class="border-base-300/80 flex items-center justify-between border-b px-4 py-3"
            >
                <div>
                    <h2 class="text-sm font-semibold">Open incidents</h2>
                    <p class="text-base-content/50 text-[11px]">
                        Current downtime across your monitors
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="webhooksIndex.url()"
                        class="btn btn-ghost btn-xs gap-1"
                    >
                        <Webhook class="h-3.5 w-3.5" />
                        Webhooks
                    </Link>
                    <Link
                        :href="incidentsIndex.url()"
                        class="btn btn-ghost btn-xs gap-1"
                    >
                        <TriangleAlert class="h-3.5 w-3.5" />
                        All incidents
                    </Link>
                </div>
            </div>

            <div
                v-if="openIncidents.length === 0"
                class="text-base-content/60 px-4 py-10 text-center text-xs"
            >
                No open incidents. All checked monitors are up.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr class="text-base-content/60 text-xs uppercase">
                            <th>Opened</th>
                            <th>Monitor</th>
                            <th>Status</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="incident in openIncidents"
                            :key="incident.id"
                        >
                            <td class="font-mono text-xs">
                                <Link
                                    :href="showIncident.url(incident.id)"
                                    class="hover:text-primary"
                                >
                                    {{
                                        new Date(
                                            incident.opened_at,
                                        ).toLocaleString()
                                    }}
                                </Link>
                            </td>
                            <td>
                                <Link
                                    v-if="incident.monitor"
                                    :href="showMonitor.url(incident.monitor.id)"
                                    class="text-xs font-medium hover:underline"
                                >
                                    {{ incident.monitor.name }}
                                </Link>
                            </td>
                            <td>
                                <StatusBadge :status="incident.status" />
                            </td>
                            <td
                                class="text-base-content/60 max-w-xs truncate text-xs"
                            >
                                {{ incident.last_error_message ?? '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
