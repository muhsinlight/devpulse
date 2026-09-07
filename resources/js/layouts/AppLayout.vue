<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { dashboard } from '@/routes';
import { index as projectsIndex } from '@/routes/projects';
import { index as monitorsIndex } from '@/routes/monitors';
import { destroy as destroySession } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import type { Auth } from '@/types/auth';

const page = usePage();

const user = computed(() => (page.props.auth as Auth).user);

const currentPath = computed(() => page.url.split('?')[0] ?? '/');

const isOverview = computed(() => currentPath.value === '/dashboard');
const isProjects = computed(() => currentPath.value.startsWith('/projects'));
const isMonitors = computed(
    () =>
        currentPath.value === '/monitors' ||
        /^\/monitors\/\d+/.test(currentPath.value),
);

const logout = () => {
    router.post(destroySession.url());
};

const navClass = (active: boolean) =>
    active
        ? 'btn btn-sm btn-ghost text-base-content'
        : 'btn btn-sm btn-ghost text-base-content/55';
</script>

<template>
    <div
        class="bg-base-200 text-base-content flex min-h-screen flex-col font-sans"
    >
        <header
            class="navbar bg-base-100 border-base-300 sticky top-0 z-30 border-b px-4 sm:px-6"
        >
            <div class="flex-1 items-center gap-3">
                <Link :href="dashboard.url()" class="inline-flex items-center">
                    <AppLogo size="sm" />
                </Link>
                <div class="ml-2 hidden items-center gap-1 text-sm md:flex">
                    <Link :href="dashboard.url()" :class="navClass(isOverview)">
                        Overview
                    </Link>
                    <Link
                        :href="projectsIndex.url()"
                        :class="navClass(isProjects)"
                    >
                        Projects
                    </Link>
                    <Link
                        :href="monitorsIndex.url()"
                        :class="navClass(isMonitors)"
                    >
                        Monitors
                    </Link>
                    <button
                        type="button"
                        class="btn btn-sm btn-ghost text-base-content/40 cursor-not-allowed"
                        disabled
                    >
                        Webhooks
                    </button>
                </div>
            </div>

            <div class="flex-none items-center gap-1">
                <ThemeToggle />
                <div class="dropdown dropdown-end">
                    <div
                        tabindex="0"
                        role="button"
                        class="btn btn-ghost btn-sm gap-2 font-normal normal-case"
                    >
                        <span
                            class="bg-base-300 text-base-content flex h-6 w-6 items-center justify-center rounded text-xs font-medium"
                        >
                            {{ user?.name?.charAt(0) || 'U' }}
                        </span>
                        <span class="max-w-[120px] truncate text-xs">
                            {{ user?.name }}
                        </span>
                    </div>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu menu-sm bg-base-100 border-base-300 z-50 mt-2 w-52 rounded-md border p-2 shadow-sm"
                    >
                        <li
                            class="menu-title text-base-content/50 border-base-200 mb-1 border-b px-2 py-1 text-xs"
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
                                Sign out
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
