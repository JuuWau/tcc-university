<script setup lang="ts">
import { User } from 'lucide-vue-next';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});
</script>


<template>
    <div
        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted"
    >
        <User class="h-4 w-4 text-muted-foreground" />
    </div>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span
            v-if="showEmail"
            class="truncate text-xs text-muted-foreground"
        >
            {{ user.email }}
        </span>
    </div>
</template>
