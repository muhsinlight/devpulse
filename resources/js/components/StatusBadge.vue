<script setup lang="ts">
import type { MonitorStatus } from '@/types';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        status: MonitorStatus | 'success' | 'failed' | 'active' | 'inactive' | 'open' | 'resolved';
        size?: 'xs' | 'sm' | 'md' | 'lg';
        showDot?: boolean;
    }>(),
    {
        size: 'sm',
        showDot: true,
    },
);

const badgeClass = computed(() => {
    switch (props.status) {
        case 'online':
        case 'success':
        case 'active':
        case 'resolved':
            return 'badge-success text-success-content';
        case 'offline':
        case 'failed':
        case 'open':
            return 'badge-error text-error-content';
        case 'degraded':
            return 'badge-warning text-warning-content';
        case 'inactive':
        case 'pending':
        default:
            return 'badge-neutral text-neutral-content';
    }
});

const dotClass = computed(() => {
    switch (props.status) {
        case 'online':
        case 'success':
        case 'active':
        case 'resolved':
            return 'bg-emerald-400 animate-pulse';
        case 'offline':
        case 'failed':
        case 'open':
            return 'bg-rose-400 animate-ping';
        case 'degraded':
            return 'bg-amber-400 animate-pulse';
        default:
            return 'bg-slate-400';
    }
});

const label = computed(() => {
    switch (props.status) {
        case 'online':
            return 'Online';
        case 'offline':
            return 'Offline';
        case 'degraded':
            return 'Degraded';
        case 'pending':
            return 'Pending';
        case 'active':
            return 'Active';
        case 'inactive':
            return 'Inactive';
        case 'success':
            return '200 OK';
        case 'failed':
            return 'Failed';
        case 'open':
            return 'Open';
        case 'resolved':
            return 'Resolved';
        default:
            return props.status;
    }
});
</script>

<template>
    <div
        class="badge gap-1.5 font-medium transition-all duration-300"
        :class="[badgeClass, `badge-${props.size}`]"
    >
        <span
            v-if="showDot"
            class="h-1.5 w-1.5 rounded-full"
            :class="dotClass"
        ></span>
        <span>{{ label }}</span>
    </div>
</template>
