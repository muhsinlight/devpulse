<script setup lang="ts">
import { onMounted, ref } from 'vue';

defineProps<{
    modelValue: string;
    type?: string;
    placeholder?: string;
    id?: string;
    required?: boolean;
    autocomplete?: string;
    error?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        :id="id"
        :type="type || 'text'"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :autocomplete="autocomplete"
        :class="[
            'input input-bordered bg-base-200/50 focus:bg-base-100 border-base-300 focus:border-primary focus:ring-primary/20 w-full text-sm transition-all duration-200 focus:ring-2',
            error
                ? 'input-error border-error focus:border-error focus:ring-error/20'
                : '',
        ]"
        @input="
            emit('update:modelValue', ($event.target as HTMLInputElement).value)
        "
    />
</template>
