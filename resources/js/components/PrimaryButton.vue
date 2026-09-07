<script setup lang="ts">
defineProps<{
    type?: 'button' | 'submit' | 'reset';
    loading?: boolean;
    disabled?: boolean;
}>();
</script>

<template>
    <button
        :type="type || 'submit'"
        :disabled="disabled || loading"
        class="btn btn-primary w-full font-medium disabled:cursor-not-allowed disabled:opacity-50"
    >
        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
        <slot />
    </button>
</template>
