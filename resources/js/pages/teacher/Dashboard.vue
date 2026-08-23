<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useInitials } from '@/composables/useInitials';
import { Users, GraduationCap, TableProperties, CalendarDays, BookOpen, CheckCircle, AlertCircle, ArrowRight, ShieldCheck, Clock } from '@lucide/vue';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const { getInitials } = useInitials();

defineProps<{
    section: {
        id: number;
        name: string;
        student_profiles_count: number;
        school_year: { label: string };
        grade_level: {
            name: string;
            subjects: Array<{ id: number; name: string }>;
        };
    } | null;
    todayAttendance: {
        present: number;
        absent: number;
        tardy: number;
        excused: number;
        is_recorded: boolean;
    };
    todayDate: string;
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
        <Head title="Teacher Dashboard — Faculty Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Avatar class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl border-2 border-primary/20 shadow-sm">
                        <AvatarImage
                            v-if="user?.avatar"
                            :src="user.avatar"
                            :alt="user?.name || 'Teacher'"
                            class="object-cover"
                        />
                        <AvatarFallback class="bg-primary/10 text-primary font-bold text-lg rounded-2xl">
                            {{ getInitials(user?.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                                <ShieldCheck class="h-3.5 w-3.5" />
                                Faculty &amp; Adviser Portal
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground mt-1">
                            Homeroom Teacher Dashboard
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Manage your homeroom advisory learners, encode quarterly grades, and record daily attendance.
                        </p>
                    </div>
                </div>

                <div v-if="section" class="flex flex-wrap items-center gap-2">
                    <Link href="/teacher/calendar">
                        <Button size="sm" variant="outline" class="gap-1.5">
                            <CalendarDays class="h-4 w-4" />
                            Class Calendar
                        </Button>
                    </Link>
                    <Link href="/teacher/grades">
                        <Button size="sm" class="gap-1.5 shadow-sm">
                            <TableProperties class="h-4 w-4" />
                            Encode Grades
                        </Button>
                    </Link>
                    <Link href="/teacher/attendance">
                        <Button size="sm" variant="outline" class="gap-1.5">
                            <CalendarDays class="h-4 w-4" />
                            Daily Attendance
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- No Section State -->
            <div v-if="!section" class="flex flex-col items-center justify-center py-16 text-center">
                <div class="rounded-full bg-muted p-4 mb-4">
                    <GraduationCap class="h-10 w-10 text-muted-foreground" />
                </div>
                <h2 class="text-xl font-bold text-foreground">No Advisory Section Assigned</h2>
                <p class="text-sm text-muted-foreground max-w-md mt-1 mb-6">
                    You have not been assigned as an adviser to any section for the active school year yet. Please contact the school administrator.
                </p>
            </div>

            <div v-else class="space-y-6">
                <!-- Section Overview Card -->
                <Card class="rounded-xl border border-primary/20 bg-primary/5 shadow-sm p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary">Assigned Homeroom Class</span>
                            <div class="flex items-center gap-2 mt-1">
                                <h2 class="text-2xl font-black text-foreground">{{ section.grade_level.name }} &mdash; {{ section.name }}</h2>
                                <span class="rounded-md bg-primary/20 px-2 py-0.5 text-xs font-semibold text-primary">
                                    S.Y. {{ section.school_year.label }}
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">
                                Total Enrolled Learners: <strong class="text-foreground">{{ section.student_profiles_count }} students</strong> &bull; Total DepEd Subjects: <strong class="text-foreground">{{ section.grade_level.subjects.length }}</strong>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link href="/teacher/roster">
                                <Button variant="secondary" size="sm" class="gap-1.5">
                                    <Users class="h-4 w-4" />
                                    View Full Roster
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Card>

                <!-- 3 Column Grid: Attendance, Subjects, Upcoming Events -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Today's Attendance Widget -->
                    <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden flex flex-col justify-between">
                        <CardHeader class="pb-3 border-b border-border bg-muted/20">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base font-bold flex items-center gap-2">
                                    <CalendarDays class="h-4 w-4 text-primary" />
                                    Today's Attendance
                                </CardTitle>
                                <span class="text-xs font-medium text-muted-foreground">{{ todayDate }}</span>
                            </div>
                        </CardHeader>
                        <CardContent class="p-5">
                            <div v-if="!todayAttendance.is_recorded" class="py-4 text-center">
                                <div class="inline-flex rounded-full bg-amber-500/10 p-3 text-amber-600 mb-2">
                                    <AlertCircle class="h-6 w-6" />
                                </div>
                                <h4 class="text-sm font-bold text-foreground">Attendance Pending</h4>
                                <p class="text-xs text-muted-foreground mt-1 mb-4">
                                    Today's homeroom attendance has not been recorded yet.
                                </p>
                                <Link href="/teacher/attendance">
                                    <Button size="sm" class="gap-1.5">
                                        <CalendarDays class="h-4 w-4" />
                                        Record Attendance
                                    </Button>
                                </Link>
                            </div>

                            <div v-else class="space-y-4">
                                <div class="flex items-center gap-2 text-emerald-600 text-xs font-semibold">
                                    <CheckCircle class="h-4 w-4" />
                                    Recorded for today
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                                    <div class="rounded-lg bg-emerald-500/10 p-2">
                                        <span class="text-[11px] text-emerald-800 dark:text-emerald-300 block">Present</span>
                                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ todayAttendance.present }}</span>
                                    </div>
                                    <div class="rounded-lg bg-rose-500/10 p-2">
                                        <span class="text-[11px] text-rose-800 dark:text-rose-300 block">Absent</span>
                                        <span class="text-lg font-bold text-rose-600 dark:text-rose-400 mt-0.5 block">{{ todayAttendance.absent }}</span>
                                    </div>
                                    <div class="rounded-lg bg-amber-500/10 p-2">
                                        <span class="text-[11px] text-amber-800 dark:text-amber-300 block">Tardy</span>
                                        <span class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5 block">{{ todayAttendance.tardy }}</span>
                                    </div>
                                    <div class="rounded-lg bg-blue-500/10 p-2">
                                        <span class="text-[11px] text-blue-800 dark:text-blue-300 block">Excused</span>
                                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-0.5 block">{{ todayAttendance.excused }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <Link href="/teacher/attendance" class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1">
                                        Update attendance
                                        <ArrowRight class="h-3 w-3" />
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subjects & Grading Overview -->
                    <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden flex flex-col justify-between">
                        <CardHeader class="pb-3 border-b border-border bg-muted/20">
                            <CardTitle class="text-base font-bold flex items-center gap-2">
                                <BookOpen class="h-4 w-4 text-primary" />
                                DepEd Curriculum
                            </CardTitle>
                            <CardDescription>
                                {{ section.grade_level.name }} Learning Areas
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-5">
                            <div class="flex flex-wrap gap-1 max-h-[110px] overflow-y-auto">
                                <span
                                    v-for="subj in section.grade_level.subjects"
                                    :key="subj.id"
                                    class="rounded-md border border-border bg-muted/30 px-2 py-0.5 text-xs font-medium text-foreground"
                                >
                                    {{ subj.name }}
                                </span>
                            </div>
                            <div class="mt-4 pt-3 border-t border-border flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Passing: <strong>75</strong></span>
                                <Link href="/teacher/grades">
                                    <Button size="sm" class="gap-1.5">
                                        <TableProperties class="h-4 w-4" />
                                        Grade Matrix
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Upcoming Calendar Activities -->
                    <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden flex flex-col justify-between">
                        <CardHeader class="pb-3 border-b border-border bg-muted/20">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base font-bold flex items-center gap-2">
                                    <CalendarDays class="h-4 w-4 text-primary" />
                                    Upcoming Activities
                                </CardTitle>
                                <Link href="/teacher/calendar" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                                    Calendar
                                    <ArrowRight class="h-3 w-3" />
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div v-if="!upcomingEvents || upcomingEvents.length === 0" class="p-5 text-center text-xs text-muted-foreground">
                                No upcoming activities scheduled.
                            </div>
                            <div v-else class="divide-y divide-border">
                                <div
                                    v-for="event in upcomingEvents.slice(0, 4)"
                                    :key="event.id"
                                    class="p-3 hover:bg-muted/30 transition-colors flex items-start justify-between gap-2"
                                >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full shrink-0" :class="getCategoryDot(event.color)" />
                                            <h4 class="text-xs font-bold text-foreground truncate">{{ event.title }}</h4>
                                        </div>
                                        <p class="text-[11px] text-muted-foreground mt-0.5 flex items-center gap-1">
                                            <Clock class="h-3 w-3" />
                                            {{ event.start_date }}
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
            </div>
        </div>
    </div>
</template>
