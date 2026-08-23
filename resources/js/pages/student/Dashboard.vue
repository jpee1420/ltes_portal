<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useInitials } from '@/composables/useInitials';
import { Award, BookOpen, CalendarCheck, FileText, GraduationCap, ArrowRight, User, CalendarDays, Clock } from '@lucide/vue';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const { getInitials } = useInitials();

defineProps<{
    student: {
        id: number;
        name: string;
        lrn: string;
        gender: string;
        birth_date: string;
        section: string | null;
        grade_level: string | null;
        adviser: string | null;
        school_year: string;
    } | null;
    gradesSummary: {
        general_average: number | null;
        descriptor: string;
        subject_count: number;
        quarterly_averages: {
            Q1: number | null;
            Q2: number | null;
            Q3: number | null;
            Q4: number | null;
        };
    } | null;
    attendanceSummary: {
        total_days: number;
        present_count: number;
        absent_count: number;
        tardy_count: number;
        excused_count: number;
        attendance_rate: number;
    } | null;
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
        <Head title="Learner & Parent Dashboard — Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-6xl mx-auto">
            <!-- No Profile State -->
            <div v-if="!student" class="flex flex-col items-center justify-center py-16 text-center">
                <div class="rounded-full bg-muted p-4 mb-4">
                    <GraduationCap class="h-10 w-10 text-muted-foreground" />
                </div>
                <h2 class="text-xl font-bold text-foreground">No Profile Found</h2>
                <p class="text-sm text-muted-foreground max-w-md mt-1">
                    Your student record has not been linked yet. Please contact your school administrator.
                </p>
            </div>

            <div v-else class="space-y-6">
                <!-- Welcome Banner -->
                <Card class="rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-background shadow-sm p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <Avatar class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl border-2 border-primary/30 shadow-md">
                                <AvatarImage
                                    v-if="user?.avatar"
                                    :src="user.avatar"
                                    :alt="student.name"
                                    class="object-cover"
                                />
                                <AvatarFallback class="bg-primary/20 text-primary font-black text-xl sm:text-2xl rounded-2xl">
                                    {{ getInitials(student.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-primary/20 px-2.5 py-0.5 text-xs font-semibold text-primary">
                                        <User class="h-3 w-3" />
                                        Learner &amp; Parent Portal
                                    </span>
                                    <span class="text-xs text-muted-foreground">S.Y. {{ student.school_year }}</span>
                                </div>
                                <h1 class="text-2xl sm:text-3xl font-black text-foreground mt-2">
                                    {{ student.name }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground mt-1">
                                    <span>LRN: <strong class="font-mono text-foreground">{{ student.lrn }}</strong></span>
                                    <span>&bull;</span>
                                    <span>{{ student.grade_level || 'Unassigned' }} &mdash; {{ student.section || 'No Section' }}</span>
                                    <span v-if="student.adviser">&bull;</span>
                                    <span v-if="student.adviser">Adviser: <strong class="text-foreground">{{ student.adviser }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link href="/student/calendar">
                                <Button variant="outline" class="gap-1.5 shadow-sm">
                                    <CalendarDays class="h-4 w-4" />
                                    School Calendar
                                </Button>
                            </Link>
                            <Link href="/student/grades">
                                <Button class="gap-1.5 shadow-sm">
                                    <FileText class="h-4 w-4" />
                                    View Report Card
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Card>

                <!-- 3-Column Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Academic Performance Card -->
                    <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden flex flex-col justify-between">
                        <CardHeader class="pb-3 border-b border-border bg-muted/20">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base font-bold flex items-center gap-2">
                                    <Award class="h-4 w-4 text-primary" />
                                    Academic Progress
                                </CardTitle>
                                <span class="text-xs font-semibold text-primary">DepEd Form 138</span>
                            </div>
                        </CardHeader>
                        <CardContent class="p-5">
                            <div v-if="gradesSummary" class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-muted/40 border border-border">
                                    <div>
                                        <span class="text-[11px] text-muted-foreground block font-medium">General Average</span>
                                        <span class="text-xl font-black text-foreground">
                                            {{ gradesSummary.general_average ?? 'Pending' }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[11px] text-muted-foreground block font-medium">Descriptor</span>
                                        <span class="text-xs font-bold text-primary">{{ gradesSummary.descriptor }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 gap-1 text-center">
                                    <div v-for="q in ['Q1', 'Q2', 'Q3', 'Q4'] as const" :key="q" class="rounded-lg bg-muted/30 p-1.5 border border-border/50">
                                        <span class="text-[10px] text-muted-foreground font-semibold block uppercase">{{ q }}</span>
                                        <span class="text-xs font-mono font-bold text-foreground mt-0.5 block">
                                            {{ gradesSummary.quarterly_averages[q] ?? '—' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-2 text-right">
                                    <Link href="/student/grades" class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1">
                                        Full breakdown
                                        <ArrowRight class="h-3 w-3" />
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attendance Summary Card -->
                    <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden flex flex-col justify-between">
                        <CardHeader class="pb-3 border-b border-border bg-muted/20">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base font-bold flex items-center gap-2">
                                    <CalendarCheck class="h-4 w-4 text-primary" />
                                    Attendance
                                </CardTitle>
                                <span class="text-xs font-medium text-muted-foreground">Homeroom Record</span>
                            </div>
                        </CardHeader>
                        <CardContent class="p-5">
                            <div v-if="attendanceSummary" class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-primary/5 border border-primary/20">
                                    <div>
                                        <span class="text-[11px] text-muted-foreground block font-medium">Rate</span>
                                        <span class="text-xl font-black text-primary">
                                            {{ attendanceSummary.attendance_rate }}%
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[11px] text-muted-foreground block font-medium">Logged Days</span>
                                        <span class="text-xs font-bold text-foreground">{{ attendanceSummary.total_days }} days</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-1.5 text-center">
                                    <div class="rounded-lg bg-emerald-500/10 p-1.5">
                                        <span class="text-[10px] text-emerald-800 dark:text-emerald-300 font-semibold block">Present</span>
                                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ attendanceSummary.present_count }}</span>
                                    </div>
                                    <div class="rounded-lg bg-rose-500/10 p-1.5">
                                        <span class="text-[10px] text-rose-800 dark:text-rose-300 font-semibold block">Absent</span>
                                        <span class="text-sm font-bold text-rose-600 dark:text-rose-400 mt-0.5 block">{{ attendanceSummary.absent_count }}</span>
                                    </div>
                                    <div class="rounded-lg bg-amber-500/10 p-1.5">
                                        <span class="text-[10px] text-amber-800 dark:text-amber-300 font-semibold block">Tardy</span>
                                        <span class="text-sm font-bold text-amber-600 dark:text-amber-400 mt-0.5 block">{{ attendanceSummary.tardy_count }}</span>
                                    </div>
                                </div>

                                <div class="pt-2 text-right">
                                    <Link href="/student/attendance" class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1">
                                        Full log
                                        <ArrowRight class="h-3 w-3" />
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Upcoming School & Class Events -->
                    <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden flex flex-col justify-between">
                        <CardHeader class="pb-3 border-b border-border bg-muted/20">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base font-bold flex items-center gap-2">
                                    <CalendarDays class="h-4 w-4 text-primary" />
                                    Upcoming Events
                                </CardTitle>
                                <Link href="/student/calendar" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
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
