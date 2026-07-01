<script setup lang="ts">
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const openGroup = ref<string | null>(null);
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem v-for="item in items" :key="item.title">
            <SidebarMenuButton v-if="!item.children" as-child>
                <Link :href="item.href">
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="mr-2 h-4 w-4"
                    />
                    {{ item.title }}
                </Link>
            </SidebarMenuButton>

            <div v-else class="w-full">
                <SidebarMenuButton
                    @click="
                        openGroup = openGroup === item.title ? null : item.title
                    "
                    class="w-full"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="mr-2 h-4 w-4"
                    />
                    {{ item.title }}

                    <ChevronDown
                        class="ml-auto h-4 w-4 transition-transform"
                        :class="{ 'rotate-180': openGroup === item.title }"
                    />
                </SidebarMenuButton>

                <ul
                    v-show="openGroup === item.title"
                    class="mt-1 ml-6 flex flex-col gap-1"
                >
                    <li v-for="child in item.children" :key="child.title">
                        <SidebarMenuButton as-child size="sm">
                            <Link :href="child.href">
                                <component
                                    v-if="child.icon"
                                    :is="child.icon"
                                    class="mr-2 h-4 w-4"
                                />

                                {{ child.title }}
                            </Link>
                        </SidebarMenuButton>
                    </li>
                </ul>
            </div>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
