<script setup lang="ts">
import SidebarMenuButton from '@/components/ui/sidebar/SidebarMenuButton.vue';
import { SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useSidebar } from '@/components/ui/sidebar/utils';

const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const { state, isMobile, setOpen, setOpenMobile } = useSidebar();

const currentUrl = computed(() => page.url);

const openGroup = ref<string | null>(null);

const isItemActive = (item: NavItem) => {
    if (!item.href) {
        return false;
    }

    return (
        currentUrl.value === item.href ||
        currentUrl.value.startsWith(`${item.href}/`)
    );
};

const getActiveGroup = () => {
    const activeGroup = props.items.find((item) => {
        return item.children?.some((child) => isItemActive(child));
    });

    return activeGroup?.title ?? null;
};

const initializeOpenGroup = () => {
    openGroup.value = getActiveGroup();
};

const toggleGroup = (title: string) => {
    if (state.value === 'collapsed' && !isMobile.value) {
        setOpen(true);
        openGroup.value = title;
        return;
    }

    if (isMobile.value) {
        setOpenMobile(true);
    }

    openGroup.value = openGroup.value === title ? null : title;
};

watch(
    () => page.url,
    () => {
        const activeGroup = getActiveGroup();

        if (activeGroup) {
            openGroup.value = activeGroup;
        }
    },
    { immediate: true },
);

</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem
            v-for="item in props.items"
            :key="item.title"
        >
            <SidebarMenuButton
                v-if="!item.children"
                as-child
                :tooltip="item.title"
                :is-active="isItemActive(item)"
            >
                <Link :href="item.href">
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="size-4 shrink-0"
                    />
                    <span class="truncate">
                        {{ item.title }}
                    </span>
                </Link>
            </SidebarMenuButton>

            <div
                v-else
                class="w-full"
            >
                <SidebarMenuButton
                    :tooltip="item.title"
                    :data-state="
                        openGroup === item.title ? 'open' : 'closed'
                    "
                    :is-active="
                        item.children.some((child) =>
                            isItemActive(child),
                        )
                    "
                    class="data-[active=true]:bg-sky-100! data-[active=true]:text-sky-700!"
                    @click="toggleGroup(item.title)"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="size-4 shrink-0"
                    />
                    <span class="truncate">
                        {{ item.title }}
                    </span>
                    <ChevronDown
                        class="ml-auto size-4 shrink-0 transition-transform duration-200 group-data-[collapsible=icon]:hidden"
                        :class="{
                            'rotate-180': openGroup === item.title,
                        }"
                    />
                </SidebarMenuButton>

                <ul
                    v-if="openGroup === item.title"
                    class="mt-1 flex flex-col gap-1 pl-2 group-data-[collapsible=icon]:hidden"
                >
                    <li
                        v-for="child in item.children"
                        :key="child.title"
                        class="min-w-0"
                    >
                        <SidebarMenuButton
                            as-child
                            size="sm"
                            :tooltip="child.title"
                            :is-active="isItemActive(child)"
                            class="data-[active=true]:bg-sky-100! data-[active=true]:text-sky-700!"
                        >
                            <Link :href="child.href">
                                <component
                                    v-if="child.icon"
                                    :is="child.icon"
                                    class="size-4 shrink-0"
                                />

                                <span class="truncate">
                                    {{ child.title }}
                                </span>
                            </Link>
                        </SidebarMenuButton>
                    </li>
                </ul>
            </div>
        </SidebarMenuItem>
    </SidebarMenu>
</template>