<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Moon, Sun } from 'lucide-vue-next';

type Theme = 'light' | 'dark';

const STORAGE_KEY = 'devpulse-theme';

const theme = ref<Theme>('light');

const applyTheme = (next: Theme) => {
    theme.value = next;
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem(STORAGE_KEY, next);
};

const toggle = () => {
    applyTheme(theme.value === 'light' ? 'dark' : 'light');
};

onMounted(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    const current =
        document.documentElement.getAttribute('data-theme') === 'dark'
            ? 'dark'
            : 'light';

    if (stored === 'light' || stored === 'dark') {
        applyTheme(stored);
        return;
    }

    theme.value = current;
});
</script>

<template>
    <button
        type="button"
        class="btn btn-ghost btn-sm btn-square"
        :aria-label="
            theme === 'light' ? 'Switch to dark theme' : 'Switch to light theme'
        "
        @click="toggle"
    >
        <Moon v-if="theme === 'light'" class="h-4 w-4" />
        <Sun v-else class="h-4 w-4" />
    </button>
</template>
