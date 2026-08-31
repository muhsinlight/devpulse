<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Activity, ShieldCheck, Zap } from 'lucide-vue-next';

defineProps<{
    title?: string;
    subtitle?: string;
}>();
</script>

<template>
    <div
        class="bg-base-300/40 selection:bg-primary relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-8 selection:text-white"
    >
        <!-- Ambient background glow effects -->
        <div
            class="bg-primary/10 pointer-events-none absolute top-[-10%] left-[-10%] h-[40rem] w-[40rem] rounded-full blur-3xl"
        ></div>
        <div
            class="bg-secondary/10 pointer-events-none absolute right-[-10%] bottom-[-10%] h-[40rem] w-[40rem] rounded-full blur-3xl"
        ></div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Brand Logo -->
            <div class="mb-8 text-center">
                <Link href="/" class="group inline-flex items-center gap-2.5">
                    <div
                        class="from-primary text-primary-content shadow-primary/25 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr to-emerald-400 shadow-lg transition-transform duration-200 group-hover:scale-105"
                    >
                        <Activity class="h-5 w-5 animate-pulse text-white" />
                    </div>
                    <div class="text-left">
                        <div class="flex items-center gap-1.5">
                            <span
                                class="from-base-content to-base-content/80 bg-gradient-to-r bg-clip-text text-2xl font-bold tracking-tight text-transparent"
                            >
                                Dev<span class="text-primary">Pulse</span>
                            </span>
                            <span
                                class="badge badge-xs badge-success gap-1 px-1.5 py-1 font-mono text-[10px]"
                            >
                                <span
                                    class="h-1 w-1 animate-ping rounded-full bg-current"
                                ></span>
                                live
                            </span>
                        </div>
                        <p
                            class="text-base-content/50 text-[11px] font-medium tracking-wide uppercase"
                        >
                            API & Webhook Monitoring
                        </p>
                    </div>
                </Link>
            </div>

            <!-- Auth Card -->
            <div
                class="card bg-base-100 border-base-200/80 border shadow-xl backdrop-blur-sm"
            >
                <div class="card-body p-6 sm:p-8">
                    <div v-if="title" class="mb-6">
                        <h1
                            class="text-base-content text-xl font-bold tracking-tight"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="subtitle"
                            class="text-base-content/60 mt-1 text-xs"
                        >
                            {{ subtitle }}
                        </p>
                    </div>

                    <slot />
                </div>
            </div>

            <!-- Features micro-badge footer -->
            <div
                class="text-base-content/50 mt-8 flex items-center justify-center gap-6 text-xs"
            >
                <div class="flex items-center gap-1.5">
                    <ShieldCheck class="text-success h-4 w-4" />
                    <span>Bcrypt & Session Security</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <Zap class="text-warning h-4 w-4" />
                    <span>Real-time Checks</span>
                </div>
            </div>
        </div>
    </div>
</template>
