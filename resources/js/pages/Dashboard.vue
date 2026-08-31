<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Activity,
    Plus,
    Radio,
    Webhook,
    Server,
    Clock,
    CheckCircle2,
    LogOut,
    FolderKanban,
    ChevronRight,
    Search,
    Shield,
} from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
}

defineProps<{
    user: User;
}>();

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Developer Dashboard - DevPulse" />

    <div
        class="bg-base-200/50 text-base-content selection:bg-primary flex min-h-screen flex-col font-sans selection:text-white"
    >
        <!-- Navigation Bar -->
        <header
            class="navbar bg-base-100 border-base-300 sticky top-0 z-30 border-b px-4 sm:px-8"
        >
            <div class="flex-1 items-center gap-3">
                <Link href="/dashboard" class="flex items-center gap-2.5">
                    <div
                        class="from-primary text-primary-content shadow-primary/20 flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-tr to-emerald-400 shadow-sm"
                    >
                        <Activity class="h-4 w-4 text-white" />
                    </div>
                    <span class="text-lg font-bold tracking-tight">
                        Dev<span class="text-primary">Pulse</span>
                    </span>
                </Link>
                <div
                    class="ml-6 hidden items-center gap-1 text-sm font-medium md:flex"
                >
                    <Link
                        href="/dashboard"
                        class="btn btn-sm btn-ghost text-primary"
                        >Overview</Link
                    >
                    <button
                        class="btn btn-sm btn-ghost text-base-content/60 cursor-not-allowed"
                    >
                        Projects
                    </button>
                    <button
                        class="btn btn-sm btn-ghost text-base-content/60 cursor-not-allowed"
                    >
                        Monitors
                    </button>
                    <button
                        class="btn btn-sm btn-ghost text-base-content/60 cursor-not-allowed"
                    >
                        Webhooks
                    </button>
                </div>
            </div>

            <div class="flex-none items-center gap-3">
                <!-- User dropdown -->
                <div class="dropdown dropdown-end">
                    <div
                        tabindex="0"
                        role="button"
                        class="btn btn-ghost btn-sm border-base-300/80 hover:border-base-300 gap-2 border font-normal normal-case"
                    >
                        <div
                            class="bg-primary/20 text-primary flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold"
                        >
                            {{ user?.name?.charAt(0) || 'U' }}
                        </div>
                        <span
                            class="max-w-[120px] truncate text-xs font-semibold"
                            >{{ user?.name }}</span
                        >
                    </div>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu menu-sm bg-base-100 rounded-box border-base-200 z-50 mt-3 w-52 border p-2 shadow-lg"
                    >
                        <li
                            class="menu-title text-base-content/50 border-base-200/60 mb-1 border-b px-2 py-1 pb-1.5 text-xs"
                        >
                            {{ user?.email }}
                        </li>
                        <li>
                            <button
                                @click="logout"
                                class="text-error flex items-center gap-2"
                            >
                                <LogOut class="h-3.5 w-3.5" />
                                Sign Out
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="mx-auto w-full max-w-7xl flex-1 space-y-6 p-4 sm:p-8">
            <!-- Greeting & Quick Stats -->
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
                    <button
                        class="btn btn-primary btn-sm shadow-primary/20 gap-1.5 shadow-sm"
                    >
                        <Plus class="h-4 w-4" />
                        Create Project
                    </button>
                    <button class="btn btn-outline btn-sm gap-1.5">
                        <Radio class="h-4 w-4 text-emerald-500" />
                        New Monitor
                    </button>
                </div>
            </div>

            <!-- Metric Cards -->
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
                        <span class="text-base-content/50 text-xs"
                            >configured</span
                        >
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
                        <span class="text-base-content/50 text-xs"
                            >last 24h</span
                        >
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

            <!-- Getting Started / Empty State -->
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
                            You haven't set up any projects yet. Create your
                            first project to start monitoring your API endpoints
                            and inspecting incoming webhooks.
                        </p>
                    </div>
                    <div
                        class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row"
                    >
                        <button
                            class="btn btn-primary btn-sm w-full gap-2 sm:w-auto"
                        >
                            <Plus class="h-4 w-4" />
                            Create First Project
                        </button>
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
        </main>
    </div>
</template>
