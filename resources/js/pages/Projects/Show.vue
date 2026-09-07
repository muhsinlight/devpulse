<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Pencil,
    Plus,
    Radio,
    Trash2,
    Webhook,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Project } from '@/types';
import {
    destroy as destroyProject,
    edit as editProject,
    index as projectsIndex,
} from '@/actions/App/Http/Controllers/ProjectController';

const props = defineProps<{
    project: Project;
}>();

const deleteProject = () => {
    if (
        !confirm(
            `Delete project "${props.project.name}"? This cannot be undone.`,
        )
    ) {
        return;
    }

    router.delete(destroyProject.url(props.project.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`${project.name} - DevPulse`" />

        <div class="space-y-6">
            <div>
                <Link
                    :href="projectsIndex.url()"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to projects
                </Link>

                <div
                    class="flex flex-col justify-between gap-4 md:flex-row md:items-start"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="mt-2 h-3.5 w-3.5 shrink-0 rounded-full"
                            :style="{ backgroundColor: project.color }"
                        />
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ project.name }}
                            </h1>
                            <p class="text-base-content/60 mt-1 text-xs">
                                {{
                                    project.description ||
                                    'No description provided.'
                                }}
                            </p>
                            <p
                                class="text-base-content/40 mt-2 font-mono text-[11px]"
                            >
                                /{{ project.slug }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            :href="editProject.url(project.id)"
                            class="btn btn-outline btn-sm gap-1.5"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="btn btn-error btn-outline btn-sm gap-1.5"
                            @click="deleteProject"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div
                    class="card bg-base-100 border-base-300/80 border p-5 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="bg-primary/10 text-primary rounded-lg p-2"
                            >
                                <Radio class="h-4 w-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold">Monitors</h2>
                                <p class="text-base-content/50 text-[11px]">
                                    {{ project.monitors_count ?? 0 }} configured
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-ghost btn-xs cursor-not-allowed gap-1 opacity-60"
                            disabled
                        >
                            <Plus class="h-3 w-3" />
                            Add
                        </button>
                    </div>
                    <div
                        class="border-base-300/80 bg-base-200/40 rounded-lg border border-dashed px-4 py-8 text-center"
                    >
                        <p class="text-base-content/70 text-xs font-medium">
                            API monitoring comes in Phase 3
                        </p>
                        <p class="text-base-content/50 mt-1 text-[11px]">
                            You'll be able to ping endpoints on a schedule from
                            here.
                        </p>
                    </div>
                </div>

                <div
                    class="card bg-base-100 border-base-300/80 border p-5 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="bg-warning/10 text-warning rounded-lg p-2"
                            >
                                <Webhook class="h-4 w-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold">Webhooks</h2>
                                <p class="text-base-content/50 text-[11px]">
                                    {{ project.webhooks_count ?? 0 }} endpoints
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-ghost btn-xs cursor-not-allowed gap-1 opacity-60"
                            disabled
                        >
                            <Plus class="h-3 w-3" />
                            Add
                        </button>
                    </div>
                    <div
                        class="border-base-300/80 bg-base-200/40 rounded-lg border border-dashed px-4 py-8 text-center"
                    >
                        <p class="text-base-content/70 text-xs font-medium">
                            Webhook inbox comes in Phase 5
                        </p>
                        <p class="text-base-content/50 mt-1 text-[11px]">
                            Unique URLs and live request capture will land here.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
