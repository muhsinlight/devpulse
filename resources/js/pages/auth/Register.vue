<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail, User } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { login } from '@/routes';
import { store as storeRegistration } from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(storeRegistration.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout
        title="Create account"
        subtitle="Monitor APIs and capture webhooks"
    >
        <Head title="Sign Up" />

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Full Name Field -->
            <div>
                <label
                    for="name"
                    class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                >
                    Full Name
                </label>
                <div class="relative">
                    <div
                        class="text-base-content/40 pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <User class="h-4 w-4" />
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        v-model="form.name"
                        placeholder="Alex Morgan"
                        required
                        autofocus
                        autocomplete="name"
                        :error="Boolean(form.errors.name)"
                        class="pl-9.5"
                    />
                </div>
                <InputError :message="form.errors.name" />
            </div>

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
                        placeholder="alex@company.com"
                        required
                        autocomplete="username"
                        :error="Boolean(form.errors.email)"
                        class="pl-9.5"
                    />
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <!-- Password Field -->
            <div>
                <label
                    for="password"
                    class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                >
                    Password
                </label>
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
                        placeholder="At least 8 characters"
                        required
                        autocomplete="new-password"
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

            <!-- Password Confirmation Field -->
            <div>
                <label
                    for="password_confirmation"
                    class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                >
                    Confirm Password
                </label>
                <div class="relative">
                    <div
                        class="text-base-content/40 pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <Lock class="h-4 w-4" />
                    </div>
                    <TextInput
                        id="password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password_confirmation"
                        placeholder="Re-enter your password"
                        required
                        autocomplete="new-password"
                        :error="Boolean(form.errors.password_confirmation)"
                        class="pl-9.5"
                    />
                </div>
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <PrimaryButton :loading="form.processing">
                    Create account
                </PrimaryButton>
            </div>

            <!-- Login Link -->
            <div class="border-base-200 border-t pt-3 text-center">
                <p class="text-base-content/60 text-xs">
                    Already have an account?
                    <Link
                        :href="login.url()"
                        class="text-primary ml-1 font-semibold hover:underline"
                    >
                        Sign in
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
