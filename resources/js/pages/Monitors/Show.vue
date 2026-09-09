<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Play, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Incident, Monitor, MonitorResult, Project } from '@/types';
import { show as showProject } from '@/actions/App/Http/Controllers/ProjectController';
import { show as showIncident } from '@/actions/App/Http/Controllers/IncidentController';
import {
    check as checkMonitor,
    destroy as destroyMonitor,
    edit as editMonitor,
    index as monitorsIndex,
} from '@/actions/App/Http/Controllers/MonitorController';

const props = defineProps<{
    monitor: Monitor & {
        project?: Pick<Project, 'id' | 'name' | 'color' | 'slug'>;
    };
    results: MonitorResult[];
    openIncident: Incident | null;
    incidents: Incident[];
}>();

const page = usePage();
const flashSuccess = computed(
    () =>
        (page.props.flash as { success?: string | null } | undefined)?.success,
);

const runCheck = () => {
    router.post(
        checkMonitor.url(props.monitor.id),
        {},
        { preserveScroll: true },
    );
};

const deleteMonitor = () => {
    if (
        !confirm(
            `Delete monitor "${props.monitor.name}"? This cannot be undone.`,
        )
    ) {
        return;
    }

    router.delete(destroyMonitor.url(props.monitor.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`${monitor.name} - DevPulse`" />

        <div class="space-y-6">
            <div>
                <Link
                    :href="monitorsIndex.url()"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to monitors
                </Link>

                <div
                    class="flex flex-col justify-between gap-4 md:flex-row md:items-start"
                >
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ monitor.name }}
                            </h1>
                            <StatusBadge :status="monitor.status" />
                        </div>
                        <p class="text-base-content/60 font-mono text-xs">
                            {{ monitor.method }} {{ monitor.url }}
                        </p>
                        <Link
                            v-if="monitor.project"
                            :href="showProject.url(monitor.project.id)"
                            class="text-primary text-xs font-medium hover:underline"
                        >
                            {{ monitor.project.name }}
                        </Link>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm gap-1.5"
                            @click="runCheck"
                        >
                            <Play class="h-3.5 w-3.5" />
                            Check now
                        </button>
                        <Link
                            :href="editMonitor.url(monitor.id)"
                            class="btn btn-outline btn-sm gap-1.5"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="btn btn-error btn-outline btn-sm gap-1.5"
                            @click="deleteMonitor"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="flashSuccess" class="alert alert-success text-sm">
                {{ flashSuccess }}
            </div>

            <div v-if="openIncident" class="alert alert-error text-sm">
                <div>
                    <p class="font-medium">Open incident</p>
                    <p class="mt-0.5 text-xs opacity-80">
                        Started
                        {{ new Date(openIncident.opened_at).toLocaleString() }}
                        ·
                        {{ openIncident.last_error_message ?? 'Check failed' }}
                    </p>
                </div>
                <Link
                    :href="showIncident.url(openIncident.id)"
                    class="btn btn-sm"
                >
                    View incident
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
                >
                    <p
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                    >
                        Uptime
                    </p>
                    <p class="mt-2 font-mono text-2xl font-bold">
                        {{ Number(monitor.uptime_percentage).toFixed(1) }}%
                    </p>
                </div>
                <div
                    class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
                >
                    <p
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                    >
                        Last response
                    </p>
                    <p class="mt-2 font-mono text-2xl font-bold">
                        <template v-if="monitor.last_response_time_ms != null">
                            {{ monitor.last_response_time_ms }}ms
                        </template>
                        <template v-else>—</template>
                    </p>
                </div>
                <div
                    class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
                >
                    <p
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                    >
                        Last status
                    </p>
                    <p class="mt-2 font-mono text-2xl font-bold">
                        {{ monitor.last_status_code ?? '—' }}
                    </p>
                </div>
                <div
                    class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
                >
                    <p
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                    >
                        Next check
                    </p>
                    <p class="mt-2 font-mono text-lg font-bold">
                        <template v-if="!monitor.is_active">Paused</template>
                        <template v-else-if="monitor.next_check_at">
                            {{
                                new Date(monitor.next_check_at).toLocaleString()
                            }}
                        </template>
                        <template v-else>—</template>
                    </p>
                </div>
            </div>

            <div
                v-if="incidents.length > 0"
                class="card bg-base-100 border-base-300/80 border shadow-sm"
            >
                <div class="border-base-300/80 border-b px-4 py-3">
                    <h2 class="text-sm font-semibold">Incidents</h2>
                    <p class="text-base-content/50 text-[11px]">
                        Latest 10 downtime events for this monitor
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-base-content/60 text-xs uppercase">
                                <th>Opened</th>
                                <th>Status</th>
                                <th>Resolved</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="incident in incidents"
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
                                    <StatusBadge :status="incident.status" />
                                </td>
                                <td class="font-mono text-xs">
                                    <template v-if="incident.resolved_at">
                                        {{
                                            new Date(
                                                incident.resolved_at,
                                            ).toLocaleString()
                                        }}
                                    </template>
                                    <template v-else>—</template>
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

            <div class="card bg-base-100 border-base-300/80 border shadow-sm">
                <div class="border-base-300/80 border-b px-4 py-3">
                    <h2 class="text-sm font-semibold">Recent checks</h2>
                    <p class="text-base-content/50 text-[11px]">
                        Latest 50 results · scheduled every
                        {{ monitor.check_interval }}
                        {{
                            monitor.check_interval === 1 ? 'minute' : 'minutes'
                        }}
                    </p>
                </div>

                <div
                    v-if="results.length === 0"
                    class="text-base-content/60 px-4 py-10 text-center text-xs"
                >
                    No checks yet. Click “Check now” to run the first request.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-base-content/60 text-xs uppercase">
                                <th>Time</th>
                                <th>Result</th>
                                <th>Status</th>
                                <th>Latency</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in results" :key="result.id">
                                <td class="font-mono text-xs">
                                    {{
                                        new Date(
                                            result.checked_at,
                                        ).toLocaleString()
                                    }}
                                </td>
                                <td>
                                    <StatusBadge
                                        :status="
                                            result.is_success
                                                ? 'success'
                                                : 'failed'
                                        "
                                    />
                                </td>
                                <td class="font-mono text-xs">
                                    {{ result.status_code ?? '—' }}
                                </td>
                                <td class="font-mono text-xs">
                                    {{
                                        result.response_time_ms != null
                                            ? `${result.response_time_ms}ms`
                                            : '—'
                                    }}
                                </td>
                                <td
                                    class="text-base-content/60 max-w-xs truncate text-xs"
                                >
                                    {{ result.error_message ?? '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
