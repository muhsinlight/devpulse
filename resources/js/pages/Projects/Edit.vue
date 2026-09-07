<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import type { Project } from '@/types';
import {
    show as showProject,
    update as updateProject,
} from '@/actions/App/Http/Controllers/ProjectController';

const props = defineProps<{
    project: Project;
}>();

const colorOptions = [
    '#10b981',
    '#3b82f6',
    '#f59e0b',
    '#ef4444',
    '#8b5cf6',
    '#06b6d4',
];

const form = useForm({
    name: props.project.name,
    description: props.project.description ?? '',
    color: props.project.color,
});

const submit = () => {
    form.put(updateProject.url(props.project.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit ${project.name} - DevPulse`" />

        <div class="mx-auto max-w-xl space-y-6">
            <div>
                <Link
                    :href="showProject.url(project.id)"
                    class="text-base-content/60 hover:text-primary mb-3 inline-flex items-center gap-1.5 text-xs font-medium"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to project
                </Link>
                <h1 class="text-2xl font-bold tracking-tight">Edit Project</h1>
                <p class="text-base-content/60 mt-1 text-xs">
                    Update the name, description, or accent color
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
                        Project Name
                    </label>
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        :error="!!form.errors.name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div>
                    <label
                        for="description"
                        class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                    >
                        Description
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="textarea textarea-bordered bg-base-200/50 focus:bg-base-100 border-base-300 focus:border-primary focus:ring-primary/20 w-full text-sm focus:ring-2"
                        :class="{
                            'textarea-error border-error':
                                !!form.errors.description,
                        }"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div>
                    <span
                        class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                    >
                        Accent Color
                    </span>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="color in colorOptions"
                            :key="color"
                            type="button"
                            class="h-8 w-8 rounded-full border-2 transition-transform hover:scale-105"
                            :class="
                                form.color === color
                                    ? 'border-base-content scale-105'
                                    : 'border-transparent'
                            "
                            :style="{ backgroundColor: color }"
                            :aria-label="`Select color ${color}`"
                            @click="form.color = color"
                        />
                    </div>
                    <InputError :message="form.errors.color" />
                </div>

                <PrimaryButton :loading="form.processing" class="mt-2">
                    Save Changes
                </PrimaryButton>
            </form>
        </div>
    </AppLayout>
</template>
