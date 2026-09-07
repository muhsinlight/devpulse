<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail, ArrowRight } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { register } from '@/routes';
import { request as passwordRequest } from '@/routes/password';
import { store as storeLogin } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(storeLogin.url(), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <AuthLayout
        title="Welcome back"
        subtitle="Sign in to your DevPulse developer workspace"
    >
        <Head title="Sign In" />

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

            <!-- Password Field -->
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label
                        for="password"
                        class="text-base-content/70 block text-xs font-semibold tracking-wider uppercase"
                    >
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="passwordRequest.url()"
                        class="text-primary text-xs font-medium hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>
                <div class="relative">
                    <div
                        class="text-base-content/40 pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <Lock class="h-4 w-4" />
                    </div>
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        placeholder="••••••••••••"
                        required
                        autocomplete="current-password"
                        :error="Boolean(form.errors.password)"
                        class="pr-10 pl-9.5"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="text-base-content/40 hover:text-base-content absolute inset-y-0 right-0 flex items-center pr-3"
                    >
                        <EyeOff v-if="showPassword" class="h-4 w-4" />
                        <Eye v-else class="h-4 w-4" />
                    </button>
                </div>
                <InputError :message="form.errors.password" />
            </div>

            <!-- Remember Me Checkbox -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="checkbox checkbox-primary checkbox-sm rounded"
                    />
                    <span class="text-base-content/70 text-xs select-none"
                        >Remember this device</span
                    >
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <PrimaryButton :loading="form.processing">
                    <span class="flex items-center justify-center gap-2">
                        Sign In to DevPulse
                        <ArrowRight class="h-4 w-4" />
                    </span>
                </PrimaryButton>
            </div>

            <!-- Register Link -->
            <div class="border-base-200 border-t pt-3 text-center">
                <p class="text-base-content/60 text-xs">
                    Don't have an account yet?
                    <Link
                        :href="register.url()"
                        class="text-primary ml-1 font-semibold hover:underline"
                    >
                        Create an account
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
