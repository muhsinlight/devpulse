<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    siteKey: string;
}>();

const emit = defineEmits<{
    token: [value: string];
}>();

const widget = ref<HTMLDivElement | null>(null);
let widgetId: string | undefined;

const renderWidget = (): void => {
    if (!widget.value || !window.turnstile) {
        return;
    }

    if (widgetId !== undefined) {
        window.turnstile.remove(widgetId);
    }

    widgetId = window.turnstile.render(widget.value, {
        sitekey: props.siteKey,
        callback: (token: string) => emit('token', token),
        'expired-callback': () => emit('token', ''),
        'error-callback': () => emit('token', ''),
    });
};

onMounted(() => {
    const existing = document.querySelector<HTMLScriptElement>(
        'script[data-turnstile]',
    );

    if (window.turnstile) {
        renderWidget();

        return;
    }

    const script = existing ?? document.createElement('script');

    if (!existing) {
        script.src =
            'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
        script.async = true;
        script.dataset.turnstile = 'true';
        document.head.appendChild(script);
    }

    script.addEventListener('load', renderWidget);
});

watch(
    () => props.siteKey,
    () => renderWidget(),
);

onBeforeUnmount(() => {
    if (widgetId !== undefined && window.turnstile) {
        window.turnstile.remove(widgetId);
    }
});
</script>

<template>
    <div ref="widget"></div>
</template>
