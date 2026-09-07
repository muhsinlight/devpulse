<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    subtitle?: string;
}>();
</script>

<template>
    <div
        class="bg-base-200 text-base-content relative flex min-h-screen flex-col items-center justify-center px-4 py-10"
    >
        <div class="absolute top-4 right-4">
            <ThemeToggle />
        </div>

        <div class="w-full max-w-md">
            <div class="mb-8 flex flex-col items-center text-center">
                <Link :href="home.url()" class="inline-flex items-center">
                    <AppLogo size="lg" />
                </Link>
                <p class="text-base-content/50 mt-2 text-xs">
                    API & webhook monitoring
                </p>
            </div>

            <div class="card bg-base-100 border-base-300 border shadow-none">
                <div class="card-body p-6 sm:p-8">
                    <div v-if="title" class="mb-6">
                        <h1 class="text-xl font-semibold tracking-tight">
                            {{ title }}
                        </h1>
                        <p
                            v-if="subtitle"
                            class="text-base-content/60 mt-1 text-sm"
                        >
                            {{ subtitle }}
                        </p>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
