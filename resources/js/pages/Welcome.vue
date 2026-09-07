<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowRight,
    CheckCircle,
    Radio,
    Shield,
    Webhook,
    Zap,
} from 'lucide-vue-next';
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
    <Head title="DevPulse - API & Webhook Monitoring Platform" />

    <div
        class="bg-base-100 text-base-content selection:bg-primary relative flex min-h-screen flex-col overflow-hidden selection:text-white"
    >
        <!-- Ambient Glow -->
        <div
            class="bg-primary/10 pointer-events-none absolute top-[-15%] left-[20%] h-[50rem] w-[50rem] rounded-full blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute right-[10%] bottom-[-15%] h-[45rem] w-[45rem] rounded-full bg-emerald-500/10 blur-3xl"
        ></div>

        <!-- Top Navigation -->
        <header
            class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-5 sm:px-8"
        >
            <Link
                :href="home.url()"
                class="group inline-flex items-center gap-2.5"
            >
                <div
                    class="from-primary shadow-primary/20 flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr to-emerald-400 text-white shadow-lg transition-transform duration-200 group-hover:scale-105"
                >
                    <Activity class="h-5 w-5 animate-pulse" />
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-xl font-bold tracking-tight">
                        Dev<span class="text-primary">Pulse</span>
                    </span>
                    <span
                        class="badge badge-xs badge-success gap-1 px-1.5 py-0.5 font-mono text-[10px]"
                    >
                        <span
                            class="h-1 w-1 animate-ping rounded-full bg-current"
                        ></span>
                        v1.0
                    </span>
                </div>
            </Link>

            <nav class="flex items-center gap-3">
                <template v-if="auth?.user">
                    <Link
                        :href="dashboard.url()"
                        class="btn btn-primary btn-sm shadow-primary/20 gap-2 shadow-sm"
                    >
                        <span>Go to Dashboard</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="login.url()"
                        class="btn btn-ghost btn-sm text-xs font-semibold"
                    >
                        Sign In
                    </Link>
                    <Link
                        :href="register.url()"
                        class="btn btn-primary btn-sm shadow-primary/20 gap-1.5 text-xs font-semibold shadow-sm"
                    >
                        <span>Get Started Free</span>
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Hero Section -->
        <main
            class="relative z-10 mx-auto flex w-full max-w-6xl flex-1 flex-col items-center justify-center px-4 py-16 text-center sm:px-8 sm:py-24"
        >
            <!-- Pill badge -->
            <div
                class="bg-primary/10 border-primary/20 text-primary mb-6 inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium"
            >
                <Zap class="h-3.5 w-3.5" />
                <span>Next-Gen API & Webhook Telemetry for Developers</span>
            </div>

            <!-- Main Heading -->
            <h1
                class="max-w-4xl text-4xl font-extrabold tracking-tight text-balance sm:text-6xl"
            >
                Monitor APIs & Webhooks with
                <span
                    class="from-primary bg-gradient-to-r via-emerald-400 to-cyan-500 bg-clip-text text-transparent"
                >
                    Zero Friction
                </span>
            </h1>

            <p
                class="text-base-content/70 mt-6 max-w-2xl text-base leading-relaxed text-pretty sm:text-lg"
            >
                DevPulse periodically pings your endpoints, computes real-time
                uptime metrics, and lets you capture and inspect incoming
                webhooks in a unified developer cockpit.
            </p>

            <!-- CTA Buttons -->
            <div
                class="mt-8 flex w-full flex-col items-center gap-3 sm:w-auto sm:flex-row"
            >
                <template v-if="auth?.user">
                    <Link
                        :href="dashboard.url()"
                        class="btn btn-primary btn-md shadow-primary/25 w-full gap-2 text-sm shadow-lg sm:w-auto"
                    >
                        <span>Open Dashboard</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="register.url()"
                        class="btn btn-primary btn-md shadow-primary/25 w-full gap-2 text-sm shadow-lg sm:w-auto"
                    >
                        <span>Start Monitoring Now</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                    <Link
                        :href="login.url()"
                        class="btn btn-outline btn-md w-full text-sm sm:w-auto"
                    >
                        Sign in to account
                    </Link>
                </template>
            </div>

            <!-- Feature Highlights -->
            <div
                class="mt-16 grid w-full grid-cols-1 gap-6 text-left sm:mt-24 md:grid-cols-3"
            >
                <div
                    class="card bg-base-200/50 border-base-300/80 border p-6 shadow-sm"
                >
                    <div
                        class="bg-primary/10 text-primary mb-4 flex h-10 w-10 items-center justify-center rounded-xl"
                    >
                        <Radio class="h-5 w-5" />
                    </div>
                    <h3 class="text-base font-bold">API Uptime & Latency</h3>
                    <p
                        class="text-base-content/60 mt-1.5 text-xs leading-relaxed"
                    >
                        Automatic health check intervals (1m, 5m, 15m) with
                        response code verification and latency graphs.
                    </p>
                </div>

                <div
                    class="card bg-base-200/50 border-base-300/80 border p-6 shadow-sm"
                >
                    <div
                        class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500"
                    >
                        <Webhook class="h-5 w-5" />
                    </div>
                    <h3 class="text-base font-bold">Live Webhook Relay</h3>
                    <p
                        class="text-base-content/60 mt-1.5 text-xs leading-relaxed"
                    >
                        Generate unique webhook endpoints and inspect payloads,
                        headers, and signatures in real-time.
                    </p>
                </div>

                <div
                    class="card bg-base-200/50 border-base-300/80 border p-6 shadow-sm"
                >
                    <div
                        class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-500"
                    >
                        <Shield class="h-5 w-5" />
                    </div>
                    <h3 class="text-base font-bold">
                        Developer-First Security
                    </h3>
                    <p
                        class="text-base-content/60 mt-1.5 text-xs leading-relaxed"
                    >
                        Bcrypt-hashed session security, CSRF protection, rate
                        limiting, and project-based tenancy.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="border-base-300 text-base-content/50 relative z-10 border-t py-6 text-center text-xs"
        >
            <p>
                © {{ new Date().getFullYear() }} DevPulse. Built with Laravel,
                Inertia.js & Vue 3.
            </p>
        </footer>
    </div>
</template>
