<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Copy, Pencil, RefreshCw, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Project, WebhookEndpoint, WebhookRequest } from '@/types';
import { show as showProject } from '@/actions/App/Http/Controllers/ProjectController';
import {
    destroy as destroyWebhook,
    edit as editWebhook,
    index as webhooksIndex,
    rotate as rotateWebhook,
    rotateHmac as rotateHmacWebhook,
} from '@/actions/App/Http/Controllers/WebhookEndpointController';
import { createEcho } from '@/echo';

const props = defineProps<{
    webhook: WebhookEndpoint & {
        project?: Pick<Project, 'id' | 'name' | 'color' | 'slug'>;
        ingest_url: string;
    };
    requests: WebhookRequest[];
    retention_days: number;
}>();

const page = usePage();
const flashSuccess = computed(
    () =>
        (page.props.flash as { success?: string | null } | undefined)?.success,
);

const inbox = ref<WebhookRequest[]>([...props.requests]);
const highlightedId = ref<number | null>(null);
const copied = ref(false);
const copiedSecret = ref(false);
const expandedId = ref<number | null>(null);

let echo: ReturnType<typeof createEcho> = null;

onMounted(() => {
    echo = createEcho();

    if (echo === null) {
        return;
    }

    echo.private(`webhooks.${props.webhook.id}`).listen(
        '.webhook.request-received',
        (event: { request: WebhookRequest }) => {
            inbox.value = [event.request, ...inbox.value].slice(0, 50);
            highlightedId.value = event.request.id;
        },
    );
});

onBeforeUnmount(() => {
    echo?.leave(`webhooks.${props.webhook.id}`);
    echo?.disconnect();
});

const copyUrl = async () => {
    await navigator.clipboard.writeText(props.webhook.ingest_url);
    copied.value = true;
    window.setTimeout(() => {
        copied.value = false;
    }, 1500);
};

const rotateToken = () => {
    if (
        !confirm('Rotate this ingest URL? Existing services must be updated.')
    ) {
        return;
    }

    router.post(rotateWebhook.url(props.webhook.id));
};

const copySecret = async () => {
    if (!props.webhook.hmac_secret) {
        return;
    }

    await navigator.clipboard.writeText(props.webhook.hmac_secret);
    copiedSecret.value = true;
    window.setTimeout(() => {
        copiedSecret.value = false;
    }, 1500);
};

const rotateHmac = () => {
    const message = props.webhook.hmac_required
        ? 'Rotate the HMAC secret? Services must sign with the new secret.'
        : 'Enable HMAC? Unsigned requests will be rejected.';

    if (!confirm(message)) {
        return;
    }

    router.post(rotateHmacWebhook.url(props.webhook.id));
};

const deleteEndpoint = () => {
    if (
        !confirm(
            `Delete webhook "${props.webhook.name}"? Captured requests will be removed.`,
        )
    ) {
        return;
    }

    router.delete(destroyWebhook.url(props.webhook.id));
};

const ipLocation = (item: WebhookRequest): string | null => {
    const parts = [item.ip_city, item.ip_country].filter(
        (part): part is string => Boolean(part),
    );

    return parts.length > 0 ? parts.join(', ') : null;
};

const pretty = (value: unknown): string => {
    if (value === null || value === undefined) {
        return '—';
    }

    return JSON.stringify(value, null, 2);
};
</script>

<template>
    <AppLayout>
        <Head :title="`${webhook.name} - DevPulse`" />

        <div class="space-y-6">
            <div>
                <Link
                    :href="webhooksIndex.url()"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to webhooks
                </Link>

                <div
                    class="flex flex-col justify-between gap-4 md:flex-row md:items-start"
                >
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ webhook.name }}
                            </h1>
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
                        </div>
                        <Link
                            v-if="webhook.project"
                            :href="showProject.url(webhook.project.id)"
                            class="text-primary text-xs font-medium hover:underline"
                        >
                            {{ webhook.project.name }}
                        </Link>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="editWebhook.url(webhook.id)"
                            class="btn btn-outline btn-sm gap-1.5"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="btn btn-outline btn-sm gap-1.5"
                            @click="rotateToken"
                        >
                            <RefreshCw class="h-3.5 w-3.5" />
                            Rotate URL
                        </button>
                        <button
                            type="button"
                            class="btn btn-error btn-outline btn-sm gap-1.5"
                            @click="deleteEndpoint"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="flashSuccess" class="alert alert-success text-sm">
                {{ flashSuccess }}
            </div>

            <div
                class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
            >
                <p
                    class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                >
                    Ingest URL
                </p>
                <p class="text-base-content/50 mt-1 text-[11px]">
                    Treat this like a password. Only this account can see it
                    here.
                </p>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                    <input
                        :value="webhook.ingest_url"
                        type="text"
                        readonly
                        class="input input-bordered bg-base-200/50 flex-1 font-mono text-xs"
                    />
                    <button
                        type="button"
                        class="btn btn-primary btn-sm gap-1.5"
                        @click="copyUrl"
                    >
                        <Copy class="h-3.5 w-3.5" />
                        {{ copied ? 'Copied' : 'Copy' }}
                    </button>
                </div>
            </div>

            <div
                class="card bg-base-100 border-base-300/80 border p-4 shadow-sm"
            >
                <p
                    class="text-base-content/60 text-xs font-medium tracking-wider uppercase"
                >
                    HMAC secret
                </p>
                <p class="text-base-content/50 mt-1 text-[11px]">
                    When required, send
                    <span class="font-mono">X-Hub-Signature-256</span>
                    as
                    <span class="font-mono">sha256=&lt;hex&gt;</span>
                    of the raw body (GitHub-style). Also accepts
                    <span class="font-mono">X-Webhook-Signature</span>
                    and
                    <span class="font-mono">X-Signature</span>.
                </p>
                <div
                    v-if="webhook.hmac_required && webhook.hmac_secret"
                    class="mt-3 flex flex-col gap-2 sm:flex-row"
                >
                    <input
                        :value="webhook.hmac_secret"
                        type="text"
                        readonly
                        class="input input-bordered bg-base-200/50 flex-1 font-mono text-xs"
                    />
                    <button
                        type="button"
                        class="btn btn-outline btn-sm gap-1.5"
                        @click="copySecret"
                    >
                        <Copy class="h-3.5 w-3.5" />
                        {{ copiedSecret ? 'Copied' : 'Copy' }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline btn-sm gap-1.5"
                        @click="rotateHmac"
                    >
                        <RefreshCw class="h-3.5 w-3.5" />
                        Rotate secret
                    </button>
                </div>
                <div v-else class="mt-3 flex flex-wrap items-center gap-2">
                    <p class="text-base-content/60 text-xs">
                        Signatures are optional. Unsigned requests are stored.
                    </p>
                    <button
                        type="button"
                        class="btn btn-outline btn-sm gap-1.5"
                        @click="rotateHmac"
                    >
                        Enable HMAC
                    </button>
                </div>
            </div>

            <div class="card bg-base-100 border-base-300/80 border shadow-sm">
                <div class="border-base-300/80 border-b px-4 py-3">
                    <h2 class="text-sm font-semibold">Inbox</h2>
                    <p class="text-base-content/50 text-[11px]">
                        Latest 50 requests
                        <template v-if="retention_days > 0">
                            · older than {{ retention_days }} days are deleted
                        </template>
                        · live updates when Reverb is running
                    </p>
                </div>

                <div
                    v-if="inbox.length === 0"
                    class="text-base-content/60 px-4 py-10 text-center text-xs"
                >
                    No requests yet. POST to the ingest URL to capture the first
                    payload.
                </div>

                <ul v-else class="divide-base-300/60 divide-y">
                    <li
                        v-for="item in inbox"
                        :key="item.id"
                        class="px-4 py-3"
                        :class="
                            item.id === highlightedId
                                ? 'bg-primary/10'
                                : undefined
                        "
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 text-left"
                            @click="
                                expandedId =
                                    expandedId === item.id ? null : item.id
                            "
                        >
                            <div class="min-w-0">
                                <p class="font-mono text-xs font-semibold">
                                    {{ item.method }}
                                    <span class="text-base-content/50">
                                        {{ item.content_type ?? '—' }}
                                    </span>
                                </p>
                                <p class="text-base-content/50 text-[11px]">
                                    {{
                                        new Date(
                                            item.received_at,
                                        ).toLocaleString()
                                    }}
                                    ·
                                    {{ item.ip_address ?? 'unknown IP' }}
                                    <template v-if="ipLocation(item)">
                                        · {{ ipLocation(item) }}
                                    </template>
                                </p>
                            </div>
                            <span class="text-base-content/40 text-[11px]">
                                {{ expandedId === item.id ? 'Hide' : 'View' }}
                            </span>
                        </button>
                        <div
                            v-if="expandedId === item.id"
                            class="mt-3 space-y-3"
                        >
                            <div>
                                <p
                                    class="text-base-content/50 text-[11px] font-medium tracking-wider uppercase"
                                >
                                    Headers
                                </p>
                                <pre
                                    class="bg-base-200 mt-1 overflow-x-auto rounded p-3 font-mono text-[11px]"
                                    >{{ pretty(item.headers) }}</pre>
                            </div>
                            <div>
                                <p
                                    class="text-base-content/50 text-[11px] font-medium tracking-wider uppercase"
                                >
                                    Query
                                </p>
                                <pre
                                    class="bg-base-200 mt-1 overflow-x-auto rounded p-3 font-mono text-[11px]"
                                    >{{ pretty(item.query_params) }}</pre>
                            </div>
                            <div>
                                <p
                                    class="text-base-content/50 text-[11px] font-medium tracking-wider uppercase"
                                >
                                    Body
                                </p>
                                <pre
                                    class="bg-base-200 mt-1 overflow-x-auto rounded p-3 font-mono text-[11px]"
                                    >{{
                                        pretty(item.payload ?? item.raw_body)
                                    }}</pre>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
