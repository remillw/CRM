<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Search, Users, List, Mail, BarChart3, Globe, TrendingUp, PieChart, Calendar, MessageSquare, Send } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Campagnes',
        href: '/campaigns',
        icon: Search,
    },
    {
        title: 'Contacts',
        href: '/contacts',
        icon: Users,
    },
    {
        title: 'Listes',
        href: '/contact-lists',
        icon: List,
    },
    {
        title: 'Templates Email',
        href: '/email-templates',
        icon: Mail,
    },
    {
        title: 'Campagnes Email',
        href: '/email-campaign-schedules',
        icon: Calendar,
    },
    {
        title: 'Récap Emails',
        href: '/email-reports',
        icon: PieChart,
    },
    {
        title: 'Templates SMS',
        href: '/sms-templates',
        icon: MessageSquare,
    },
    {
        title: 'Campagnes SMS',
        href: '/sms-campaign-schedules',
        icon: Send,
    },
    {
        title: 'Analytics',
        href: '/analytics',
        icon: BarChart3,
    },
    {
        title: 'Requêtes SEO',
        href: '/seo-queries',
        icon: TrendingUp,
    },
    {
        title: 'Web Scraping',
        href: '/web-scraping',
        icon: Globe,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
