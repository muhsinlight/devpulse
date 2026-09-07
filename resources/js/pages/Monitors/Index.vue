<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Radio } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Monitor, Project } from '@/types';
import {
    index as projectsIndex,
    show as showProject,
} from '@/actions/App/Http/Controllers/ProjectController';
import { show as showMonitor } from '@/actions/App/Http/Controllers/MonitorController';

defineProps<{
    monitors: (Monitor & {
        project?: Pick<Project, 'id' | 'name' | 'color'>;
    })[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="Monitors - DevPulse" />

        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Monitors</h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    Manual and scheduled health checks across your projects
                </p>
            </div>
            <Link
                :href="projectsIndex.url()"
                class="btn btn-primary btn-sm shadow-primary/20 gap-1.5 shadow-sm"
            >
                <Plus class="h-4 w-4" />
                Add via Project
            </Link>
        </div>

        <div
            v-if="monitors.length === 0"
            class="card bg-base-100 border-base-300/80 border p-6 shadow-sm sm:p-8"
        >
            <div class="mx-auto max-w-md space-y-4 text-center">
                <div
                    class="bg-primary/10 text-primary ring-primary/5 mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ring-8"
                >
                    <Radio class="h-7 w-7" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">No monitors yet</h2>
                    <p class="text-base-content/70 mt-1.5 text-xs">
                        Open a project and add a monitor to start checking an
                        endpoint.
                    </p>
                </div>
                <Link
                    :href="projectsIndex.url()"
                    class="btn btn-primary btn-sm gap-2"
                >
                    <Plus class="h-4 w-4" />
                    Go to Projects
                </Link>
            </div>
        </div>

        <div v-else class="overflow-x-auto">
            <table
                class="bg-base-100 border-base-300/80 table border shadow-sm"
            >
                <thead>
                    <tr class="text-base-content/60 text-xs uppercase">
                        <th>Monitor</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Last check</th>
                        <th>Uptime</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="monitor in monitors"
                        :key="monitor.id"
                        class="hover"
                    >
                        <td>
                            <Link
                                :href="showMonitor.url(monitor.id)"
                                class="hover:text-primary font-medium"
                            >
                                {{ monitor.name }}
                            </Link>
                            <div
                                class="text-base-content/50 mt-0.5 max-w-xs truncate font-mono text-[11px]"
                            >
                                {{ monitor.method }} {{ monitor.url }}
                            </div>
                        </td>
                        <td>
                            <Link
                                v-if="monitor.project"
                                :href="showProject.url(monitor.project.id)"
                                class="text-xs font-medium hover:underline"
                            >
                                {{ monitor.project.name }}
                            </Link>
                        </td>
                        <td>
                            <StatusBadge :status="monitor.status" />
                        </td>
                        <td class="font-mono text-xs">
                            <template v-if="monitor.last_checked_at">
                                {{ monitor.last_status_code ?? '—' }} ·
                                {{ monitor.last_response_time_ms ?? '—' }}ms
                            </template>
                            <span v-else class="text-base-content/40"
                                >Never</span
                            >
                        </td>
                        <td class="font-mono text-xs">
                            {{ Number(monitor.uptime_percentage).toFixed(1) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
