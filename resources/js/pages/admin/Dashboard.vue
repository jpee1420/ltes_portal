<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Users, GraduationCap, School, Calendar, UserPlus, FolderTree, ArrowRight, ShieldCheck, CalendarDays, Clock } from '@lucide/vue';

defineProps<{
    metrics: {
        total_students: number;
        total_teachers: number;
        total_sections: number;
        active_school_year: string;
    };
    recentStudents: Array<{
        id: number;
        lrn: string;
        gender: string;
        birth_date: string;
        user: { name: string };
        section?: { name: string; grade_level: { name: string } } | null;
    }>;
    sections: Array<{
        id: number;
        name: string;
        student_profiles_count: number;
        grade_level: { name: string };
        adviser?: { name: string; credential: string } | null;
    }>;
    upcomingEvents?: Array<{
        id: number;
        title: string;
        category: string;
        category_label: string;
        color: string;
        start_date: string;
        end_date: string | null;
        is_school_wide: boolean;
        section_name: string | null;
    }>;
}>();

function getCategoryDot(color: string) {
    switch (color) {
        case 'red': return 'bg-rose-500';
        case 'amber': return 'bg-amber-500';
        case 'blue': return 'bg-blue-500';
        case 'purple': return 'bg-purple-500';
        case 'emerald': return 'bg-emerald-500';
        default: return 'bg-primary';
    }
}
</script>

<template>
    <div>
        <Head title="Admin Dashboard — Lipit-Tomeeng Elementary School" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                            <ShieldCheck class="h-3.5 w-3.5" />
                            Administrator Portal
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground mt-1">
                        Lipit-Tomeeng Elementary School
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Academic Management &bull; Active S.Y. <strong class="text-foreground font-medium">{{ metrics.active_school_year }}</strong>
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <Link href="/admin/calendar">
                        <Button size="sm" variant="outline" class="gap-1.5">
                            <CalendarDays class="h-4 w-4" />
                            School Calendar
                        </Button>
                    </Link>
                    <Link href="/admin/users/students/create">
                        <Button size="sm" class="gap-1.5 shadow-sm">
                            <UserPlus class="h-4 w-4" />
                            New Student
                        </Button>
                    </Link>
                    <Link href="/admin/sections/create">
                        <Button size="sm" variant="outline" class="gap-1.5">
                            <FolderTree class="h-4 w-4" />
                            New Section
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="rounded-xl border border-primary/20 bg-primary/5 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Enrolled Students</span>
                        <div class="rounded-lg bg-primary/10 p-2 text-primary">
                            <GraduationCap class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-foreground">{{ metrics.total_students }}</span>
                        <span class="text-xs text-muted-foreground block mt-0.5">Learners with LRN</span>
                    </div>
                </Card>

                <Card class="rounded-xl border border-blue-500/20 bg-blue-500/5 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-900 dark:text-blue-200">Teaching Faculty</span>
                        <div class="rounded-lg bg-blue-500/10 p-2 text-blue-600 dark:text-blue-400">
                            <School class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-foreground">{{ metrics.total_teachers }}</span>
                        <span class="text-xs text-muted-foreground block mt-0.5">Class Advisers</span>
                    </div>
                </Card>

                <Card class="rounded-xl border border-amber-500/20 bg-amber-500/5 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-900 dark:text-amber-200">Class Sections</span>
                        <div class="rounded-lg bg-amber-500/10 p-2 text-amber-600 dark:text-amber-400">
                            <FolderTree class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-foreground">{{ metrics.total_sections }}</span>
                        <span class="text-xs text-muted-foreground block mt-0.5">Kinder to Grade 6</span>
                    </div>
                </Card>

                <Card class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-900 dark:text-emerald-200">Academic Year</span>
                        <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400">
                            <Calendar class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-xl sm:text-2xl font-black text-foreground truncate block">S.Y. {{ metrics.active_school_year }}</span>
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium block mt-0.5">Active Cycle</span>
                    </div>
                </Card>
            </div>

            <!-- Main Content Split (3 Columns) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sections & Advisers (Span 2) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold tracking-tight text-foreground flex items-center gap-2">
                            <FolderTree class="h-5 w-5 text-primary" />
                            Sections &amp; Class Advisers
                        </h2>
                        <Link href="/admin/sections" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                            View all
                            <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div
                            v-for="section in sections"
                            :key="section.id"
                            class="rounded-xl border border-border/80 bg-card p-4 shadow-sm hover:border-primary/40 transition-colors"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="text-xs font-medium text-muted-foreground uppercase">{{ section.grade_level.name }}</span>
                                    <h3 class="font-bold text-foreground text-base">{{ section.name }}</h3>
                                </div>
                                <span class="rounded-md bg-muted px-2 py-0.5 text-xs font-semibold text-muted-foreground">
                                    {{ section.student_profiles_count }} students
                                </span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-border/60 flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">Adviser:</span>
                                <span class="font-medium text-foreground">{{ section.adviser?.name || 'Unassigned' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Upcoming Events + Recent Learners -->
                <div class="space-y-6">
                    <!-- Upcoming Calendar Events -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold tracking-tight text-foreground flex items-center gap-2">
                                <CalendarDays class="h-5 w-5 text-primary" />
                                Upcoming Events
                            </h2>
                            <Link href="/admin/calendar" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                                Calendar
                                <ArrowRight class="h-3 w-3" />
                            </Link>
                        </div>

                        <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                            <CardContent class="p-0">
                                <div v-if="!upcomingEvents || upcomingEvents.length === 0" class="p-4 text-center text-xs text-muted-foreground">
                                    No upcoming events scheduled.
                                </div>
                                <div v-else class="divide-y divide-border">
                                    <div
                                        v-for="event in upcomingEvents"
                                        :key="event.id"
                                        class="p-3.5 hover:bg-muted/30 transition-colors flex items-start justify-between gap-2"
                                    >
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full shrink-0" :class="getCategoryDot(event.color)" />
                                                <h4 class="text-xs font-bold text-foreground truncate">{{ event.title }}</h4>
                                            </div>
                                            <p class="text-[11px] text-muted-foreground mt-1 flex items-center gap-1">
                                                <Clock class="h-3 w-3" />
                                                {{ event.start_date }}
                                                <span v-if="event.end_date">&rarr; {{ event.end_date }}</span>
                                            </p>
                                        </div>
                                        <Badge variant="secondary" class="text-[10px] shrink-0">
                                            {{ event.category_label }}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Recent Learners -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold tracking-tight text-foreground flex items-center gap-2">
                                <Users class="h-5 w-5 text-primary" />
                                Recently Added Learners
                            </h2>
                            <Link href="/admin/users" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                                Directory
                                <ArrowRight class="h-3 w-3" />
                            </Link>
                        </div>

                        <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                            <CardContent class="p-0">
                                <div class="divide-y divide-border">
                                    <div
                                        v-for="student in recentStudents"
                                        :key="student.id"
                                        class="p-3.5 hover:bg-muted/30 transition-colors flex items-center justify-between"
                                    >
                                        <div>
                                            <h4 class="text-sm font-semibold text-foreground">{{ student.user.name }}</h4>
                                            <p class="text-xs font-mono text-muted-foreground">LRN: {{ student.lrn }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-medium text-muted-foreground">
                                                {{ student.section ? `${student.section.grade_level.name} - ${student.section.name}` : 'Unassigned' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
