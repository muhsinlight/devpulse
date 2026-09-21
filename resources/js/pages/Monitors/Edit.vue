<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import type { HttpMethod, Monitor } from '@/types';
import {
    show as showMonitor,
    update as updateMonitor,
} from '@/actions/App/Http/Controllers/MonitorController';

const props = defineProps<{
    monitor: Monitor;
}>();

const methods: HttpMethod[] = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'];
const intervals = [1, 5, 15, 30, 60];

const existingHeaders = props.monitor.headers ?? {};
const headerRows = ref<{ key: string; value: string }[]>(
    Object.keys(existingHeaders).length > 0
        ? Object.entries(existingHeaders).map(([key, value]) => ({
              key,
              value,
          }))
        : [{ key: '', value: '' }],
);

const form = useForm({
    name: props.monitor.name,
    url: props.monitor.url,
    method: props.monitor.method,
    headers: props.monitor.headers ?? null,
    body: props.monitor.body ?? '',
    check_interval: props.monitor.check_interval,
    expected_status_code: props.monitor.expected_status_code,
    timeout_seconds: props.monitor.timeout_seconds,
    is_active: props.monitor.is_active,
});

const showBodyField = computed(() => !['GET', 'HEAD'].includes(form.method));

const addHeaderRow = () => {
    headerRows.value.push({ key: '', value: '' });
};

const removeHeaderRow = (index: number) => {
    headerRows.value.splice(index, 1);
    if (headerRows.value.length === 0) {
        headerRows.value.push({ key: '', value: '' });
    }
};

const buildHeaders = (): Record<string, string> | null => {
    const headers: Record<string, string> = {};

    for (const row of headerRows.value) {
        const key = row.key.trim();
        if (key !== '') {
            headers[key] = row.value;
        }
    }

    return Object.keys(headers).length > 0 ? headers : null;
};

const submit = () => {
    form.headers = buildHeaders();
    form.transform((data) => ({
        ...data,
        body: showBodyField.value ? data.body || null : null,
    })).put(updateMonitor.url(props.monitor.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit ${monitor.name}`" />

        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <Link
                    :href="showMonitor.url(monitor.id)"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to monitor
                </Link>
                <h1 class="text-2xl font-bold tracking-tight">Edit Monitor</h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    Update endpoint check settings
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

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label
                            for="method"
                            class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                        >
                            Method
                        </label>
                        <select
                            id="method"
                            v-model="form.method"
                            class="select select-bordered bg-base-200/50 w-full text-sm"
                        >
                            <option
                                v-for="method in methods"
                                :key="method"
                                :value="method"
                            >
                                {{ method }}
                            </option>
                        </select>
                        <InputError :message="form.errors.method" />
                    </div>
                    <div class="sm:col-span-2">
                        <label
                            for="url"
                            class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                        >
                            URL
                        </label>
                        <TextInput
                            id="url"
                            v-model="form.url"
                            type="url"
                            required
                            :error="!!form.errors.url"
                        />
                        <InputError :message="form.errors.url" />
                    </div>
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <span
                            class="text-base-content/70 text-xs font-semibold tracking-wider uppercase"
                        >
                            Headers
                        </span>
                        <button
                            type="button"
                            class="btn btn-ghost btn-xs gap-1"
                            @click="addHeaderRow"
                        >
                            <Plus class="h-3 w-3" />
                            Add
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="(row, index) in headerRows"
                            :key="index"
                            class="flex gap-2"
                        >
                            <input
                                v-model="row.key"
                                type="text"
                                placeholder="Header"
                                class="input input-bordered bg-base-200/50 w-1/3 text-sm"
                            />
                            <input
                                v-model="row.value"
                                type="text"
                                placeholder="Value"
                                class="input input-bordered bg-base-200/50 flex-1 text-sm"
                            />
                            <button
                                type="button"
                                class="btn btn-ghost btn-sm"
                                @click="removeHeaderRow(index)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                    <InputError :message="form.errors.headers" />
                </div>

                <div v-if="showBodyField">
                    <label
                        for="body"
                        class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                    >
                        Body
                    </label>
                    <textarea
                        id="body"
                        v-model="form.body"
                        rows="3"
                        class="textarea textarea-bordered bg-base-200/50 w-full font-mono text-xs"
                    />
                    <InputError :message="form.errors.body" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label
                            for="check_interval"
                            class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                        >
                            Interval (min)
                        </label>
                        <select
                            id="check_interval"
                            v-model.number="form.check_interval"
                            class="select select-bordered bg-base-200/50 w-full text-sm"
                        >
                            <option
                                v-for="interval in intervals"
                                :key="interval"
                                :value="interval"
                            >
                                {{ interval }}
                            </option>
                        </select>
                        <InputError :message="form.errors.check_interval" />
                    </div>
                    <div>
                        <label
                            for="expected_status_code"
                            class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                        >
                            Expected status
                        </label>
                        <input
                            id="expected_status_code"
                            v-model.number="form.expected_status_code"
                            type="number"
                            min="100"
                            max="599"
                            required
                            class="input input-bordered bg-base-200/50 w-full text-sm"
                        />
                        <InputError
                            :message="form.errors.expected_status_code"
                        />
                    </div>
                    <div>
                        <label
                            for="timeout_seconds"
                            class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                        >
                            Timeout (sec)
                        </label>
                        <input
                            id="timeout_seconds"
                            v-model.number="form.timeout_seconds"
                            type="number"
                            min="1"
                            max="60"
                            required
                            class="input input-bordered bg-base-200/50 w-full text-sm"
                        />
                        <InputError :message="form.errors.timeout_seconds" />
                    </div>
                </div>

                <label class="label cursor-pointer justify-start gap-3">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="toggle toggle-primary toggle-sm"
                    />
                    <span class="label-text text-sm">Active</span>
                </label>

                <PrimaryButton :loading="form.processing" class="mt-2">
                    Save Changes
                </PrimaryButton>
            </form>
        </div>
    </AppLayout>
</template>
