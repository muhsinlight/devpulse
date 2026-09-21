<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Webhook } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Project, WebhookEndpoint } from '@/types';
import {
    index as projectsIndex,
    show as showProject,
} from '@/actions/App/Http/Controllers/ProjectController';
import { show as showWebhook } from '@/actions/App/Http/Controllers/WebhookEndpointController';

defineProps<{
    webhooks: (WebhookEndpoint & {
        project?: Pick<Project, 'id' | 'name' | 'color'>;
    })[];
}>();

const tokenHint = (token: string): string => token.slice(-4);
</script>

<template>
    <AppLayout>
        <Head title="Webhooks" />

        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Webhooks</h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    Unique ingest URLs and captured inbound requests
                </p>
            </div>
            <Link
                :href="projectsIndex.url()"
                class="btn btn-primary btn-sm gap-1.5"
            >
                <Plus class="h-4 w-4" />
                Add via Project
            </Link>
        </div>

        <div
            v-if="webhooks.length === 0"
            class="card bg-base-100 border-base-300/80 border p-6 shadow-sm sm:p-8"
        >
            <div class="mx-auto max-w-md space-y-4 text-center">
                <div
                    class="bg-base-200 text-base-content/70 mx-auto flex h-10 w-10 items-center justify-center rounded"
                >
                    <Webhook class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">No webhook endpoints yet</h2>
                    <p class="text-base-content/70 mt-1.5 text-xs">
                        Open a project and add an endpoint to capture inbound
                        HTTP requests.
                    </p>
                </div>
                <Link
                    :href="projectsIndex.url()"
                    class="btn btn-primary btn-sm gap-2"
                >
                    <Plus class="h-4 w-4" />
                    Go to Projects
                </Link>
            </div>
        </div>

        <div v-else class="overflow-x-auto">
            <table
                class="bg-base-100 border-base-300/80 table border shadow-sm"
            >
                <thead>
                    <tr class="text-base-content/60 text-xs uppercase">
                        <th>Endpoint</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Requests</th>
                        <th>Last received</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="webhook in webhooks"
                        :key="webhook.id"
                        class="hover"
                    >
                        <td>
                            <Link
                                :href="showWebhook.url(webhook.id)"
                                class="hover:text-primary font-medium"
                            >
                                {{ webhook.name }}
                            </Link>
                            <div
                                class="text-base-content/50 mt-0.5 font-mono text-[11px]"
                            >
                                …{{ tokenHint(webhook.token) }}
                            </div>
                        </td>
                        <td>
                            <Link
                                v-if="webhook.project"
                                :href="showProject.url(webhook.project.id)"
                                class="text-xs font-medium hover:underline"
                            >
                                {{ webhook.project.name }}
                            </Link>
                        </td>
                        <td>
                            <span
                                class="badge badge-sm"
                                :class="
                                    webhook.is_active
                                        ? 'badge-success'
                                        : 'badge-ghost'
                                "
                            >
                                {{ webhook.is_active ? 'Active' : 'Paused' }}
                            </span>
                            <span
                                v-if="webhook.hmac_required"
                                class="badge badge-sm badge-outline ml-1"
                            >
                                HMAC
                            </span>
                        </td>
                        <td class="font-mono text-xs">
                            {{ webhook.requests_count ?? 0 }}
                        </td>
                        <td class="font-mono text-xs">
                            <template v-if="webhook.last_received_at">
                                {{
                                    new Date(
                                        webhook.last_received_at,
                                    ).toLocaleString()
                                }}
                            </template>
                            <span v-else class="text-base-content/40"
                                >Never</span
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
