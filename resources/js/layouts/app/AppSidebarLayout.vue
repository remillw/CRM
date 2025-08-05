<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <header class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
                <div class="flex items-center gap-2 w-full">
                    <SidebarTrigger class="-ml-1" />
                    <div class="flex-1">
                        <slot name="header">
                            <Breadcrumbs v-if="breadcrumbs && breadcrumbs.length > 0" :breadcrumbs="breadcrumbs" />
                        </slot>
                    </div>
                </div>
            </header>
            <slot />
        </AppContent>
    </AppShell>
</template>
