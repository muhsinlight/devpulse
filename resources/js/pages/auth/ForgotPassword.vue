<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <AuthLayout
        title="Reset Password"
        subtitle="Forgot your password? No problem. Enter your email and we'll send you a reset link."
    >
        <Head title="Forgot Password" />

        <div v-if="status" class="alert alert-success mb-4 px-3 py-2.5 text-xs">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 shrink-0 stroke-current"
                fill="none"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email Field -->
            <div>
                <label
                    for="email"
                    class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                >
                    Email Address
                </label>
                <div class="relative">
                    <div
                        class="text-base-content/40 pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <Mail class="h-4 w-4" />
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="developer@example.com"
                        required
                        autofocus
                        autocomplete="username"
                        :error="Boolean(form.errors.email)"
                        class="pl-9.5"
                    />
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <PrimaryButton :loading="form.processing">
                    <span class="flex items-center justify-center gap-2">
                        Send Reset Link
                        <Send class="h-4 w-4" />
                    </span>
                </PrimaryButton>
            </div>

            <!-- Back to Login -->
            <div class="border-base-200 border-t pt-3 text-center">
                <Link
                    href="/login"
                    class="text-base-content/70 hover:text-primary inline-flex items-center gap-1.5 text-xs font-semibold transition-colors"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to sign in
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
