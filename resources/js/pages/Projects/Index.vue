<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FolderKanban, Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Project } from '@/types';
import {
    create as createProject,
    show as showProject,
} from '@/actions/App/Http/Controllers/ProjectController';

defineProps<{
    projects: Project[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="Projects" />

        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Projects</h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    Group monitors and webhooks under a project workspace
                </p>
            </div>
            <Link
                :href="createProject.url()"
                class="btn btn-primary btn-sm gap-1.5"
            >
                <Plus class="h-4 w-4" />
                Create Project
            </Link>
        </div>

        <div
            v-if="projects.length === 0"
            class="card bg-base-100 border-base-300/80 border p-6 shadow-sm sm:p-8"
        >
            <div class="mx-auto max-w-md space-y-4 text-center">
                <div
                    class="bg-base-200 text-base-content/70 mx-auto flex h-10 w-10 items-center justify-center rounded"
                >
                    <FolderKanban class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">No projects yet</h2>
                    <p class="text-base-content/70 mt-1.5 text-xs">
                        Create your first project to start organizing API
                        monitors and webhook endpoints.
                    </p>
                </div>
                <Link
                    :href="createProject.url()"
                    class="btn btn-primary btn-sm gap-2"
                >
                    <Plus class="h-4 w-4" />
                    Create First Project
                </Link>
            </div>
        </div>

        <div
            v-else
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
        >
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="showProject.url(project.id)"
                class="card bg-base-100 border-base-300/80 hover:border-primary/40 border p-5 shadow-sm transition-colors"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 h-3 w-3 shrink-0 rounded-full"
                        :style="{ backgroundColor: project.color }"
                    />
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-base font-semibold">
                            {{ project.name }}
                        </h2>
                        <p
                            class="text-base-content/60 mt-1 line-clamp-2 text-xs"
                        >
                            {{
                                project.description ||
                                'No description provided.'
                            }}
                        </p>
                        <div
                            class="text-base-content/50 mt-3 flex gap-3 text-[11px] font-medium tracking-wide uppercase"
                        >
                            <span
                                >{{
                                    project.monitors_count ?? 0
                                }}
                                monitors</span
                            >
                            <span
                                >{{
                                    project.webhooks_count ?? 0
                                }}
                                webhooks</span
                            >
                        </div>
                    </div>
                </div>
            </Link>
        </div>
    </AppLayout>
</template>
