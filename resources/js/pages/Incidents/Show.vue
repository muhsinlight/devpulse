<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Incident, Monitor, Project } from '@/types';
import { show as showMonitor } from '@/actions/App/Http/Controllers/MonitorController';
import { show as showProject } from '@/actions/App/Http/Controllers/ProjectController';
import { index as incidentsIndex } from '@/actions/App/Http/Controllers/IncidentController';

defineProps<{
    incident: Incident & {
        monitor?: Pick<Monitor, 'id' | 'name' | 'url' | 'method' | 'status'> & {
            project?: Pick<Project, 'id' | 'name' | 'color' | 'slug'>;
        };
    };
}>();

const formatDuration = (
    openedAt: string,
    resolvedAt: string | null,
): string => {
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
        <Head title="Incident - DevPulse" />

        <div class="space-y-6">
            <div>
                <Link
                    :href="incidentsIndex.url()"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to incidents
                </Link>

                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight">Incident</h1>
                    <StatusBadge :status="incident.status" />
                </div>
                <p
                    v-if="incident.monitor"
                    class="text-base-content/60 mt-2 font-mono text-xs"
                >
                    {{ incident.monitor.method }} {{ incident.monitor.url }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
                >
                    <p
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                    >
                        Opened
                    </p>
                    <p class="mt-2 font-mono text-sm font-bold">
                        {{ new Date(incident.opened_at).toLocaleString() }}
                    </p>
                </div>
                <div
                    class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
                >
                    <p
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                    >
                        Resolved
                    </p>
                    <p class="mt-2 font-mono text-sm font-bold">
                        <template v-if="incident.resolved_at">
                            {{
                                new Date(incident.resolved_at).toLocaleString()
                            }}
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
                        Duration
                    </p>
                    <p class="mt-2 font-mono text-sm font-bold">
                        {{
                            formatDuration(
                                incident.opened_at,
                                incident.resolved_at,
                            )
                        }}
                    </p>
                </div>
                <div
                    class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
                >
                    <p
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                    >
                        Status codes
                    </p>
                    <p class="mt-2 font-mono text-sm font-bold">
                        {{ incident.opened_status_code ?? '—' }}
                        →
                        {{ incident.resolved_status_code ?? '—' }}
                    </p>
                </div>
            </div>

            <div
                class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
            >
                <p
                    class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                >
                    Error
                </p>
                <p class="mt-2 text-sm">
                    {{ incident.last_error_message ?? '—' }}
                </p>
            </div>

            <div
                v-if="incident.monitor"
                class="flex flex-wrap items-center gap-2"
            >
                <Link
                    :href="showMonitor.url(incident.monitor.id)"
                    class="btn btn-primary btn-sm"
                >
                    Open monitor
                </Link>
                <Link
                    v-if="incident.monitor.project"
                    :href="showProject.url(incident.monitor.project.id)"
                    class="btn btn-outline btn-sm"
                >
                    {{ incident.monitor.project.name }}
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
