<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import type { Project } from '@/types';
import { show as showProject } from '@/actions/App/Http/Controllers/ProjectController';
import { store as storeWebhook } from '@/actions/App/Http/Controllers/WebhookEndpointController';

const props = defineProps<{
    project: Project;
}>();

const form = useForm({
    name: '',
    is_active: true,
    hmac_required: false,
});

const submit = () => {
    form.post(storeWebhook.url(props.project.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`Add Webhook - ${project.name}`" />

        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <Link
                    :href="showProject.url(project.id)"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to {{ project.name }}
                </Link>
                <h1 class="text-2xl font-bold tracking-tight">Add Webhook</h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    A unique ingest URL is generated after you save. You cannot
                    choose the path.
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
                        placeholder="Stripe events"
                        required
                        autofocus
                        :error="!!form.errors.name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <label class="flex items-start gap-2 text-sm">
                    <input
                        v-model="form.hmac_required"
                        type="checkbox"
                        class="checkbox checkbox-sm mt-0.5"
                    />
                    <span>
                        Require HMAC-SHA256 signatures
                        <span class="text-base-content/50 mt-0.5 block text-xs">
                            Reject unsigned requests. The secret is shown after
                            save.
                        </span>
                    </span>
                </label>

                <PrimaryButton :loading="form.processing" class="mt-2">
                    Create endpoint
                </PrimaryButton>
            </form>
        </div>
    </AppLayout>
</template>
