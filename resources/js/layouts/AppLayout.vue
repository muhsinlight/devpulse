<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Activity, LogOut } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { index as projectsIndex } from '@/routes/projects';
import { destroy as destroySession } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import type { Auth } from '@/types/auth';

const page = usePage();

const user = computed(() => (page.props.auth as Auth).user);

const currentPath = computed(() => page.url.split('?')[0] ?? '/');

const isOverview = computed(() => currentPath.value === '/dashboard');
const isProjects = computed(() => currentPath.value.startsWith('/projects'));

const logout = () => {
    router.post(destroySession.url());
};
</script>

<template>
    <div
        class="bg-base-200/50 text-base-content selection:bg-primary flex min-h-screen flex-col font-sans selection:text-white"
    >
        <header
            class="navbar bg-base-100 border-base-300 sticky top-0 z-30 border-b px-4 sm:px-8"
        >
            <div class="flex-1 items-center gap-3">
                <Link :href="dashboard.url()" class="flex items-center gap-2.5">
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
                        :href="dashboard.url()"
                        class="btn btn-sm btn-ghost"
                        :class="
                            isOverview ? 'text-primary' : 'text-base-content/60'
                        "
                    >
                        Overview
                    </Link>
                    <Link
                        :href="projectsIndex.url()"
                        class="btn btn-sm btn-ghost"
                        :class="
                            isProjects ? 'text-primary' : 'text-base-content/60'
                        "
                    >
                        Projects
                    </Link>
                    <button
                        type="button"
                        class="btn btn-sm btn-ghost text-base-content/60 cursor-not-allowed"
                        disabled
                    >
                        Monitors
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-ghost text-base-content/60 cursor-not-allowed"
                        disabled
                    >
                        Webhooks
                    </button>
                </div>
            </div>

            <div class="flex-none items-center gap-3">
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
                        >
                            {{ user?.name }}
                        </span>
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
                                type="button"
                                class="text-error flex items-center gap-2"
                                @click="logout"
                            >
                                <LogOut class="h-3.5 w-3.5" />
                                Sign Out
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 space-y-6 p-4 sm:p-8">
            <slot />
        </main>
    </div>
</template>
