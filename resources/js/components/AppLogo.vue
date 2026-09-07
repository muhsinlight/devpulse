<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        size?: 'sm' | 'md' | 'lg';
        /** Full marketing lockup vs clean D mark for nav */
        variant?: 'mark' | 'full';
        /** Show “DevPulse” text beside the mark */
        wordmark?: boolean;
    }>(),
    {
        size: 'md',
        variant: 'mark',
        wordmark: true,
    },
);

const heightClass = computed(() => {
    if (props.variant === 'full') {
        switch (props.size) {
            case 'sm':
                return 'h-10';
            case 'lg':
                return 'h-16';
            default:
                return 'h-12';
        }
    }

    switch (props.size) {
        case 'sm':
            return 'h-8 w-8';
        case 'lg':
            return 'h-11 w-11';
        default:
            return 'h-9 w-9';
    }
});

const textClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'text-base';
        case 'lg':
            return 'text-xl';
        default:
            return 'text-lg';
    }
});

const src = computed(() =>
    props.variant === 'full' ? '/images/logo.png' : '/images/logo-mark.png',
);
</script>

<template>
    <span class="inline-flex items-center gap-2">
        <img
            :src="src"
            alt="DevPulse"
            class="object-contain"
            :class="heightClass"
        />
        <span
            v-if="variant === 'mark' && wordmark"
            class="text-base-content font-semibold tracking-tight"
            :class="textClass"
        >
            DevPulse
        </span>
    </span>
</template>
