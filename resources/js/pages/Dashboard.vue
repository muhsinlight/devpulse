<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Plus, Radio } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth } from '@/types/auth';
import { create as createProject } from '@/actions/App/Http/Controllers/ProjectController';
import { index as monitorsIndex } from '@/actions/App/Http/Controllers/MonitorController';

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
                <p class="mt-2 font-mono text-2xl font-semibold">0</p>
            </div>
            <div class="bg-base-100 border-base-300 border p-4">
                <p class="text-base-content/55 text-xs font-medium uppercase">
                    Avg response
                </p>
                <p class="mt-2 font-mono text-2xl font-semibold">—</p>
            </div>
            <div class="bg-base-100 border-base-300 border p-4">
                <p class="text-base-content/55 text-xs font-medium uppercase">
                    Uptime
                </p>
                <p class="mt-2 font-mono text-2xl font-semibold">—</p>
            </div>
            <div class="bg-base-100 border-base-300 border p-4">
                <p class="text-base-content/55 text-xs font-medium uppercase">
                    Webhooks
                </p>
                <p class="mt-2 font-mono text-2xl font-semibold">0</p>
            </div>
        </div>

        <div class="bg-base-100 border-base-300 border p-6 sm:p-8">
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
    </AppLayout>
</template>
