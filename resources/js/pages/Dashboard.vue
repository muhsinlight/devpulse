<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Plus,
    Radio,
    Webhook,
    Clock,
    CheckCircle2,
    FolderKanban,
    Shield,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth } from '@/types/auth';
import { create as createProject } from '@/actions/App/Http/Controllers/ProjectController';
import { index as monitorsIndex } from '@/actions/App/Http/Controllers/MonitorController';

const page = usePage();
const user = (page.props.auth as Auth).user;
</script>

<template>
    <AppLayout>
        <Head title="Developer Dashboard - DevPulse" />

        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Workspace Overview
                </h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    Real-time health monitoring and webhook telemetry
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <Link
                    :href="createProject.url()"
                    class="btn btn-primary btn-sm shadow-primary/20 gap-1.5 shadow-sm"
                >
                    <Plus class="h-4 w-4" />
                    Create Project
                </Link>
                <Link
                    :href="monitorsIndex.url()"
                    class="btn btn-outline btn-sm gap-1.5"
                >
                    <Radio class="h-4 w-4 text-emerald-500" />
                    New Monitor
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                        >Total Monitors</span
                    >
                    <div class="bg-primary/10 text-primary rounded-lg p-2">
                        <Radio class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-mono text-2xl font-bold">0</span>
                    <span class="text-base-content/50 text-xs">configured</span>
                </div>
            </div>

            <div
                class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                        >Avg Response Time</span
                    >
                    <div class="bg-info/10 text-info rounded-lg p-2">
                        <Clock class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-mono text-2xl font-bold">-- ms</span>
                    <span class="text-base-content/50 text-xs">last 24h</span>
                </div>
            </div>

            <div
                class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                        >Global Uptime</span
                    >
                    <div class="bg-success/10 text-success rounded-lg p-2">
                        <CheckCircle2 class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-mono text-2xl font-bold">100.0%</span>
                    <span class="text-success text-xs font-medium"
                        >All systems green</span
                    >
                </div>
            </div>

            <div
                class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                        >Webhooks Captured</span
                    >
                    <div class="bg-warning/10 text-warning rounded-lg p-2">
                        <Webhook class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-mono text-2xl font-bold">0</span>
                    <span class="text-base-content/50 text-xs"
                        >events today</span
                    >
                </div>
            </div>
        </div>

        <div
            class="card bg-base-100 border-base-300/80 border p-6 shadow-sm sm:p-8"
        >
            <div class="mx-auto max-w-2xl space-y-4 text-center">
                <div
                    class="bg-primary/10 text-primary ring-primary/5 mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ring-8"
                >
                    <FolderKanban class="h-7 w-7" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">
                        Welcome to DevPulse, {{ user?.name }}!
                    </h2>
                    <p
                        class="text-base-content/70 mx-auto mt-1.5 max-w-md text-xs"
                    >
                        You haven't set up any projects yet. Create your first
                        project to start monitoring your API endpoints and
                        inspecting incoming webhooks.
                    </p>
                </div>
                <div
                    class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row"
                >
                    <Link
                        :href="createProject.url()"
                        class="btn btn-primary btn-sm w-full gap-2 sm:w-auto"
                    >
                        <Plus class="h-4 w-4" />
                        Create First Project
                    </Link>
                    <a
                        href="https://github.com"
                        target="_blank"
                        class="btn btn-ghost btn-sm w-full gap-2 text-xs sm:w-auto"
                    >
                        <Shield class="h-3.5 w-3.5" />
                        Documentation
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
