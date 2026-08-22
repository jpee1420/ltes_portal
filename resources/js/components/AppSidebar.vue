<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Users,
    Calendar,
    FolderTree,
    TableProperties,
    CalendarDays,
    CalendarCheck,
    FileText,
    School,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);

const homeRoute = computed(() => {
    if (!user.value) return '/';
    if (user.value.role === 'admin') return '/admin/dashboard';
    if (user.value.role === 'teacher') return '/teacher/dashboard';
    if (user.value.role === 'student') return '/student/dashboard';
    return '/';
});

const mainNavItems = computed<NavItem[]>(() => {
    if (!user.value) return [];

    if (user.value.role === 'admin') {
        return [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'School Calendar',
                href: '/admin/calendar',
                icon: CalendarDays,
            },
            {
                title: 'User Management',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'School Years',
                href: '/admin/school-years',
                icon: Calendar,
            },
            {
                title: 'Sections & Roster',
                href: '/admin/sections',
                icon: FolderTree,
            },
        ];
    }

    if (user.value.role === 'teacher') {
        return [
            {
                title: 'Dashboard',
                href: '/teacher/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'School Calendar',
                href: '/teacher/calendar',
                icon: Calendar,
            },
            {
                title: 'Class Roster',
                href: '/teacher/roster',
                icon: Users,
            },
            {
                title: 'Quarterly Grades',
                href: '/teacher/grades',
                icon: TableProperties,
            },
            {
                title: 'Daily Attendance',
                href: '/teacher/attendance',
                icon: CalendarDays,
            },
        ];
    }

    if (user.value.role === 'student') {
        return [
            {
                title: 'Dashboard',
                href: '/student/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'School Calendar',
                href: '/student/calendar',
                icon: CalendarDays,
            },
            {
                title: 'Report Card',
                href: '/student/grades',
                icon: FileText,
            },
            {
                title: 'Attendance',
                href: '/student/attendance',
                icon: CalendarCheck,
            },
        ];
    }

    return [];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="homeRoute">
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
