<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Activity, Inbox, Radio } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { dashboard, home, login, register } from '@/routes';

interface Auth {
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
}

defineProps<{
    auth?: Auth;
}>();
</script>

<template>
    <Head title="DevPulse - API & Webhook Monitoring" />

    <div class="bg-base-100 text-base-content flex min-h-screen flex-col">
        <header
            class="border-base-300 mx-auto flex w-full max-w-5xl items-center justify-between border-b px-4 py-4 sm:px-6"
        >
            <Link :href="home.url()" class="inline-flex items-center">
                <AppLogo size="md" />
            </Link>

            <nav class="flex items-center gap-1 sm:gap-2">
                <ThemeToggle />
                <template v-if="auth?.user">
                    <Link
                        :href="dashboard.url()"
                        class="btn btn-primary btn-sm"
                    >
                        Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link :href="login.url()" class="btn btn-ghost btn-sm">
                        Sign in
                    </Link>
                    <Link :href="register.url()" class="btn btn-primary btn-sm">
                        Get started
                    </Link>
                </template>
            </nav>
        </header>

        <main
            class="mx-auto grid w-full max-w-5xl flex-1 grid-cols-1 items-center gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-2 lg:gap-14"
        >
            <div>
                <h1
                    class="max-w-xl text-4xl font-semibold tracking-tight text-balance sm:text-5xl"
                >
                    Monitor APIs and inspect webhooks in one place.
                </h1>
                <p
                    class="text-base-content/60 mt-5 max-w-xl text-base leading-relaxed"
                >
                    Schedule health checks, track uptime and latency, and
                    capture incoming webhook requests from a single workspace.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <template v-if="auth?.user">
                        <Link :href="dashboard.url()" class="btn btn-primary">
                            Open dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="register.url()" class="btn btn-primary">
                            Create account
                        </Link>
                        <Link :href="login.url()" class="btn btn-outline">
                            Sign in
                        </Link>
                    </template>
                </div>

                <ul
                    class="border-base-300 text-base-content/70 mt-12 space-y-3 border-t pt-8 text-sm"
                >
                    <li class="flex items-start gap-2.5">
                        <Radio
                            class="text-base-content/40 mt-0.5 h-4 w-4 shrink-0"
                        />
                        <span
                            >Periodic endpoint checks with status and response
                            time</span
                        >
                    </li>
                    <li class="flex items-start gap-2.5">
                        <Activity
                            class="text-base-content/40 mt-0.5 h-4 w-4 shrink-0"
                        />
                        <span>Project-scoped monitors you own and control</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <Inbox
                            class="text-base-content/40 mt-0.5 h-4 w-4 shrink-0"
                        />
                        <span
                            >Inbound webhook inbox with unique ingest URLs</span
                        >
                    </li>
                </ul>
            </div>

            <div class="bg-base-200 border-base-300 hidden border p-5 lg:block">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium">Monitor preview</p>
                    <span
                        class="bg-success/15 text-success rounded px-2 py-0.5 text-xs font-medium"
                    >
                        Online
                    </span>
                </div>
                <div class="bg-base-100 border-base-300 space-y-4 border p-4">
                    <div>
                        <p class="text-base-content/50 text-xs">Name</p>
                        <p class="mt-0.5 text-sm font-medium">
                            Production API Health
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-base-content/50 text-xs">Latency</p>
                            <p class="mt-0.5 font-mono text-sm">120ms</p>
                        </div>
                        <div>
                            <p class="text-base-content/50 text-xs">Status</p>
                            <p class="mt-0.5 font-mono text-sm">200</p>
                        </div>
                        <div>
                            <p class="text-base-content/50 text-xs">Uptime</p>
                            <p class="mt-0.5 font-mono text-sm">99.9%</p>
                        </div>
                        <div>
                            <p class="text-base-content/50 text-xs">Interval</p>
                            <p class="mt-0.5 font-mono text-sm">5 min</p>
                        </div>
                    </div>
                    <div class="border-base-300 border-t pt-3">
                        <p class="text-base-content/50 text-xs">Last check</p>
                        <p class="mt-0.5 text-sm">Just now · GET /health</p>
                    </div>
                </div>
            </div>
        </main>

        <footer
            class="border-base-300 text-base-content/40 border-t py-5 text-center text-xs"
        >
            © {{ new Date().getFullYear() }} DevPulse
        </footer>
    </div>
</template>
