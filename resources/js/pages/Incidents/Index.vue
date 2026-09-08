<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { TriangleAlert } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Incident, Monitor, Project } from '@/types';
import { show as showMonitor } from '@/actions/App/Http/Controllers/MonitorController';
import { show as showProject } from '@/actions/App/Http/Controllers/ProjectController';
import { show as showIncident } from '@/actions/App/Http/Controllers/IncidentController';

defineProps<{
    incidents: (Incident & {
        monitor?: Pick<Monitor, 'id' | 'name' | 'url' | 'status'> & {
            project?: Pick<Project, 'id' | 'name' | 'color'>;
        };
    })[];
}>();

const formatDuration = (openedAt: string, resolvedAt: string | null): string => {
    const start = new Date(openedAt).getTime();
    const end = resolvedAt ? new Date(resolvedAt).getTime() : Date.now();
    const totalSeconds = Math.max(0, Math.floor((end - start) / 1000));
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }

    if (minutes > 0) {
        return `${minutes}m`;
    }

    return `${totalSeconds}s`;
};
</script>

<template>
    <AppLayout>
        <Head title="Incidents - DevPulse" />

        <div>
            <h1 class="text-2xl font-bold tracking-tight">Incidents</h1>
            <p class="text-base-content/60 mt-1 text-xs">
                Downtime opened when a monitor goes offline, resolved when it
                recovers
            </p>
        </div>

        <div
            v-if="incidents.length === 0"
            class="card bg-base-100 border-base-300/80 border p-6 shadow-sm sm:p-8"
        >
            <div class="mx-auto max-w-md space-y-4 text-center">
                <div
                    class="bg-base-200 text-base-content/70 mx-auto flex h-10 w-10 items-center justify-center rounded"
                >
                    <TriangleAlert class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">No incidents yet</h2>
                    <p class="text-base-content/70 mt-1.5 text-xs">
                        An incident is recorded the first time a monitor check
                        fails, and closed when it succeeds again.
                    </p>
                </div>
            </div>
        </div>

        <div v-else class="overflow-x-auto">
            <table
                class="bg-base-100 border-base-300/80 table border shadow-sm"
            >
                <thead>
                    <tr class="text-base-content/60 text-xs uppercase">
                        <th>Incident</th>
                        <th>Monitor</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="incident in incidents"
                        :key="incident.id"
                        class="hover"
                    >
                        <td>
                            <Link
                                :href="showIncident.url(incident.id)"
                                class="hover:text-primary font-medium"
                            >
                                {{
                                    new Date(
                                        incident.opened_at,
                                    ).toLocaleString()
                                }}
                            </Link>
                            <div
                                class="text-base-content/50 mt-0.5 max-w-xs truncate text-[11px]"
                            >
                                {{ incident.last_error_message ?? '—' }}
                            </div>
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
                            <Link
                                v-if="incident.monitor?.project"
                                :href="
                                    showProject.url(
                                        incident.monitor.project.id,
                                    )
                                "
                                class="text-xs font-medium hover:underline"
                            >
                                {{ incident.monitor.project.name }}
                            </Link>
                        </td>
                        <td>
                            <StatusBadge :status="incident.status" />
                        </td>
                        <td class="font-mono text-xs">
                            {{
                                formatDuration(
                                    incident.opened_at,
                                    incident.resolved_at,
                                )
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
