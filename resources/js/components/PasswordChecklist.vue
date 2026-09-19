<script setup lang="ts">
import { Check, X } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    password: string;
    passwordConfirmation: string;
}>();

const checks = computed(() => {
    const password = props.password;

    return [
        {
            id: 'length',
            label: 'At least 8 characters',
            passed: password.length >= 8,
        },
        {
            id: 'upper',
            label: 'One uppercase letter',
            passed: /[A-Z]/.test(password),
        },
        {
            id: 'lower',
            label: 'One lowercase letter',
            passed: /[a-z]/.test(password),
        },
        {
            id: 'number',
            label: 'One number',
            passed: /\d/.test(password),
        },
        {
            id: 'match',
            label: 'Passwords match',
            passed:
                password.length > 0 &&
                password === props.passwordConfirmation,
        },
    ];
});
</script>

<template>
    <ul
        class="bg-base-200/60 border-base-300 space-y-1.5 rounded-lg border p-3 text-xs"
    >
        <li
            v-for="check in checks"
            :key="check.id"
            class="flex items-center gap-2"
            :class="check.passed ? 'text-success' : 'text-base-content/50'"
        >
            <Check v-if="check.passed" class="h-3.5 w-3.5 shrink-0" />
            <X v-else class="h-3.5 w-3.5 shrink-0" />
            <span>{{ check.label }}</span>
        </li>
    </ul>
</template>
