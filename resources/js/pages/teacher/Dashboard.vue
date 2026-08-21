<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, GraduationCap, TableProperties, CalendarDays, BookOpen, CheckCircle, AlertCircle, ArrowRight, ShieldCheck } from '@lucide/vue';

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
}>();
</script>

<template>
    <div>
        <Head title="Teacher Dashboard — Faculty Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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

                <div v-if="section" class="flex flex-wrap items-center gap-2">
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

                <!-- 2 Column Widgets -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <CardContent class="p-6">
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
                                        Record Today's Attendance
                                    </Button>
                                </Link>
                            </div>

                            <div v-else class="space-y-4">
                                <div class="flex items-center gap-2 text-emerald-600 text-xs font-semibold">
                                    <CheckCircle class="h-4 w-4" />
                                    Recorded for today
                                </div>
                                <div class="grid grid-cols-4 gap-2 text-center">
                                    <div class="rounded-lg bg-emerald-500/10 p-2.5">
                                        <span class="text-xs text-emerald-800 dark:text-emerald-300 block">Present</span>
                                        <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ todayAttendance.present }}</span>
                                    </div>
                                    <div class="rounded-lg bg-rose-500/10 p-2.5">
                                        <span class="text-xs text-rose-800 dark:text-rose-300 block">Absent</span>
                                        <span class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1 block">{{ todayAttendance.absent }}</span>
                                    </div>
                                    <div class="rounded-lg bg-amber-500/10 p-2.5">
                                        <span class="text-xs text-amber-800 dark:text-amber-300 block">Tardy</span>
                                        <span class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1 block">{{ todayAttendance.tardy }}</span>
                                    </div>
                                    <div class="rounded-lg bg-blue-500/10 p-2.5">
                                        <span class="text-xs text-blue-800 dark:text-blue-300 block">Excused</span>
                                        <span class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1 block">{{ todayAttendance.excused }}</span>
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
                                DepEd Curriculum Subjects
                            </CardTitle>
                            <CardDescription>
                                {{ section.grade_level.name }} Learning Areas
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-6">
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="subj in section.grade_level.subjects"
                                    :key="subj.id"
                                    class="rounded-md border border-border bg-muted/30 px-2.5 py-1 text-xs font-medium text-foreground"
                                >
                                    {{ subj.name }}
                                </span>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Passing Grade: <strong class="text-foreground">75</strong></span>
                                <Link href="/teacher/grades">
                                    <Button size="sm" class="gap-1.5">
                                        <TableProperties class="h-4 w-4" />
                                        Open Grade Matrix
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
