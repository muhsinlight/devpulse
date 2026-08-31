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
        class="btn btn-primary shadow-primary/20 hover:shadow-primary/30 w-full font-medium text-white shadow-md transition-all duration-200 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
    >
        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
        <slot />
    </button>
</template>
