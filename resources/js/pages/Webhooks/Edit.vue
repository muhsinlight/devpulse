<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import type { WebhookEndpoint } from '@/types';
import {
    show as showWebhook,
    update as updateWebhook,
} from '@/actions/App/Http/Controllers/WebhookEndpointController';

const props = defineProps<{
    webhook: WebhookEndpoint;
}>();

const form = useForm({
    name: props.webhook.name,
    is_active: props.webhook.is_active,
    hmac_required: props.webhook.hmac_required,
});

const submit = () => {
    form.put(updateWebhook.url(props.webhook.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit ${webhook.name} - DevPulse`" />

        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <Link
                    :href="showWebhook.url(webhook.id)"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to webhook
                </Link>
                <h1 class="text-2xl font-bold tracking-tight">Edit Webhook</h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    Pause an endpoint to reject inbound requests with 404
                </p>
            </div>

            <form
                class="card bg-base-100 border-base-300/80 space-y-4 border p-5 shadow-sm sm:p-6"
                @submit.prevent="submit"
            >
                <div>
                    <label
                        for="name"
                        class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                    >
                        Name
                    </label>
                    <TextInput
                        id="name"
                        v-model="form.name"
                        required
                        autofocus
                        :error="!!form.errors.name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="checkbox checkbox-sm"
                    />
                    Accept inbound requests
                </label>

                <label class="flex items-start gap-2 text-sm">
                    <input
                        v-model="form.hmac_required"
                        type="checkbox"
                        class="checkbox checkbox-sm mt-0.5"
                    />
                    <span>
                        Require HMAC-SHA256 signatures
                        <span class="text-base-content/50 mt-0.5 block text-xs">
                            Unsigned requests return 401. Copy the secret from
                            the endpoint page.
                        </span>
                    </span>
                </label>

                <PrimaryButton :loading="form.processing" class="mt-2">
                    Save
                </PrimaryButton>
            </form>
        </div>
    </AppLayout>
</template>
