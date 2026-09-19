<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import PasswordChecklist from '@/components/PasswordChecklist.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { store as storeNewPassword } from '@/actions/App/Http/Controllers/Auth/NewPasswordController';

const props = defineProps<{
    email: string;
    token: string;
}>();

const showPassword = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const passwordMeetsRules = computed(() => {
    const password = form.password;

    return (
        password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /\d/.test(password) &&
        password === form.password_confirmation
    );
});

const submit = () => {
    if (!passwordMeetsRules.value) {
        return;
    }

    form.post(storeNewPassword.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout
        title="Set New Password"
        subtitle="Please enter your new password to secure your account."
    >
        <Head title="Reset Password" />

        <form @submit.prevent="submit" class="space-y-4">
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

            <div>
                <label
                    for="password"
                    class="text-base-content/70 mb-1.5 block text-xs font-semibold tracking-wider uppercase"
                >
                    New Password
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
                        placeholder="Choose a strong password"
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
                        placeholder="Re-enter your new password"
                        required
                        autocomplete="new-password"
                        :error="Boolean(form.errors.password_confirmation)"
                        class="pl-9.5"
                    />
                </div>
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PasswordChecklist
                :password="form.password"
                :password-confirmation="form.password_confirmation"
            />

            <div class="pt-2">
                <PrimaryButton
                    :loading="form.processing"
                    :disabled="!passwordMeetsRules"
                >
                    Reset password
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
